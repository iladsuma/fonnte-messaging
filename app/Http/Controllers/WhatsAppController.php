<?php

namespace App\Http\Controllers;

use App\Services\FonnteService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'message' => 'required|string',
        ]);

        $response = $this->fonnteService->sendMessage($request->phone, $request->message);

        return response()->json($response);
    }
}
