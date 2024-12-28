@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Kirim Pesan WhatsApp</h2>

        <!-- Menambah Nomor WhatsApp -->
        <form action="{{ url('/messages/add-number') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Nomor WhatsApp</label>
                <input type="text" name="phone_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Nomor</button>
        </form>

        <hr>


      <!-- Menambah Template Pesan -->
<form action="{{ url('/messages/add-template') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="template_name">Nama Template</label>
        <input type="text" name="template_name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="template_content">Isi Template Pesan</label>
        <textarea name="template_content" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="image">Gambar (Opsional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Tambah Template</button>
</form>


        <hr>

        <!-- Kirim Pesan -->
        <form action="{{ url('/messages/send') }}" method="POST">
            @csrf
            <div class="card-body">
    <div class="d-flex justify-content-between mb-2">
        <button type="button" class="btn btn-sm btn-primary selectall">Pilih Semua</button>
        <button type="button" class="btn btn-sm btn-secondary selectnone">Hapus Semua</button>
        <!-- Tambahkan tombol hapus semua yang dipilih -->
        <button type="button" class="btn btn-sm btn-danger deleteall">Hapus Kontak yang Dipilih</button>
    </div>

    <!-- Daftar Nomor WhatsApp dengan Checkbox -->
    <div id="whatsapp-numbers">
        @foreach ($numbers as $number)
            <div class="form-check">
                <input class="form-check-input select-contact" type="checkbox" value="{{ $number->phone_number }}" id="contact{{ $number->id }}">
                <label class="form-check-label" for="contact{{ $number->id }}">
                    {{ $number->name }} - {{ $number->phone_number }}
                </label>
            </div>
        @endforeach
    </div>
</div>


            <div class="form-group">
                <label for="template_id">Pilih Template Pesan</label>
                <select name="template_id" class="form-control" required>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success sendall">Kirim Pesan</button>
        </form>
    </div>
    <hr>

<!-- Daftar Template Pesan -->
<h3>Template Pesan</h3>
@foreach ($templates as $template)
    <div>
        <h4>{{ $template->template_name }}</h4>
        <p>{{ $template->template_content }}</p>
        @if ($template->image_path)
            <img src="{{ asset('storage/' . $template->image_path) }}" alt="Template Image" width="150">
        @endif
    </div>

        <!-- Tombol Edit Template -->
        <a href="{{ route('messages.editTemplate', $template->id) }}" class="btn btn-sm btn-warning">Edit</a>

        <!-- Tombol Hapus Template -->
        <form action="{{ route('messages.deleteTemplate', $template->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus template ini?')">Hapus</button>
        </form>
    </div>
@endforeach
<script>
document.querySelector('.sendall').addEventListener('click', function () {
    const selectedNumbers = [];
    document.querySelectorAll('.select-contact:checked').forEach(checkbox => {
        selectedNumbers.push(checkbox.value);  // Mengumpulkan nomor telepon yang dipilih
    });

    if (selectedNumbers.length === 0) {
        alert('Tidak ada kontak yang dipilih.');
        return;
    }

    // Ambil template_id dan pesan dari input
    const templateId = document.querySelector('#template_id').value;

    // Kirim request untuk mengirim pesan ke server
    fetch('/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            phone_numbers: selectedNumbers.join(','),  // Mengubah array menjadi string yang dipisahkan koma
            template_id: templateId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pesan berhasil dikirim ke semua kontak.');
        } else {
            alert('Gagal mengirim pesan.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan dalam pengiriman pesan.');
    });
});

</script>


@endsection
