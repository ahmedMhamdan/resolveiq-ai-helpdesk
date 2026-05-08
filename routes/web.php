<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

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

Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
