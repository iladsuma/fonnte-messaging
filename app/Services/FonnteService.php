<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $apiUrl = 'https://api.fonnte.com/send';
    protected $token;

    public function __construct()
    {
        // Masukkan API token Fonnte Anda
        $this->token = env('FONNTE_API_TOKEN');
    }
    public function sendMessage()
    {
     
        $postData = [
            'target' => '62895801205393', // Pastikan nomor di format internasional
            'message' => 'testing pesan 1',
            'schedule' => 0,
            'typing' => false,
            'delay' => '2',
            'countryCode' => '62',
        ];
    
        $response = Http::withHeaders([
            'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Token Fonnte Anda
        ])->post('https://api.fonnte.com/send', $postData);
    
        // Debug: Lihat respons dari API
    
}    
}