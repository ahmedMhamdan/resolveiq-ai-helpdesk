<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserRoleController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/tickets/trashed', [TicketController::class, 'trashed'])
            ->name('tickets.trashed');

        Route::post('/tickets/{id}/restore', [TicketController::class, 'restore'])
            ->name('tickets.restore');

        Route::delete('/tickets/{id}/force-delete', [TicketController::class, 'forceDelete'])
            ->name('tickets.forceDelete');

        Route::get('/users', [UserRoleController::class, 'index'])
                ->name('users.index');

        Route::patch('/users/{user}/role', [UserRoleController::class, 'updateRole'])
                ->name('users.updateRole');
    });

    Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])
        ->name('tickets.replies.store');

    Route::delete('/tickets/{ticket}/replies/{reply}', [TicketReplyController::class, 'destroy'])
        ->name('tickets.replies.destroy');

    Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])
        ->name('tickets.attachments.destroy');

    Route::resource('tickets', TicketController::class);

    Route::middleware('admin')->group(function () {
        Route::resource('departments', DepartmentController::class)
            ->except(['show']);

        Route::resource('agents', AgentController::class)
            ->except(['show']);

        Route::resource('knowledge-base', KnowledgeBaseController::class)
            ->parameters(['knowledge-base' => 'article'])
            ->names('knowledge')
            ->except(['show']);
    });

    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])
        ->name('ai.index');

    Route::post('/ai-assistant/use-as-reply', [AiAssistantController::class, 'useAsReply'])
        ->name('ai.useAsReply');

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
});
