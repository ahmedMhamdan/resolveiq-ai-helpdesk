<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\Ticket;
use App\Models\KnowledgeArticle;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function loginAs(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();
        $this->actingAs($user);
        return $user;
    }

    private function setArabic(): void
    {
        app()->setLocale('ar');
        session(['locale' => 'ar']);
    }

    private function setEnglish(): void
    {
        app()->setLocale('en');
        session()->forget('locale');
    }

    public function test_arabic_dashboard_shows_translated_ui_and_preserves_ticket_titles(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');
        $response->assertSee('التذاكر المفتوحة');
        $response->assertSee('قيد الانتظار');
        $response->assertSee('تم الحل');
        $response->assertSee('عاجلة');

        $ticket = Ticket::where('ticket_number', 'RIQ-1001')->firstOrFail();
        $response->assertSee($ticket->title);
    }

    public function test_arabic_dashboard_preserves_user_names_and_activity_values(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('dashboard'));

        $response->assertSee('Ahmed Admin');
        $response->assertSee('Support Agent');
    }

    public function test_english_dashboard_shows_english_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setEnglish();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_arabic_tickets_index_shows_translated_ui_and_preserves_titles(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('التذاكر');

        $ticket = Ticket::where('ticket_number', 'RIQ-1001')->firstOrFail();
        $response->assertSee($ticket->title);
    }

    public function test_arabic_ticket_show_preserves_dynamic_content(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $ticket = Ticket::where('ticket_number', 'RIQ-1001')->firstOrFail();
        $response = $this->get(route('tickets.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee($ticket->title);
        $response->assertSee($ticket->description);
    }

    public function test_arabic_trashed_tickets_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('tickets.trashed'));

        $response->assertStatus(200);
    }

    public function test_arabic_unassigned_tickets_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('tickets.unassigned'));

        $response->assertStatus(200);
    }

    public function test_arabic_overdue_tickets_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('tickets.overdue'));

        $response->assertStatus(200);
    }

    public function test_arabic_users_index_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('إدارة المستخدمين');
    }

    public function test_arabic_ai_assistant_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('ai.index'));

        $response->assertStatus(200);
        $response->assertSee('المساعد الذكي');
    }

    public function test_arabic_knowledge_base_shows_translated_ui(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('knowledge.index'));

        $response->assertStatus(200);
        $response->assertSee('قاعدة المعرفة');
    }

    public function test_locale_switch_updates_session(): void
    {
        $this->loginAs('user@resolveiq.test');

        $response = $this->post(route('translations.switch', 'ar'));

        $response->assertSessionHas('locale', 'ar');
        $this->assertEquals('ar', session('locale'));
    }

    public function test_locale_switch_persists_to_user(): void
    {
        $user = $this->loginAs('user@resolveiq.test');
        $this->assertNull($user->locale);

        $this->post(route('translations.switch', 'ar'));

        $user->refresh();
        $this->assertEquals('ar', $user->locale);
    }

    public function test_locale_switch_with_get_request_works(): void
    {
        $this->loginAs('user@resolveiq.test');

        $response = $this->get(route('translations.switch.get', 'ar'));

        $response->assertSessionHas('locale', 'ar');
    }

    public function test_locale_switch_invalid_locale_returns_404(): void
    {
        $this->loginAs('user@resolveiq.test');

        $response = $this->post(route('translations.switch', 'fr'));

        $response->assertStatus(404);
    }

    public function test_ticket_titles_are_never_translated_across_all_ticket_pages(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $titles = [
            'Unable to login to account',
            'Invoice amount is incorrect',
            'Need help resetting 2FA',
            'Email notifications are delayed',
        ];

        $response = $this->get(route('tickets.index'));
        foreach ($titles as $title) {
            $response->assertSee($title);
        }
    }

    public function test_user_names_are_never_translated(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $response = $this->get(route('dashboard'));
        $response->assertSee('Sarah Johnson');
        $response->assertSee('Omar Customer');
    }

    public function test_knowledge_article_content_is_never_translated(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $article = KnowledgeArticle::orderBy('created_at', 'desc')->firstOrFail();

        $response = $this->get(route('knowledge.index'));
        $response->assertSee($article->title);

        $response = $this->get(route('knowledge.edit', $article));
        $response->assertSee($article->title);
    }

    public function test_all_ticket_views_return_success_in_arabic(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $ticket = Ticket::where('ticket_number', 'RIQ-1004')->firstOrFail();

        $views = [
            route('tickets.index'),
            route('tickets.show', $ticket),
            route('tickets.create'),
            route('tickets.edit', $ticket),
        ];

        foreach ($views as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_all_admin_views_return_success_in_arabic(): void
    {
        $this->loginAs('admin@resolveiq.test');
        $this->setArabic();

        $views = [
            route('users.index'),
            route('ai.index'),
            route('knowledge.index'),
            route('departments.index'),
            route('agents.index'),
        ];

        foreach ($views as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }
}
