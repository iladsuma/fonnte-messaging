@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Template Pesan</h2>

        <form action="{{ route('messages.updateTemplate', $template->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="template_name">Nama Template</label>
                <input type="text" name="template_name" class="form-control" value="{{ $template->template_name }}" required>
            </div>
            <div class="form-group">
                <label for="template_content">Isi Template Pesan</label>
                <textarea name="template_content" class="form-control" required>{{ $template->template_content }}</textarea>
            </div>

            <!-- Opsi untuk mengganti gambar -->
            <div class="form-group">
                <label for="image">Gambar Template</label>
                <input type="file" name="image" class="form-control">
                @if ($template->image_path)
                    <p>Gambar Saat Ini:</p>
                    <img src="{{ asset('storage/' . $template->image_path) }}" alt="Template Image" width="150">
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Perbarui Template</button>
        </form>
    </div>
@endsection
