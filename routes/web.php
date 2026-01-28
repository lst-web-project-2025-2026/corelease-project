<?php

use Illuminate\Support\Facades\Route;

use App\Models\Resource;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Setting;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaintenanceController;

Route::get("/", [HomeController::class, "index"])->name("home");

// Maintenance Routes
Route::get("/under-maintenance", fn() => view("maintenance.under"))->name(
    "maintenance.under",
);

// Authentication & Application Routes
Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"])->name("logout");

Route::get("/apply", [AuthController::class, "showRegister"])->name("register");
Route::post("/apply", [AuthController::class, "register"]);
Route::post("/check-status", [AuthController::class, "checkStatus"])->name(
    "status.check",
);

// Resource Catalog
Route::get("/catalog", [CatalogController::class, "browse"])->name(
    "catalog.index",
);
Route::get("/maintenance", [MaintenanceController::class, "schedule"])->name(
    "maintenance.schedule",
);

// Reservations (Authenticated)
Route::middleware("auth")->group(function () {
    Route::get("/dashboard", [
        \App\Http\Controllers\DashboardController::class,
        "index",
    ])->name("dashboard");
    Route::get("/dashboard/reservations", [
        \App\Http\Controllers\DashboardController::class,
        "reservations",
    ])->name("dashboard.reservations");

    Route::get("/notifications", [
        \App\Http\Controllers\NotificationController::class,
        "index",
    ])->name("notifications.index");
    Route::patch("/notifications/{notification}/status", [
        \App\Http\Controllers\NotificationController::class,
        "updateStatus",
    ])->name("notifications.status");

    Route::post("/dashboard/reservations", [
        \App\Http\Controllers\ReservationController::class,
        "store",
    ])->name("reservations.store");
    Route::post("/dashboard/reservations/{reservation}/cancel", [
        \App\Http\Controllers\ReservationController::class,
        "cancel",
    ])->name("reservations.cancel");
    Route::post("/dashboard/reservations/{reservation}/report", [
        \App\Http\Controllers\ReservationController::class,
        "report",
    ])->name("reservations.report");

    // Manager Routes
    Route::prefix("dashboard/manager")
        ->name("dashboard.manager.")
        ->group(function () {
            Route::get("/", [
                \App\Http\Controllers\ManagerController::class,
                "index",
            ])->name("index");
            Route::get("/approvals", [
                \App\Http\Controllers\ManagerController::class,
                "approvals",
            ])->name("approvals");
            Route::post("/approvals/{reservation}/moderate", [
                \App\Http\Controllers\ManagerController::class,
                "moderateReservation",
            ])->name("approvals.moderate");
            Route::get("/inventory", [
                \App\Http\Controllers\ManagerController::class,
                "inventory",
            ])->name("inventory");
            Route::get("/inventory/create", [
                \App\Http\Controllers\ManagerController::class,
                "createResource",
            ])->name("inventory.create");
            Route::post("/inventory", [
                \App\Http\Controllers\ManagerController::class,
                "storeResource",
            ])->name("inventory.store");
            Route::post("/inventory/{resource}/toggle", [
                \App\Http\Controllers\ManagerController::class,
                "toggleStatus",
            ])->name("inventory.toggle");
            Route::get("/inventory/{resource}/maintenance", [
                \App\Http\Controllers\ManagerController::class,
                "createMaintenance",
            ])->name("inventory.maintenance");
            Route::post("/inventory/{resource}/maintenance", [
                \App\Http\Controllers\ManagerController::class,
                "storeMaintenance",
            ])->name("inventory.maintenance.store");
            Route::get("/maintenance", [
                \App\Http\Controllers\ManagerController::class,
                "maintenance",
            ])->name("maintenance");
            Route::get("/incidents", [
                \App\Http\Controllers\ManagerController::class,
                "incidents",
            ])->name("incidents");
            Route::post("/incidents/{incident}/resolve", [
                \App\Http\Controllers\ManagerController::class,
                "resolveIncident",
            ])->name("incidents.resolve");
        });

    // Admin Routes
    Route::prefix("dashboard/admin")
        ->name("dashboard.admin.")
        ->group(function () {
            Route::get("/", [
                \App\Http\Controllers\AdminController::class,
                "index",
            ])->name("index");
            Route::get("/vetting", [
                \App\Http\Controllers\AdminController::class,
                "vetting",
            ])->name("vetting");
            Route::post("/vetting/{application}/process", [
                \App\Http\Controllers\AdminController::class,
                "processVetting",
            ])->name("vetting.process");
            Route::get("/vetting/all", [
                \App\Http\Controllers\AdminController::class,
                "vettingAll",
            ])->name("vetting.all");
            Route::get("/users", [
                \App\Http\Controllers\AdminController::class,
                "users",
            ])->name("users");
            Route::post("/users/{user}/role", [
                \App\Http\Controllers\AdminController::class,
                "changeRole",
            ])->name("users.role");
            Route::post("/users/{user}/status", [
                \App\Http\Controllers\AdminController::class,
                "toggleStatus",
            ])->name("users.status");
            Route::get("/settings", [
                \App\Http\Controllers\AdminController::class,
                "settings",
            ])->name("settings");
            Route::post("/settings/toggle-maintenance", [
                \App\Http\Controllers\AdminController::class,
                "toggleMaintenance",
            ])->name("settings.toggle-maintenance");
            Route::get("/audit", [
                \App\Http\Controllers\AdminController::class,
                "audit",
            ])->name("audit");
        });
});
