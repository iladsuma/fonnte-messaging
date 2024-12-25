@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Kirim Pesan WhatsApp</h2>

        <!-- Menambah Nomor WhatsApp -->
        <form action="{{ url('/messages/add-number') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="phone_number">Nomor WhatsApp</label>
                <input type="text" name="phone_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Nomor</button>
        </form>

        <hr>

        <!-- Menambah Template Pesan -->
        <form action="{{ url('/messages/add-template') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="template_name">Nama Template</label>
                <input type="text" name="template_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="template_content">Isi Template Pesan</label>
                <textarea name="template_content" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Template</button>
        </form>

        <hr>

        <!-- Kirim Pesan -->
        <form action="{{ url('/messages/send') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="phone_number">Pilih Nomor WhatsApp</label>
                <select name="phone_number" class="form-control" required>
                    @foreach ($numbers as $number)
                        <option value="{{ $number->phone_number }}">{{ $number->phone_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="template_id">Pilih Template Pesan</label>
                <select name="template_id" class="form-control" required>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success">Kirim Pesan</button>
        </form>
    </div>
@endsection
