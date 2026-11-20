<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ResolveIqVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_cannot_see_internal_notes_on_the_web_or_api(): void
    {
        $ticket = $this->ticket();
        $ticket->replies()->create([
            'user_id' => $ticket->user_id,
            'message' => 'Visible public reply',
        ]);
        $ticket->replies()->create([
            'user_id' => $ticket->agent_id,
            'message' => 'Private note contents',
            'is_internal_note' => true,
        ]);
        $ticket->activityLogs()->create([
            'user_id' => $ticket->agent_id,
            'action' => 'AI internal note added',
            'new_value' => 'Private note contents',
        ]);

        $this->actingAs($ticket->user)->get(route('tickets.show', $ticket))
            ->assertOk()->assertSee('Visible public reply')->assertDontSee('Private note contents');
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Private note contents');

        Sanctum::actingAs($ticket->user);
        $this->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()->assertJsonCount(1, 'ticket.replies')->assertJsonCount(0, 'ticket.activity_logs');
        $this->getJson("/api/v1/tickets/{$ticket->id}/replies")->assertOk()->assertJsonCount(1, 'data');

        Sanctum::actingAs($ticket->agent);
        $this->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()->assertJsonCount(2, 'ticket.replies')->assertJsonCount(1, 'ticket.activity_logs');
    }

    public function test_attachments_are_private_and_only_admins_can_delete_them(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $ticket = $this->ticket();

        $this->actingAs($ticket->agent)->post(route('tickets.replies.store', $ticket), [
            'message' => 'Agent note',
            'is_internal_note' => true,
            'attachments' => [UploadedFile::fake()->createWithContent('details.txt', 'Private file contents')],
        ])->assertRedirect(route('tickets.show', $ticket));

        $attachment = $ticket->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->file_path);
        Storage::disk('public')->assertMissing($attachment->file_path);

        $download = route('tickets.attachments.download', [$ticket, $attachment]);
        $delete = route('tickets.attachments.destroy', [$ticket, $attachment]);
        $this->get($download)->assertOk()->assertDownload('details.txt');
        $this->actingAs($ticket->user)->get($download)->assertNotFound();
        $this->delete($delete)->assertForbidden();

        $admin = User::factory()->create(['role_id' => Role::create(['name' => 'admin'])->id]);
        $this->actingAs($admin)->delete($delete)->assertRedirect(route('tickets.show', $ticket));
        Storage::disk('local')->assertMissing($attachment->file_path);
        $this->assertDatabaseMissing('ticket_attachments', ['id' => $attachment->id]);
    }

    public function test_changing_email_requires_verification_and_the_emailed_link_works(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'updated@example.test',
        ])->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertFalse($user->hasVerifiedEmail());

        $notification = Notification::sent($user, ResolveIqVerifyEmailNotification::class)->sole();
        $url = $notification->toMail($user)->actionUrl;
        $this->get($url.'&signature=invalid')->assertForbidden();
        $this->get($url)->assertOk();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_ticket_search_finds_records_across_pages(): void
    {
        $ticket = $this->ticket();
        $ticket->update(['title' => 'Search target', 'created_at' => now()->subDay()]);
        $ticket->delete();

        for ($i = 0; $i < 12; $i++) {
            $other = $ticket->replicate(['deleted_at']);
            $other->ticket_number = 'RIQ-OTHER-'.$i;
            $other->title = 'Unrelated ticket';
            $other->save();
            $other->delete();
        }

        $admin = User::factory()->create(['role_id' => Role::create(['name' => 'admin'])->id]);

        $this->actingAs($admin)->get(route('tickets.trashed', ['search' => 'search target']))
            ->assertOk()->assertSee('Search target')->assertDontSee('Unrelated ticket');
    }

    public function test_ticket_creation_records_activity_when_a_number_must_be_retried(): void
    {
        $existing = $this->ticket();
        $numbers = [$existing->ticket_number, 'RIQ-RETRY'];
        $new = Ticket::createWithNumber(
            function () use (&$numbers) {
                return array_shift($numbers);
            },
            $existing->only(['title', 'description', 'user_id', 'department_id', 'status']),
            'Ticket created',
        );

        $this->assertSame('RIQ-RETRY', $new->ticket_number);
        $this->assertSame('Ticket created', $new->activityLogs()->sole()->action);
    }

    public function test_existing_attachments_migrate_without_changing_the_download_route(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $ticket = $this->ticket();
        $path = 'ticket-attachments/existing.txt';
        Storage::disk('public')->put($path, 'Existing document');

        $attachment = $ticket->attachments()->create([
            'user_id' => $ticket->user_id,
            'file_name' => 'existing.txt',
            'file_path' => $path,
        ]);

        $migration = require database_path('migrations/2026_11_20_000000_move_ticket_attachments_to_private_storage.php');
        $migration->up();
        $migration->up();

        Storage::disk('public')->assertMissing($path);
        $this->assertSame('Existing document', Storage::disk('local')->get($path));
        $this->actingAs($ticket->user)->get(route('tickets.attachments.download', [$ticket, $attachment]))
            ->assertOk()->assertDownload('existing.txt');
    }

    private function ticket(): Ticket
    {
        $agent = User::factory()->create(['role_id' => Role::create(['name' => 'agent'])->id]);

        return Ticket::create([
            'ticket_number' => 'RIQ-TEST',
            'title' => 'Support request',
            'description' => 'Description for the support request',
            'user_id' => User::factory()->create()->id,
            'agent_id' => $agent->id,
            'department_id' => Department::create(['name' => 'Support'])->id,
            'status' => 'open',
        ]);
    }
}
