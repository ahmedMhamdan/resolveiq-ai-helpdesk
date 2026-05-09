<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

Route::get('/tickets/deleted', [TicketController::class, 'deleted'])->name('tickets.deleted');

Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

Route::post('/tickets/{id}/restore', [TicketController::class, 'restore'])->name('tickets.restore');
Route::delete('/tickets/{id}/force-delete', [TicketController::class, 'forceDelete'])->name('tickets.forceDelete');

Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])
    ->name('tickets.replies.store');

Route::delete('/tickets/{ticket}/replies/{reply}', [TicketReplyController::class, 'destroy'])
    ->name('tickets.replies.destroy');

Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])
    ->name('tickets.attachments.destroy');

Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
