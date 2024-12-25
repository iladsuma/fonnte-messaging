<?php

namespace App\Http\Controllers;

use App\Services\FonnteService;
use App\Models\WhatsappNumber;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function index()
    {
        $numbers = WhatsappNumber::all();
        $templates = MessageTemplate::all();
        return view('messages.index', compact('numbers', 'templates'));
    }

    public function addNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|unique:whatsapp_numbers|numeric',
        ]);

        WhatsappNumber::create([
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->back()->with('success', 'Nomor WhatsApp berhasil ditambahkan.');
    }

    public function addTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string',
            'template_content' => 'required|string',
        ]);

        MessageTemplate::create([
            'template_name' => $request->template_name,
            'template_content' => $request->template_content,
        ]);

        return redirect()->back()->with('success', 'Template pesan berhasil ditambahkan.');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:whatsapp_numbers,phone_number',
            'template_id' => 'required|exists:message_templates,id',
        ]);

        $template = MessageTemplate::find($request->template_id);
        $target = $request->phone_number;  // Nomor WhatsApp yang akan dikirimi pesan
        $message = $template->template_content;  // Konten pesan yang diambil dari template

        $response = $this->fonnteService->sendMessage($target, $message);

        return redirect()->back()->with('success', 'Pesan berhasil dikirim.');
    }
}
