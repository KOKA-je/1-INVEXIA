<?php

use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PanneController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AttributionController;
use App\Http\Controllers\TypesPostesController;
use App\Events\RealTimeNotification;
use App\Events\UserConnection;


// Example routes

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeripheriqueController;
use App\Http\Controllers\TypesPeripheriquesController;
use App\Http\Controllers\CategorieEquipementController;
use App\Http\Controllers\EquipmentAssignmentController;
use GuzzleHttp\Middleware;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


Route::middleware('guest', 'check.status')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->name('login.post');
});

// Route::get('/registration', function () {
//     return view('auth.register');
// })->name('register');

Route::post('/registration', [AuthController::class, 'register'])->name('register');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware('auth', 'check.status')->group(function () {

    // Route::get('/email/verify', function () {
    //     return view('auth.verify-email');
    // })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    })->middleware('signed')->name('verification.verify');

    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:view-dashboard-admin')->name('dashboard');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});


// Permissions
Route::middleware(['check.status'])->group(function () {
    Route::get('permissions', [PermissionController::class, 'index'])
        ->middleware('permission:list-permission')->name('permissions.index');

    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->middleware('permission:create-permission')->name('permissions.create');

    Route::post('permissions', [PermissionController::class, 'store'])
        ->middleware('permission:create-permission')->name('permissions.store');

    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->middleware('permission:view-permission')->name('permissions.show');

    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->middleware('permission:edit-permission')->name('permissions.edit');

    Route::put('permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware('permission:edit-permission')->name('permissions.update');

    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware('permission:delete-permission')->name('permissions.destroy');
});

// Rôles
Route::middleware(['check.status'])->group(function () {
    Route::get('roles', [RolesController::class, 'index'])
        ->middleware('permission:list-role')->name('roles.index');

    Route::get('roles/create', [RolesController::class, 'create'])
        ->middleware('permission:create-role')->name('roles.create');

    Route::post('roles', [RolesController::class, 'store'])
        ->middleware('permission:create-role')->name('roles.store');

    Route::get('roles/{role}', [RolesController::class, 'show'])
        ->middleware('permission:view-role')->name('roles.show');

    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])
        ->middleware('permission:edit-role')->name('roles.edit');

    Route::put('roles/{role}', [RolesController::class, 'update'])
        ->middleware('permission:edit-role')->name('roles.update');

    Route::delete('roles/{role}', [RolesController::class, 'destroy'])
        ->middleware('permission:delete-role')->name('roles.destroy');
});

// Utilisateurs
Route::middleware(['check.status'])->group(function () {
    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:view-user')->name('users.index');

    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:create-user')->name('users.create');

    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:create-user')->name('users.store');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->middleware('permission:view-user')->name('users.show');

    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:edit-user')->name('users.edit');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:edit-user')->name('users.update');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:delete-user')->name('users.destroy');
});


Route::middleware(['check.status'])->group(function () {
    Route::put('/pannes/{panne}/status', [PanneController::class, 'updateStatus'])
        ->middleware('permission:change-status-panne')->name('pannes.update_status');

    Route::get('pannes', [PanneController::class, 'index'])->middleware('permission:view-panne')->name('pannes.index');
    Route::get('pannes/create', [PanneController::class, 'create'])->middleware('permission:view-create-panne')->name('pannes.create');
    Route::post('pannes', [PanneController::class, 'store'])->name('pannes.store');
    Route::get('pannes/{panne}', [PanneController::class, 'show'])->middleware('permission:view-panne')->name('pannes.show');
    Route::get('pannes/{panne}/edit', [PanneController::class, 'edit'])->name('pannes.edit')->middleware('permission:edit-panne');
    Route::put('pannes/{panne}', [PanneController::class, 'update'])->name('pannes.update');
    Route::delete('pannes/{panne}', [PanneController::class, 'destroy'])->name('pannes.destroy')->middleware('permission:permissioncel-panne');
});

// route pour équipement


Route::prefix('reports')->group(function () {
    Route::get('/equipments', [ReportController::class, 'generateEquipmentReport'])->name('reports.equipments');
    Route::get('/pannes', [ReportController::class, 'generatePanneReport'])->name('reports.pannes');
});
Route::get('/reports/equipment/{id}', [ReportController::class, 'generateEquipmentDetailReport'])
    ->name('reports.equipment.detail');

Route::middleware(['check.status'])->group(function () {

    Route::get('/reports/equipment-status', [ReportController::class, 'equipmentStatusReport'])
        ->name('reports.equipment-status');

    Route::post('/equipements/import', [EquipementController::class, 'import'])
        ->name('equipements.import');

    Route::get('/equipements/export', [EquipementController::class, 'export'])
        ->name('equipements.export');

    Route::get('equipements', [EquipementController::class, 'index'])
        ->middleware('permission:list-equipement')
        ->name('equipements.index');

    Route::get('/mon-espace', [EquipementController::class, 'mesEquipements'])
        ->name('mon.espace')
        ->middleware('auth');

    Route::get('/mon-dashboard', [EquipementController::class, 'statsMesEquipements'])
        ->name('mes.equipements.stats')
        ->middleware(['auth']);

    Route::get('equipements/{equipement}/history', [EquipementController::class, 'historique'])->middleware('permission:view-equipement-history')->name('equipements.history');

    Route::get('equipements/create', [EquipementController::class, 'create'])
        ->middleware('permission:create-equipement')
        ->name('equipements.create');

    Route::post('equipements', [EquipementController::class, 'store'])
        ->name('equipements.store');

    Route::get('equipements/{equipement}', [EquipementController::class, 'show'])
        ->middleware('permission:view-equipement')
        ->name('equipements.show');

    Route::get('equipements/{equipement}/edit', [EquipementController::class, 'edit'])
        ->middleware('permission:edit-equipement')
        ->name('equipements.edit');

    Route::put('equipements/{equipement}', [EquipementController::class, 'update'])
        ->name('equipements.update');

    Route::delete('equipements/{equipement}', [EquipementController::class, 'destroy'])
        ->middleware('permission:delete-equipement')
        ->name('equipements.destroy');
});

// route pour catégories d'équipement
Route::middleware(['check.status'])->group(function () {
    Route::get('categories', [CategorieEquipementController::class, 'index'])
        ->middleware('permission:list-categorie')
        ->name('categories.index');

    Route::get('categories/create', [CategorieEquipementController::class, 'create'])
        ->middleware('permission:create-categorie')
        ->name('categories.create');

    Route::post('categories', [CategorieEquipementController::class, 'store'])
        ->name('categories.store');

    Route::get('categories/{categorie}', [CategorieEquipementController::class, 'show'])
        ->middleware('permission:view-categorie')
        ->name('categories.show');

    Route::get('categories/{categorie}/edit', [CategorieEquipementController::class, 'edit'])
        ->middleware('permission:edit-categorie')
        ->name('categories.edit');

    Route::put('categories/{categorie}', [CategorieEquipementController::class, 'update'])
        ->name('categories.update');

    Route::delete('categories/{categorie}', [CategorieEquipementController::class, 'destroy'])
        ->middleware('permission:delete-categorie')
        ->name('categories.destroy');
});

// Logs
Route::middleware(['check.status'])->group(function () {
    Route::get('logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('logs/create', [LogsController::class, 'create'])->name('logs.create');
    Route::post('logs', [LogsController::class, 'store'])->name('logs.store');
    Route::get('logs/{log}', [LogsController::class, 'show'])->name('logs.show');
    Route::get('logs/{log}/edit', [LogsController::class, 'edit'])->name('logs.edit');
    Route::put('logs/{log}', [LogsController::class, 'update'])->name('logs.update');
    Route::delete('logs/{log}', [LogsController::class, 'destroy'])->name('logs.destroy');
});

Route::middleware(['check.status'])->group(function () {
    // Route pour les logs des attributions
    Route::get('attributions/logs', [AttributionController::class, 'logs'])
        ->middleware('permission:list-attribution-history')
        ->name('attributions.logs');

    // Route pour l'export des attributions
    Route::get('attributions/export', [AttributionController::class, 'export'])
        ->name('attributions.export');

    Route::get('attributions', [AttributionController::class, 'index'])
        ->middleware('permission:list-attribution')->name('attributions.index');

    Route::get('attributions/create', [AttributionController::class, 'create'])
        ->middleware('permission:create-attribution')->name('attributions.create');

    Route::post('attributions', [AttributionController::class, 'store'])
        ->middleware('permission:create-attribution')->name('attributions.store');

    Route::get('attributions/{attribution}', [AttributionController::class, 'show'])
        ->middleware('permission:view-attribution')->name('attributions.show');

    Route::get('attributions/{attribution}/edit', [AttributionController::class, 'edit'])
        ->middleware('permission:edit-attribution')->name('attributions.edit');

    Route::put('attributions/{attribution}', [AttributionController::class, 'update'])
        ->middleware('permission:edit-attribution')->name('attributions.update');

    Route::delete('attributions/{attribution}', [AttributionController::class, 'destroy'])
        ->middleware('permission:delete-attribution')->name('attributions.destroy');
});


Route::middleware(['auth', 'check.status'])->group(function () {



    // Route to display all notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // Route to mark a single notification as read
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');


    Route::delete('/notifications/delete-selected', [NotificationController::class, 'deleteSelected'])
        ->name('notifications.deleteSelected');
});




// Authentification
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');