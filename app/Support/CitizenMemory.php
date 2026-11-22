<?php

namespace App\Support;

use App\Models\WaConversation;

/**
 * Per-citizen memory for the WhatsApp assistant: what the officer recorded (contact name, label, notes) plus short
 * facts the AI saved itself with the ingat_warga tool (wa_conversations.ai_memory, officers can edit it in the inbox).
 * The chat history (last messages) is sent separately as conversation turns.
 */
class CitizenMemory
{
    const MAX = 1500; // characters kept; oldest lines drop first

    public const TOOL = ['type' => 'function', 'function' => [
        'name' => 'ingat_warga',
        'description' => 'Simpan fakta singkat yang berguna untuk percakapan berikutnya dengan warga ini (mis. nama panggilan, desa/kecamatan, sedang mengurus apa, nomor registrasi permohonan, preferensi). Jangan simpan NIK, nomor KK, foto KTP, data kesehatan atau data pribadi sensitif lain.',
        'parameters' => ['type' => 'object', 'properties' => ['catatan' => ['type' => 'string', 'description' => 'satu kalimat pendek']], 'required' => ['catatan']],
    ]];

    /** System-prompt block about this citizen. */
    public static function prompt(WaConversation $c): string
    {
        $lines = array_filter([
            'Nama: ' . ($c->displayName() ?: 'belum diketahui'),
            $c->label ? 'Topik menurut petugas: ' . (WaConversation::LABELS[$c->label] ?? $c->label) : null,
            $c->notes ? 'Catatan petugas: ' . $c->notes : null,
            $c->ai_memory ? "Yang sudah Anda ingat dari percakapan sebelumnya:\n" . $c->ai_memory : null,
        ]);

        return "Tentang warga yang sedang chat (ingatan per warga):\n- " . implode("\n- ", $lines)
            . "\nPakai ingatan ini secara wajar (mis. sapa dengan namanya, lanjutkan urusan sebelumnya) tanpa membacakannya. Bila warga menyebut fakta baru yang berguna nanti, simpan dengan tool ingat_warga.";
    }

    /** Adds one dated line; keeps the newest MAX characters. */
    public static function remember(WaConversation $c, string $note): array
    {
        $note = trim(preg_replace('/\s+/u', ' ', mb_substr($note, 0, 200)));
        // never keep long digit runs (NIK, KK, phone numbers) even if the model tries
        $note = preg_replace('/\d{8,}/u', '[angka disamarkan]', $note);
        if ($note === '') {
            return ['error' => 'Catatan kosong.'];
        }
        $memory = trim(($c->ai_memory ? $c->ai_memory . "\n" : '') . '- ' . now('Asia/Jakarta')->format('d-m-Y') . ': ' . $note);
        while (mb_strlen($memory) > self::MAX && str_contains($memory, "\n")) {
            $memory = substr($memory, strpos($memory, "\n") + 1);
        }
        $c->update(['ai_memory' => $memory]);

        return ['tersimpan' => true];
    }
}
