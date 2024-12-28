@extends('layouts.app')

@section('title', 'Tambah Kontak')

@section('content')
    <h1>Tambah Nomer</h1>
    <form id="group-form">
        <div class="mb-3">
            <label for="phone_number" class="form-label">Nomor Telepon</label>
            <input type="text" id="phone_number" name="phone_number" class="form-control">
        </div>
        <div class="mb-3">
            <label for="group_name" class="form-label">Nama Kontak</label>
            <input type="text" id="group_name" name="group_name" class="form-control">
        </div>
        <button type="button" id="add-to-list" class="btn btn-primary">Simpan ke List</button>
        <button type="button" id="submit-groups" class="btn btn-success">Simpan</button>
    </form>

    <h2>Daftar Nomer</h2>
    <ul id="group-list" class="list-group">
        <!-- Grup sementara akan ditampilkan di sini -->
    </ul>
    <script>
        // Array untuk menyimpan grup sementara
        let groups = [];

        // Event listener untuk tombol "Tambah ke Daftar"
        document.getElementById('add-to-list').addEventListener('click', () => {
            const phoneNumber = document.getElementById('phone_number').value;
            const groupName = document.getElementById('group_name').value;

            if (!phoneNumber || !groupName) {
                alert('Harap isi semua data grup!');
                return;
            }

            const group = {
                phone_number: phoneNumber,
                group_name: groupName,
            };
            groups.push(group);

            // Render grup ke daftar
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.innerHTML = `
                <div><strong>Nomor Telepon:</strong> ${group.phone_number}</div>
                <div><strong>Nama Kontak:</strong> ${group.group_name}</div>
            `;
            document.getElementById('group-list').appendChild(listItem);

            // Reset form
            document.getElementById('group-form').reset();
        });

        // Event listener untuk tombol "Simpan"
        document.getElementById('submit-groups').addEventListener('click', () => {
            if (groups.length === 0) {
                alert('Tidak ada grup dalam daftar untuk disimpan.');
                return;
            }

            console.log('Mengirim grup ke server:', JSON.stringify({ groups }));

            fetch('{{ route('groups.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ groups })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Grup berhasil disimpan!',
                        text: data.message,
                    });
                    groups = [];
                    document.getElementById('group-list').innerHTML = '';
                } else {
                    // Menampilkan error menggunakan SweetAlert2
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi kesalahan',
                        text: data.message, // Pesan error dari server
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data.');
            });
        });
    </script>

@endsection
