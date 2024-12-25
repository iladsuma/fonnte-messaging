<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        
        $groups = $response->json(); 
        $groupList = isset($groups['data']) ? $groups['data'] : [];
        
        // Ambil kata kunci pencarian dari request
        $search = $request->input('search');
    
        // Query produk berdasarkan kata kunci (jika ada)
        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->get();
        
        // Tampilkan view dengan data grup dan produk
        return view('products.index', compact('groupList', 'products', 'search'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'products' => 'required|array',
                'products.*.name' => 'required|string|max:255',
                'products.*.harga' => 'required|string',
                'products.*.description' => 'required|string',
                'products.*.image' => 'required|string', // Base64 string
            ]);
    
            foreach ($request->products as $productData) {
                $imagePath = null;
if (!empty($productData['image'])) {
    $imageData = explode(',', $productData['image'])[1]; // Ambil base64 string
    $imageName = time() . '_' . uniqid() . '.png';

    // Menyimpan gambar di storage/app/public/images
    $path = Storage::disk('public')->put('images/' . $imageName, base64_decode($imageData));

    // Menyimpan path relatif untuk gambar di database
    $imagePath = 'images/' . $imageName;
}
    
                Product::create([
                    'name' => $productData['name'],
                    'harga' => $productData['harga'],
                    'description' => $productData['description'],
                    'image' => $imagePath,
                ]);
            }
    
            return response()->json(['success' => true], 201);
        } catch (\Exception $e) {
            \Log::error('Error saat menyimpan produk: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan produk'], 500);
        }
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
            'harga' => 'required',
            'description' => 'required',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->update([
            'name' => $request->name,
            'harga' => $request->harga,
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
          'selected_products.*' => 'exists:products,id',
          'group_id' => 'required|array',
          'group_id.*' => 'string',
          'schedule' => 'nullable|date',
      ]);
  
      // Ambil produk yang dipilih
      $products = Product::whereIn('id', $request->selected_products)->get();
      // Ambil ID grup yang dipilih
      $groupIds = $request->input('group_id');
      // Ambil jadwal (jika ada)
      $schedule = $request->input('schedule');
      // Konversi jadwal awal ke timestamp jika ada
      $scheduleTimestamp = $schedule ? strtotime($schedule . "+0700") : null;
  
      // Jika ada jadwal
      if ($scheduleTimestamp) {
          // Iterasi untuk pengiriman pesan (hari ini, besok, 2 hari setelahnya)
          for ($i = 0; $i < 2; $i++) {
              // Tentukan timestamp untuk pengiriman pertama (hari ini) atau kedua (2 hari setelahnya)
              $currentScheduleTimestamp = $scheduleTimestamp + (172800 * $i); // Tambah 2 hari untuk iterasi kedua
  
              // Kirim produk yang dipilih untuk setiap grup
              foreach ($products as $product) {
                  foreach ($groupIds as $groupId) {
                      $randomDelay = rand(60, 240); // Delay acak antara 60 hingga 240 detik
  
                      // Path absolut file
                      $filePath = storage_path('app/public/' . $product->image);
  
                      // Periksa apakah file ada
                      if (!file_exists($filePath)) {
                          return response()->json(['error' => 'File tidak ditemukan: ' . $filePath], 400);
                      }
  
                      // Data pesan
                      $data = [
                          'target' => $groupId,
                          'message' => $product->description,
                          'file' => new \CURLFile($filePath),
                          'schedule' => $currentScheduleTimestamp ?? now()->timestamp, // Pengiriman berdasarkan jadwal
                          'delay' => (string)$randomDelay,
                      ];
  
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
              foreach ($groupIds as $groupId) {
                  $randomDelay = rand(60, 240); // Delay acak antara 60 hingga 240 detik
  
                  // Path absolut file
                  $filePath = storage_path('app/public/' . $product->image);
  
                  // Periksa apakah file ada
                  if (!file_exists($filePath)) {
                      return response()->json(['error' => 'File tidak ditemukan: ' . $filePath], 400);
                  }
  
                  // Data pesan
                  $data = [
                      'target' => $groupId,
                      'message' => $product->description,
                      'file' => new \CURLFile($filePath),
                      'schedule' => now()->timestamp, // Kirim langsung
                      'delay' => (string)$randomDelay,
                  ];
  
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