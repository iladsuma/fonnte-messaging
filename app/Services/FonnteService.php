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
        $filePath = storage_path('app/public/' . $product->image);
        $postData = [
            'target' => $phoneNumber, // Use the selected phone number
            'message' => $template->template_content,
            'file' => new \CURLFile($filePath),
            'schedule' => 0,
            'typing' => false,
            'delay' => '2',
            'countryCode' => '62',
        ];
    
        $response = Http::withHeaders([
            'Authorization' => 'WLYy94BAnsLrgb15zLsx' // Token Fonnte Anda
        ])->post('https://api.fonnte.com/send', $postData);
    
    // Debug: Display the response to check the result
    dd($response->json(), $response->status());

        // Debug: Lihat respons dari API
    
}    
}