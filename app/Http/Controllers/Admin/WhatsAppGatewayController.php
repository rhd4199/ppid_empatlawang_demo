<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\WhatsAppGateway;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsAppGatewayController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp.index', [
            'wa' => WhatsAppGateway::settings(),
            'webhookUrl' => route('api.whatsapp.webhook', ['secret' => WhatsAppGateway::webhookSecret()]),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(WhatsAppGateway::PROVIDERS))],
            'api_url' => 'nullable|url:http,https|max:500',
            'token' => 'nullable|string|max:2000',
            'sender' => 'nullable|string|max:60',
            'enabled' => 'nullable|boolean',
            'auto_reply' => 'nullable|boolean',
            'clear_token' => 'nullable|boolean',
        ]);

        // Empty token field = keep the stored one; it is never sent back to the browser.
        $token = $request->boolean('clear_token') ? '' : (trim($data['token'] ?? '') ?: null);
        WhatsAppGateway::save(['enabled' => $request->boolean('enabled'), 'auto_reply' => $request->boolean('auto_reply')] + $data, $token);

        return redirect()->route('admin.whatsapp.index')->with('success', 'Pengaturan WhatsApp Gateway disimpan.');
    }

    public function test(Request $request)
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
            'test_message' => 'nullable|string|max:1000',
        ]);

        $text = trim($data['test_message'] ?? '') ?: 'Pesan uji WhatsApp Gateway PPID Kabupaten Empat Lawang.';
        $result = WhatsAppGateway::send($data['test_phone'], $text, force: true);

        return redirect()->route('admin.whatsapp.index')
            ->withInput($request->only('test_phone', 'test_message'))
            ->with($result['ok'] ? 'success' : 'error', $result['ok']
                ? 'Pesan uji terkirim ke ' . WhatsAppGateway::normalize($data['test_phone']) . '.'
                : $result['error']);
    }
}
