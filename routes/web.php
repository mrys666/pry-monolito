<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    // Rutas del Dominio: Catálogo
    Route::livewire('/productos', 'pages::catalog.index')->name('catalog.index');
    Route::livewire('/productos/crear', 'pages::catalog.create-product')->name('catalog.products.create');

    // Rutas del Dominio: Catálogo — Categorías
    Route::livewire('/categorias', 'pages::catalog.categories')->name('catalog.categories.index');
    Route::livewire('/categorias/crear', 'pages::catalog.create-category')->name('catalog.categories.create');
    Route::livewire('/categorias/{category}/editar', 'pages::catalog.edit-category')->name('catalog.categories.edit');

    // Rutas del Dominio: Inventario
    Route::livewire('/inventario', 'pages::inventory.index')->name('inventory.index');

    Route::livewire('/inventario/salida', 'pages::inventory.register-dispensation')->name('inventory.dispensation.create');
    Route::livewire('/inventario/entrada', 'pages::inventory.register-entry')->name('inventory.entry.create');
    Route::livewire('/inventario/ajuste', 'pages::inventory.adjust-stock')->name('inventory.adjustment.create');

    Route::livewire('/reportes', 'pages::reports.index')->name('reports.index');

    Route::livewire('/organizacion/usuarios', 'pages::organization.users')->name('organization.users');
    Route::livewire('/organizacion/ajustes', 'pages::organization.settings')->name('organization.settings');
});

require __DIR__ . '/settings.php';
