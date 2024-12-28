<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductController2;
use App\Http\Controllers\ProductController3;
use App\Http\Controllers\ProductController4;
use App\Http\Controllers\GroupController;

Route::post('/products/add-to-list', [ProductController::class, 'addProductToList'])->name('products.addToList');
Route::get('/', [ProductController::class, 'index'])->name('products.index');
// Route untuk Produk 1
Route::prefix('products')->name('products.')->group(function () {
    // Tanpa prefix di dalam resource, ini akan bekerja dengan URL seperti /products/{product}/edit
    Route::resource('/', ProductController::class);

    Route::post('/send-to-whatsapp', [ProductController::class, 'sendToWhatsApp'])->name('sendToWhatsApp');
    Route::delete('/bulk-delete', [ProductController::class, 'bulkDelete'])->name('bulk-delete');
});


// Route untuk Produk 2
Route::prefix('products2')->name('products2.')->group(function () {
    Route::resource('/', ProductController2::class);  // Menggunakan Route::resource untuk CRUD
    Route::post('/send-to-whatsapp', [ProductController2::class, 'sendToWhatsApp'])->name('sendToWhatsApp');
    Route::delete('/bulk-delete', [ProductController2::class, 'bulkDelete'])->name('bulk-delete');
});

// Route untuk Produk 3
Route::prefix('products3')->name('products3.')->group(function () {
    Route::resource('/', ProductController3::class);  // Menggunakan Route::resource untuk CRUD
    Route::post('/send-to-whatsapp', [ProductController3::class, 'sendToWhatsApp'])->name('sendToWhatsApp');
    Route::delete('/bulk-delete', [ProductController3::class, 'bulkDelete'])->name('bulk-delete');
});

// Route untuk Produk 4
Route::prefix('products4')->name('products4.')->group(function () {
    Route::resource('/', ProductController4::class);  // Menggunakan Route::resource untuk CRUD
    Route::post('/send-to-whatsapp', [ProductController4::class, 'sendToWhatsApp'])->name('sendToWhatsApp');
    Route::delete('/bulk-delete', [ProductController4::class, 'bulkDelete'])->name('bulk-delete');
});
Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
Route::post('/groups/update', [GroupController::class, 'update'])->name('groups.update');
Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

Route::get('products2/{id}/edit', [ProductController2::class, 'edit'])->name('products2.edit');
Route::put('products2/{id}', [ProductController2::class, 'update'])->name('products2.update');

Route::get('products3/{product3}/edit', [ProductController3::class, 'edit'])->name('products3.edit');

Route::get('products4/{product4}/edit', [ProductController4::class, 'edit'])->name('products4.edit');

Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');

Route::put('products2/{id}', [Product2Controller::class, 'update'])->name('products2.update');

Route::put('product3/{product3}', [ProductController3::class, 'update'])->name('products3.update');
Route::put('product4/{product4}', [ProductController4::class, 'update'])->name('products4.update');

// Product 3 Update Route
Route::put('products3/{product3}', [ProductController3::class, 'update'])->name('products3.update');

// Product 4 Update Route
Route::put('products4/{product4}', [ProductController4::class, 'update'])->name('products4.update');

Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
// Route untuk menghapus nomor kontak yang dipilih
Route::post('/groups/delete-selected', [GroupController::class, 'deleteSelected'])->name('groups.deleteSelected');
