<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressPhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:20,1');
    Route::get('registro', [RegisterController::class, 'create'])->name('register');
    Route::post('registro', [RegisterController::class, 'store'])->name('register.store')->middleware('throttle:10,1');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* Entrenador */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('clientes', Admin\ClientController::class)->names('clients')->parameters(['clientes' => 'client']);
    Route::prefix('clientes/{client}')->name('clients.')->group(function () {
        Route::post('activar', [Admin\ClientController::class, 'activate'])->name('activate');
        Route::post('contrasena', [Admin\ClientController::class, 'resetPassword'])->name('password');
        Route::post('membresias', [Admin\ClientMembershipController::class, 'store'])->name('memberships.store');
        Route::delete('membresias/{membership}', [Admin\ClientMembershipController::class, 'destroy'])->name('memberships.destroy');
        Route::post('rutina', [Admin\ClientPlanController::class, 'workout'])->name('workout');
        Route::post('nutricion', [Admin\ClientPlanController::class, 'nutrition'])->name('nutrition');
    });

    Route::resource('rutinas', Admin\WorkoutPlanController::class)->only(['index', 'store', 'edit', 'destroy'])
        ->names('workouts')->parameters(['rutinas' => 'workout']);
    Route::post('rutinas/{workout}/duplicar', [Admin\WorkoutPlanController::class, 'duplicate'])->name('workouts.duplicate');

    Route::resource('nutricion', Admin\NutritionPlanController::class)->only(['index', 'store', 'edit', 'destroy'])
        ->names('nutrition')->parameters(['nutricion' => 'nutrition']);
    Route::post('nutricion/{nutrition}/duplicar', [Admin\NutritionPlanController::class, 'duplicate'])->name('nutrition.duplicate');

    Route::get('membresias', [Admin\MembershipController::class, 'index'])->name('memberships.index');

    Route::get('mensajes', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('mensajes/{client}', [Admin\MessageController::class, 'show'])->name('messages.show');

    Route::controller(Admin\CatalogController::class)->prefix('catalogos')->name('catalog.')->group(function () {
        Route::get('ejercicios', 'exercises')->name('exercises');
        Route::post('ejercicios', 'storeExercise')->name('exercises.store');
        Route::put('ejercicios/{exercise}', 'updateExercise')->name('exercises.update');
        Route::post('ejercicios/{exercise}/visibilidad', 'toggleExercise')->name('exercises.toggle');
        Route::get('alimentos', 'foods')->name('foods');
        Route::post('alimentos', 'storeFood')->name('foods.store');
        Route::put('alimentos/{food}', 'updateFood')->name('foods.update');
        Route::post('alimentos/{food}/visibilidad', 'toggleFood')->name('foods.toggle');
        Route::get('planes', 'plans')->name('plans');
        Route::post('planes', 'storePlan')->name('plans.store');
        Route::put('planes/{plan}', 'updatePlan')->name('plans.update');
        Route::post('planes/{plan}/visibilidad', 'togglePlan')->name('plans.toggle');
    });
});

/* Cliente */
Route::middleware(['auth', 'role:client'])->prefix('app')->name('client.')->group(function () {
    Route::get('/', Client\DashboardController::class)->name('dashboard');
    Route::get('rutina', [Client\PortalController::class, 'workout'])->name('workout');
    Route::get('comidas', [Client\PortalController::class, 'nutrition'])->name('nutrition');
    Route::get('entrenador', [Client\PortalController::class, 'messages'])->name('messages');

    Route::get('progreso', [Client\ProgressController::class, 'index'])->name('progress');
    Route::post('progreso', [Client\ProgressController::class, 'store'])->name('progress.store');
    Route::delete('progreso/{entry}', [Client\ProgressController::class, 'destroy'])->name('progress.destroy');
    Route::post('fotos', [Client\ProgressController::class, 'storePhoto'])->name('photos.store');
    Route::delete('fotos/{photo}', [Client\ProgressController::class, 'destroyPhoto'])->name('photos.destroy');
});

/* Compartido */
Route::middleware('auth')->group(function () {
    Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('perfil/contrasena', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('fotos/{photo}', ProgressPhotoController::class)->name('photos.show');
});
