<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController; // Panggil AuthController yang baru dibuat

// Otomatis arahkan ke chat
Route::get('/', function () {
    return redirect()->route('chat');
});

// --- ROUTES UNTUK AUTENTIKASI (Hanya bisa diakses kalau belum login) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- ROUTES INTI CHAT (Hanya bisa diakses kalau sudah login) ---
Route::middleware(['auth'])->group(function () {
    
    // Tampilan UI
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // API Internal untuk Alpine.js
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'getMessages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/conversations/private', [ChatController::class, 'getOrCreatePrivateConversation']);
    Route::post('/conversations/group', [ChatController::class, 'createGroupConversation']);
    Route::post('/presence', [ChatController::class, 'updatePresence']);
    Route::get('/users', [ChatController::class, 'getAllUsers']);
});