@extends('layouts.app')

@section('title', 'Daftar Kontak')

@section('content')
    <h1>Aplikasi Nomer Kontak</h1>
    <div class="container mt-4">
        <!-- Bagian Tambah & Hapus Produk -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Template</a>

            <form id="delete-form" method="POST" action="{{ route('products.bulk-delete') }}" class="d-inline">
                @csrf
                @method('DELETE')

                <!-- Hidden input field to store selected product IDs -->
                <input type="hidden" name="selected_products" id="selected_products" value="">

                <button type="submit" class="btn btn-danger bulk-delete" onclick="return confirm('Yakin ingin menghapus template yang dipilih?')">
                    Hapus Template yang Dipilih
                </button>
            </form>
        </div>
        <div class="input-group mb-3">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex mb-4">
                <input 
                    type="text" 
                    name="search" 
                    id="search-input" 
                    class="form-control me-2" 
                    placeholder="Cari Template..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>

        <form action="{{ route('products.sendToWhatsApp') }}" method="POST">
            @csrf

            <!-- Bagian Pilih Grup WhatsApp -->
            <div class="card mb-4">
                <div class="card-header">
                    Pilih Nomer Kontak
                </div>
                <div class="card-body">
    <div class="d-flex justify-content-between mb-2">
        <button type="button" class="btn btn-sm btn-primary selectall">Pilih Semua</button>
        <button type="button" class="btn btn-sm btn-secondary selectnone">Hapus Semua</button>
        <a href="{{ route('groups.create') }}" class="btn btn-success">Tambah Nomor</a>
    </div>
    <select name="number[]" id="number" class="form-control" multiple required>

        @foreach ($groups as $group)
            <option value="{{ $group->phone_number }}">
                {{ $group->group_name }} ({{ $group->phone_number }})
            </option>
        @endforeach
    </select>
    <div class="mt-3">
    <button type="button" id="delete-selected" class="btn btn-danger">Hapus Nomor yang Dipilih</button>
</div>
<div class="mt-3">
</div>
                <!-- Bagian Tombol Kirim Produk di Atas -->
                <div class="card mb-4">
                    <div class="card-header">
                        Jadwalkan Pengiriman
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="schedule">Waktu Pengiriman</label>
                            <input type="datetime-local" name="schedule" id="schedule" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success mt-2">Kirim Template pesan ke kontak</button>
                    </div>
                </div>

                <!-- Bagian Tabel Produk yang Dipilih -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all"> Pilih Semua
                                    <span id="selected-count" style="margin-left: 10px;">(0 Template Dipilih)</span>
                                </th>
                                <th>Gambar</th>
                                <th>Nama Template</th>
                                <th>Isi Template</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="select-product">
                                    </td>
                                    <td><img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" width="100"></td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->description }}</td>
                                    <td>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </form>

<script>
document.querySelector('.selectall').addEventListener('click', function () {
    // Pilih semua opsi selain opsi pertama yang kosong
    const options = $('#number option').not(':first');  // Mengabaikan opsi pertama
    const selectedValues = options.map(function () {
        return $(this).val();
    }).get();
    
    $('#number').val(selectedValues).trigger('change');  // Set nilai terpilih
});


    // Tambahkan listener untuk setiap checkbox produk
    document.querySelectorAll('.select-product').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.select-product:checked');
            const selectedCount = document.getElementById('selected-count');

            // Hitung jumlah produk yang dicentang
            selectedCount.textContent = `(${checkboxes.length} Template Dipilih)`;

            // Perbarui status "Pilih Semua" jika semua atau sebagian dicentang
            const selectAll = document.getElementById('select-all');
            selectAll.checked = checkboxes.length === document.querySelectorAll('.select-product').length;
        });
    });

    // Initialize Select2
    $('#number').select2({
        dropdownParent: $('#number').parent()
    });

    document.querySelector('.selectall').addEventListener('click', function () {
        $('#number').val($('#number option').map(function () {
            return $(this).val();
        }).get()).trigger('change');
    });

    document.querySelector('.selectnone').addEventListener('click', function () {
        $('#number').val([]).trigger('change');
    });

    // Handle "delete selected products" action
    document.querySelector('.bulk-delete').addEventListener('click', function (e) {
        e.preventDefault();  // Prevent the default form submission

        // Get all selected product checkboxes
        const selectedProducts = [];
        document.querySelectorAll('.select-product:checked').forEach(checkbox => {
            selectedProducts.push(checkbox.value);
        });

        // If no products are selected, show an alert
        if (selectedProducts.length === 0) {
            alert('Tidak ada template yang dipilih.');
            return;
        }

        // Set the selected products in the hidden input field
        document.getElementById('selected_products').value = JSON.stringify(selectedProducts);

        // Submit the form
        document.getElementById('delete-form').submit();
    });
    document.getElementById('delete-selected').addEventListener('click', function () {
    const selectedOptions = $('#number').val();  // Ambil nomor yang dipilih
    if (selectedOptions.length === 0) {
        alert('Tidak ada nomor yang dipilih.');
        return;
    }

    // Menampilkan konfirmasi sebelum menghapus
    if (confirm('Yakin ingin menghapus nomor yang dipilih?')) {
        // Mengirim permintaan POST ke server untuk menghapus nomor yang dipilih
        $.ajax({
            url: '{{ route('groups.deleteSelected') }}',  // URL untuk menghapus nomor
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',  // CSRF token
                selected_numbers: selectedOptions,  // Data nomor yang dipilih
            },
            success: function(response) {
                alert(response.success);  // Menampilkan pesan sukses
                // Menghapus kontak yang dipilih dari dropdown
                selectedOptions.forEach(function(phoneNumber) {
                    $('#number option[value="' + phoneNumber + '"]').remove();
                });

                // Perbarui nilai dropdown setelah penghapusan
                $('#number').val([]).trigger('change');
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    }
});

</script>

@endsection
