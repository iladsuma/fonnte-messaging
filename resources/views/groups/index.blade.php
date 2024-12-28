@extends('layouts.app')

@section('title', 'Daftar Grup WhatsApp')

@section('content')
    <h1>Daftar Grup WhatsApp</h1>

    <!-- Tombol untuk memperbarui daftar grup -->
    <form action="{{ route('groups.update') }}" method="POST" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary">Perbarui Daftar Grup</button>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Tabel daftar grup -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Grup</th>
                <th>Nama Grup</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groupList as $group)
                <tr>
                    <td>{{ $group['id'] }}</td>
                    <td>{{ $group['name'] }}</td>
                    
                </tr>
            @empty
                <tr>
                    <td colspan="2">Tidak ada grup tersedia. Perbarui daftar terlebih dahulu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
