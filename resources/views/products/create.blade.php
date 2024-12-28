@extends('layouts.app')

@section('title', 'Tambah Template')

@section('content')
    <h1>Tambah Template</h1>
    <form id="product-form">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Template</label>
            <input type="text" id="name" name="name" class="form-control">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Isi Template Pesan</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Produk (opsional)</label>
            <input type="file" id="image" name="image" class="form-control">
        </div>
        <button type="button" id="add-to-list" class="btn btn-primary">Simpan ke List</button>
        <button type="button" id="submit-products" class="btn btn-success">Simpan</button>
    </form>

    <h2>Daftar Template</h2>
    <ul id="product-list" class="list-group">
        <!-- Template sementara akan ditampilkan di sini -->
    </ul>

    <script>
        // Array untuk menyimpan template sementara
        let products = [];

        document.getElementById('add-to-list').addEventListener('click', () => {
            // Ambil data dari form
            const name = document.getElementById('name').value;
            const description = document.getElementById('description').value;
            const imageInput = document.getElementById('image');

            // Validasi name dan description, gambar tidak wajib
            if (!name || !description) {
                alert('Harap isi semua data template!');
                return;
            }

            // Jika ada gambar, baca gambar, jika tidak biarkan kosong
            const product = {
                name,
                description,
                image: imageInput.files[0] ? URL.createObjectURL(imageInput.files[0]) : null, // Gambar opsional
            };

            // Tambahkan template ke array
            products.push(product);

            // Render produk ke daftar
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.innerHTML = `
                <div>
                    <strong>Gambar:</strong>
                    ${product.image ? `<img src="${product.image}" alt="${product.name}" style="max-height: 100px; margin-right: 10px;">` : 'Tidak ada gambar'}
                </div>
                <div>
                    <strong>Nama Template:</strong> ${product.name}
                </div>
                <div>
                    <strong>Deskripsi:</strong> ${product.description}
                </div>
            `;
            document.getElementById('product-list').appendChild(listItem);

            // Reset form
            document.getElementById('product-form').reset();
        });

        document.getElementById('submit-products').addEventListener('click', () => {
            if (products.length === 0) {
                alert('Tidak ada template dalam daftar untuk disimpan.');
                return;
            }

            console.log('Mengirim template ke server:', JSON.stringify({ products })); // Debugging

            fetch('{{ route('products.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ products })
            })
            .then(response => {
                console.log('Respons server:', response); // Debugging
                if (!response.ok) {
                    throw new Error('Kesalahan saat menyimpan data!');
                }
                return response.json();
            })
            .then(data => {
                alert('Template berhasil disimpan ke database!');
                products = [];
                document.getElementById('product-list').innerHTML = '';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data.');
            });
        });
    </script>
@endsection
