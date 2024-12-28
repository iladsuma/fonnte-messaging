<?php

namespace App\Http\Controllers;
use App\Models\Phone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        try {
            // Panggil API untuk mengambil grup
            $response = Http::withHeaders([
                'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Ganti TOKEN dengan token Fonnte Anda
            ])->post('https://api.fonnte.com/fetch-group');  // Update daftar grup
            
            // Periksa apakah pembaruan grup berhasil
            $result = $response->json();
            if (isset($result['status']) && $result['status'] === true) {
                // Setelah berhasil update, ambil daftar grup
                $response = Http::withHeaders([
                    'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Ganti dengan token Fonnte Anda
                ])->post('https://api.fonnte.com/get-whatsapp-group');  // Ambil daftar grup
                
                $groups = $response->json(); // Mendapatkan data sebagai array
    
                if (isset($groups['data'])) {
                    $groupList = $groups['data']; // Ambil daftar grup
                } else {
                    $groupList = [];
                }
    
                // Tampilkan view dengan data grup
                return view('groups.index', compact('groupList'));
            } else {
                return back()->with('error', 'Gagal memperbarui daftar grup.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil daftar grup: ' . $e->getMessage());
        }
    }
    public function create()
{
    return view('groups.create'); // Mengarahkan ke view untuk menambah nomor telepon
}


public function store(Request $request)
{
    // Validasi data
    $validated = $request->validate([
        'groups.*.phone_number' => 'required|numeric',
        'groups.*.group_name' => 'required|string|max:255',
    ]);

    $errors = [];

    foreach ($request->groups as $group) {
        // Cek apakah nomor telepon sudah ada
        $existingPhone = DB::table('phones')->where('phone_number', $group['phone_number'])->first();

        if (!$existingPhone) {
            // Jika nomor telepon belum ada, insert data baru
            DB::table('phones')->insert([
                'phone_number' => $group['phone_number'],
                'group_name' => $group['group_name'],
            ]);
        } else {
            // Jika nomor telepon sudah ada, tambahkan ke error list
            $errors[] = "Nomor telepon {$group['phone_number']} sudah ada dalam database.";
        }
    }

    if (count($errors) > 0) {
        // Kembalikan error ke frontend jika ada duplikat
        return response()->json(['status' => 'error', 'message' => implode(' ', $errors)]);
    }

    return response()->json(['status' => 'success', 'message' => 'Grup berhasil disimpan!']);
}





    public function update()
    {
        try {
            // Panggil API untuk memperbarui grup
            $response = Http::withHeaders([
                'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Ganti dengan token Fonnte Anda
            ])->post('https://api.fonnte.com/fetch-group');
            dd('Response get-whatsapp-group', $response->status(), $response->json());
            $result = $response->json();
            if (isset($result['status']) && $result['status'] === true) {
                // Setelah berhasil update, ambil daftar grup
                return redirect()->route('groups.index')->with('success', 'Daftar grup berhasil diperbarui.');
            }

            return back()->with('error', 'Gagal memperbarui daftar grup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui daftar grup: ' . $e->getMessage());
        }
    }
    public function deleteSelected(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'selected_numbers' => 'required|array', // Memastikan bahwa ada nomor yang dipilih
            'selected_numbers.*' => 'exists:phones,phone_number', // Pastikan nomor ada di tabel phones
        ]);
    
        // Menghapus nomor yang dipilih dari tabel phones
        Phone::whereIn('phone_number', $request->selected_numbers)->delete();
    
        return response()->json(['success' => 'Nomor yang dipilih berhasil dihapus.']);
    }
    
}
