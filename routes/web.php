<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Catalog/Index');
})->name('home');

Route::get('/product/{product}', function (string $product) {
    return Inertia::render('Catalog/Show', [
        'productId' => (int) $product,
    ]);
})->name('products.show.page');

Route::get('/admin/login', function () {
    return Inertia::render('Admin/Login');
})->name('admin.login');

Route::get('/admin/products', function () {
    return Inertia::render('Admin/Products/Index');
})->name('admin.products.index');

Route::get('/admin/products/create', function () {
    return Inertia::render('Admin/Products/Form');
})->name('admin.products.create');

Route::get('/admin/products/{product}/edit', function (string $product) {
    return Inertia::render('Admin/Products/Form', [
        'productId' => (int) $product,
    ]);
})->name('admin.products.edit');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
