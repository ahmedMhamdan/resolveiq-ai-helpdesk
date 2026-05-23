<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\Api\EmailVerificationController as PublicEmailVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/email/verify/{id}/{hash}', [PublicEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/email/verify', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Your email address is already verified.');
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Your email address is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent successfully.');
    })->middleware('throttle:6,1')->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Verified ticket workflow routes
    |--------------------------------------------------------------------------
    | Users can log in and manage their profile before verification, but ticket
    | workflows stay locked until their email address is verified.
    */
    Route::middleware('verified')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Ticket workflow routes
        |--------------------------------------------------------------------------
        | These routes must be defined before Route::resource('tickets', ...)
        | so Laravel does not treat words like "overdue" or "unassigned" as {ticket}.
        */

        // Authenticated ticket actions. Access rules are enforced inside controllers.
        Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])
            ->name('tickets.close');

        Route::patch('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])
            ->name('tickets.reopen');

        // Ticket-level AI assistant actions.
        Route::post('/tickets/{ticket}/ai/generate', [AiAssistantController::class, 'generateForTicket'])
            ->middleware('throttle:8,1')
            ->name('tickets.ai.generate');

        Route::post('/tickets/{ticket}/ai/use-as-reply', [AiAssistantController::class, 'useTicketAiAsReply'])
            ->name('tickets.ai.useAsReply');

        Route::patch('/tickets/{ticket}/ai/apply-priority', [AiAssistantController::class, 'applyPriority'])
            ->name('tickets.ai.applyPriority');

        Route::patch('/tickets/{ticket}/ai/apply-due-date', [AiAssistantController::class, 'applyDueDate'])
            ->name('tickets.ai.applyDueDate');

        // Admin-only ticket management pages/actions.
        Route::middleware('admin')->group(function () {
            Route::get('/tickets/overdue', [TicketController::class, 'overdue'])
                ->name('tickets.overdue');

            Route::get('/tickets/unassigned', [TicketController::class, 'unassigned'])
                ->name('tickets.unassigned');

            Route::patch('/tickets/{ticket}/assign-agent', [TicketController::class, 'assignAgent'])
                ->name('tickets.assignAgent');

            Route::get('/tickets/trashed', [TicketController::class, 'trashed'])
                ->name('tickets.trashed');

            Route::post('/tickets/{id}/restore', [TicketController::class, 'restore'])
                ->name('tickets.restore');

            Route::delete('/tickets/{id}/force-delete', [TicketController::class, 'forceDelete'])
                ->name('tickets.forceDelete');
        });

        Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])
            ->name('tickets.replies.store');

        Route::delete('/tickets/{ticket}/replies/{reply}', [TicketReplyController::class, 'destroy'])
            ->name('tickets.replies.destroy');

        Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])
            ->name('tickets.attachments.destroy');

        Route::resource('tickets', TicketController::class);

        Route::get('/ai-assistant', [AiAssistantController::class, 'index'])
            ->name('ai.index');

        Route::post('/ai-assistant/use-as-reply', [AiAssistantController::class, 'useAsReply'])
            ->name('ai.useAsReply');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserRoleController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [UserRoleController::class, 'show'])
            ->name('users.show');

        Route::get('/users/{user}/edit', [UserRoleController::class, 'edit'])
            ->name('users.edit');

        Route::put('/users/{user}', [UserRoleController::class, 'update'])
            ->name('users.update');

        Route::patch('/users/{user}/role', [UserRoleController::class, 'updateRole'])
            ->name('users.updateRole');

        Route::resource('departments', DepartmentController::class)
            ->except(['show']);

        Route::patch('/agents/{agent}/make-user', [AgentController::class, 'makeUser'])
            ->name('agents.makeUser');

        Route::resource('agents', AgentController::class)
            ->except(['show']);

        Route::resource('knowledge-base', KnowledgeBaseController::class)
            ->parameters(['knowledge-base' => 'article'])
            ->names('knowledge')
            ->except(['show']);
    });

    // Keep old /settings URL working, but send it to the real profile page.
    Route::redirect('/settings', '/profile')
        ->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    | Static bulk routes must stay before /notifications/{notification} routes.
    */
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.readAll');

    Route::delete('/notifications/delete-read', [NotificationController::class, 'deleteRead'])
        ->name('notifications.deleteRead');

    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])
        ->name('notifications.deleteAll');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
});
