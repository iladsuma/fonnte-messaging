@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <h1>Edit Produk</h1>
    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" required>{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Produk (opsional)</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
@endsection
