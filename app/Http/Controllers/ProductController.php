<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Import Http facade
use App\Http\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

set_time_limit(1000);  // Mengatur batas waktu eksekusi menjadi 5 menit

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar grup dari API Fonnte
        $response = Http::withHeaders([
            'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Token Fonnte Anda
        ])->post('https://api.fonnte.com/get-whatsapp-group');  // Ambil daftar grup
        
        $groups = Phone::all();
        
        // Ambil kata kunci pencarian dari request
        $search = $request->input('search');
    
        // Query produk berdasarkan kata kunci (jika ada)
        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->get();
        
        // Tampilkan view dengan data grup dan produk
        return view('products.index', compact('groups', 'products', 'search'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'products.*.name' => 'required|string|max:255',
            'products.*.description' => 'required|string',
            'products.*.image' => 'nullable|image',  // Pastikan image opsional
        ]);
    
        foreach ($request->products as $productData) {
            $product = new Product();
            $product->name = $productData['name'];
            $product->description = $productData['description'];
    
            if (isset($productData['image'])) {
                // Jika ada gambar, simpan gambar
                $imagePath = $productData['image']; // Gambar dalam format base64
                // Proses gambar untuk disimpan, misalnya dengan menyimpan path gambar
                // Misalnya, kamu dapat menyimpannya di folder storage
                $imageName = uniqid() . '.png'; // Ganti dengan ekstensi yang sesuai
                $path = public_path('images/' . $imageName);
                file_put_contents($path, base64_decode($imagePath));  // Menyimpan gambar
    
                $product->image = 'images/' . $imageName;  // Simpan path gambar
            } else {
                // Jika tidak ada gambar, beri nilai default (misalnya gambar default atau string kosong)
                $product->image = '';  // Atau 'images/default.png'
            }
    
            $product->save();
        }
    
        return response()->json(['message' => 'Produk berhasil disimpan']);
    }
    
    private function saveImage($imageBase64)
    {
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));
        $imageName = uniqid() . '.png';
        Storage::disk('public')->put("images/{$imageName}", $image);
    
        return "images/{$imageName}";
    }
    


public function edit(Product $product)
{
    return view('products.edit', compact('product'));
}


    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
           
            'description' => 'required',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->update([
            'name' => $request->name,
           
            'description' => $request->description,
        ]);

        return redirect()->route('products.index');
    }

    
    

  // Metode untuk mengirim pesan ke WhatsApp
  public function sendMessageToWhatsApp($target, $message, $countryCode = '62')
  {
      // Inisialisasi cURL
      $curl = curl_init();

      // Menyiapkan pengaturan cURL
      curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
              'target' => $target, // Target nomor WhatsApp atau grup
              'message' => $message, // Pesan yang akan dikirim
              'countryCode' => $countryCode, // Kode negara, default '62' untuk Indonesia
              
            
          ),
          CURLOPT_HTTPHEADER => array(
              'Authorization: WLYy94BAnsLrgb15zLsx' // Ganti 'TOKEN' dengan token API Anda
          ),
      ));

      // Eksekusi cURL untuk mengirimkan pesan
      $response = curl_exec($curl);
      
      // Jika terjadi error pada cURL
      if (curl_errno($curl)) {
          $error_msg = curl_error($curl);
      }
      
      // Tutup koneksi cURL
      curl_close($curl);

      // Cek apakah ada error
      if (isset($error_msg)) {
          return response()->json(['error' => $error_msg], 500);
      }

      // Kembalikan respons dari API Fonnte
      return response()->json(['response' => $response], 200);
  }
  public function sendToWhatsApp(Request $request)
{
    $request->validate([
        'selected_products' => 'required|array',
        'selected_products.*' => 'exists:products,id', // Pastikan produk ada di tabel products
        'number' => 'required|array|min:1', // Harus berupa array, minimal pilih satu
        'number.*' => 'string|regex:/^\d{10,15}$/', // Validasi nomor telepon (10-15 digit angka)
        'schedule' => 'nullable|date', // Format tanggal opsional
    ]);

    // Ambil produk yang dipilih
    $products = Product::whereIn('id', $request->selected_products)->get();

    // Ambil nomor telepon yang dipilih
    $phoneNumbers = $request->input('number');

    // Ambil jadwal (jika ada)
    $schedule = $request->input('schedule');

    // Konversi jadwal ke timestamp (dengan zona waktu +0700 untuk Waktu Indonesia Barat)
    $scheduleTimestamp = $schedule ? strtotime($schedule . " +0700") : null;

    // Jika ada jadwal
    if ($scheduleTimestamp) {
        // Iterasi untuk pengiriman pesan (hari ini, besok, 2 hari setelahnya)
        for ($i = 0; $i < 2; $i++) {
            // Tentukan timestamp untuk pengiriman pertama (hari ini) atau kedua (2 hari setelahnya)
            $currentScheduleTimestamp = $scheduleTimestamp + (172800 * $i); // Tambah 2 hari untuk iterasi kedua

            // Kirim produk yang dipilih untuk setiap grup
            foreach ($products as $product) {
                foreach ($phoneNumbers as $phoneNumber) {
                    $randomDelay = rand(60, 240); // Delay acak antara 60 hingga 240 detik

                    // Data pesan
                    $data = [
                        'target' => $phoneNumber,
                        'message' => $product->description,
                        'schedule' => $currentScheduleTimestamp ?? now()->timestamp, // Pengiriman berdasarkan jadwal
                        'delay' => (string)$randomDelay,
                    ];

                    // Jika produk memiliki gambar, tambahkan gambar dalam request
                    if ($product->image) {
                        $filePath = storage_path('app/public/' . $product->image);

                        // Periksa apakah file ada
                        if (file_exists($filePath)) {
                            $data['file'] = new \CURLFile($filePath); // Menambahkan gambar jika ada
                        } else {
                            return response()->json(['error' => 'File gambar tidak ditemukan: ' . $filePath], 400);
                        }
                    }

                    // Kirim data ke API menggunakan cURL
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => 'https://api.fonnte.com/send',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $data, // Kirim data satu per satu
                        CURLOPT_HTTPHEADER => [
                            'Authorization: WLYy94BAnsLrgb15zLsx', // Token API
                            'Content-Type: multipart/form-data',
                        ],
                    ]);

                    $response = curl_exec($curl);

                    // Cek apakah ada kesalahan pada cURL
                    if (curl_errno($curl)) {
                        $error_msg = curl_error($curl);
                        curl_close($curl);
                        return response()->json(['error' => $error_msg], 500);
                    }

                    curl_close($curl);
                }
            }
        }
    } else {
        // Jika tidak ada jadwal, kirim langsung
        foreach ($products as $product) {
            foreach ($phoneNumbers as $phoneNumber) {
                $randomDelay = rand(60, 240); // Delay acak antara 60 hingga 240 detik

                // Data pesan
                $data = [
                    'target' => $phoneNumber,
                    'message' => $product->description,
                    'schedule' => now()->timestamp, // Kirim langsung
                    'delay' => (string)$randomDelay,
                ];

                // Jika produk memiliki gambar, tambahkan gambar dalam request
                if ($product->image) {
                    $filePath = storage_path('app/public/' . $product->image);

                    // Periksa apakah file ada
                    if (file_exists($filePath)) {
                        $data['file'] = new \CURLFile($filePath); // Menambahkan gambar jika ada
                    } else {
                        return response()->json(['error' => 'File gambar tidak ditemukan: ' . $filePath], 400);
                    }
                }

                // Kirim data ke API menggunakan cURL
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://api.fonnte.com/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data, // Kirim data satu per satu
                    CURLOPT_HTTPHEADER => [
                        'Authorization: WLYy94BAnsLrgb15zLsx', // Token API
                        'Content-Type: multipart/form-data',
                    ],
                ]);

                $response = curl_exec($curl);

                // Cek apakah ada kesalahan pada cURL
                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                    curl_close($curl);
                    return response()->json(['error' => $error_msg], 500);
                }

                curl_close($curl);
            }
        }
    }

    return redirect()->route('products.index')->with('success', 'Pesan berhasil dijadwalkan ke WhatsApp.');
}


// Metode untuk mengirim pesan ke WhatsApp dengan penjadwalan
public function sendMessageToWhatsAppWithSchedule($target, $message, $scheduleTimestamp, $countryCode = '62', $delay = null)
{
    // Inisialisasi cURL
    $curl = curl_init();

    // Menyiapkan data POST
    $postData = [
        'target' => $target,  // Nomor WhatsApp atau grup
        'message' => $message,  // Pesan yang akan dikirim
        'schedule' => $scheduleTimestamp,  // Timestamp untuk penjadwalan
        'countryCode' => $countryCode,  // Kode negara, default '62' untuk Indonesia
        
    ];

 

    // Menyiapkan pengaturan cURL
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,  // Mengirimkan data POST yang telah disiapkan
        CURLOPT_HTTPHEADER => [
            'Authorization: WLYy94BAnsLrgb15zLsx',  // Ganti 'TOKEN' dengan token API Anda
        ],
    ]);

    // Eksekusi cURL untuk mengirimkan pesan
    $response = curl_exec($curl);

    // Cek apakah ada error pada cURL
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
    }

    // Tutup koneksi cURL
    curl_close($curl);

    // Cek apakah ada error
    if (isset($error_msg)) {
        return response()->json(['error' => $error_msg], 500);
    }

    // Kembalikan respons dari API Fonnte
    return response()->json(['response' => $response], 200);
}
public function destroy($id)
{
    // Cari produk berdasarkan ID
    $product = Product::findOrFail($id);

    // Hapus produk
    $product->delete();

    return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
}

public function bulkDelete(Request $request)
{
    // Decode the selected products from JSON
    $productIds = json_decode($request->input('selected_products', '[]'), true);

    if (!is_array($productIds) || empty($productIds)) {
        return redirect()->route('products.index')->with('error', 'Tidak ada produk yang dipilih.');
    }

    // Delete the selected products
    Product::whereIn('id', $productIds)->delete();

    return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
}





}