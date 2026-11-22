<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Demo WhatsApp inbox: ~140 citizen chats from 1 March 2026 until now (texts in assets/wa-inbox-demo.php).
 * Not in DatabaseSeeder. Run: php artisan db:seed --class=WaInboxDemoSeeder --force (re-run replaces the demo rows).
 *
 * Phones are 628000xxxxxxx on purpose: 0800 is not a mobile prefix, so a reply from the demo can never reach a real
 * person (the gateway answers "nomor tidak terdaftar"). Rows with such a phone are the demo rows.
 */
class WaInboxDemoSeeder extends Seeder
{
    private const COUNT = 140;

    public function run(): void
    {
        $data = require database_path('seeders/assets/wa-inbox-demo.php');
        mt_srand(2026); // same demo on every run

        DB::table('wa_conversations')->where('phone', 'like', '628000%')->delete(); // messages cascade

        $start = Carbon::create(2026, 3, 1, 0, 0, 0, 'Asia/Jakarta');
        $now = now();
        $span = $now->getTimestamp() - $start->getTimestamp();
        $phones = [];

        for ($i = 0; $i < self::COUNT; $i++) {
            $s = $data['scenarios'][mt_rand(0, count($data['scenarios']) - 1)];
            $fullName = $data['names'][mt_rand(0, count($data['names']) - 1)];
            do {
                $phone = '628000' . mt_rand(1000000, 9999999);
            } while (isset($phones[$phone]));
            $phones[$phone] = true;

            // Busier towards now (more people know the number); office-ish hours 07–21 WIB.
            $at = $start->copy()->addSeconds((int) ($span * sqrt(mt_rand() / mt_getrandmax())))
                ->setTimezone('Asia/Jakarta')->setTime(mt_rand(7, 20), mt_rand(0, 59), mt_rand(0, 59))->utc(); // DB stores UTC
            if ($i >= self::COUNT - 10) {
                $at = $now->copy()->subMinutes(mt_rand(20, 72 * 60)); // a few from the last 3 days (some still unanswered)
            }
            $vars = ['{name}' => strtok($fullName, ' '), '{village}' => $data['villages'][mt_rand(0, count($data['villages']) - 1)],
                '{year}' => '2026', '{n}' => (string) mt_rand(10, 99)];

            $thread = $s['thread'];
            $times = [];
            $t = $at->copy();
            foreach ($thread as $k => [$dir, $text]) {
                $sender = $thread[$k][2] ?? null;
                if ($k > 0) {
                    $t->addSeconds(match (true) {
                        $sender === 'bot' => mt_rand(4, 20),
                        $sender === 'officer' => mt_rand(600, 86400), // 10 min – 1 day
                        default => mt_rand(60, 3600),
                    });
                }
                $times[] = $t->copy();
            }
            // Thread would end in the future: move it back.
            if (end($times)->gt($now)) {
                $shift = end($times)->diffInSeconds($now) + mt_rand(300, 7200);
                $times = array_map(fn ($x) => $x->subSeconds($shift), $times);
            }

            $last = end($times);
            $unanswered = end($thread)[0] === 'in';
            $recent = $last->gt($now->copy()->subDays(5));
            // Old unanswered chats were answered long ago (late reply).
            if ($unanswered && ! $recent) {
                $thread[] = ['out', 'Mohon maaf atas keterlambatan balasan kami, Bapak/Ibu ' . $vars['{name}'] . '. Pertanyaan Anda sudah kami teruskan ke perangkat daerah terkait; bila masih perlu, silakan ajukan lewat https://ppid.empatlawangkab.go.id/permohonan-informasi.', 'officer'];
                $times[] = $last->copy()->addSeconds(mt_rand(3600, 172800))->min($now);
                $unanswered = false;
                $last = end($times);
            }
            $failed = ! empty($s['failed']);
            $officer = collect($thread)->contains(fn ($m) => ($m[2] ?? null) === 'officer');

            $id = DB::table('wa_conversations')->insertGetId([
                'phone' => $phone,
                'name' => mt_rand(0, 9) ? $fullName : null, // some WhatsApp profiles have no name
                'contact_name' => $officer && mt_rand(0, 1) ? $fullName : null,
                'label' => $officer || mt_rand(0, 1) ? $s['label'] : null,
                'notes' => $officer ? ($s['notes'] ?? null) : null,
                'status' => match (true) {
                    $unanswered => $last->gt($now->copy()->subDay()) ? 'new' : 'read',
                    $failed => 'read',
                    default => 'done',
                },
                'handoff_reason' => match (true) {
                    $failed => 'Balasan gagal terkirim',
                    $unanswered => $s['handoff'] ?? 'Bot tidak yakin dengan maksud pertanyaan',
                    default => null,
                },
                'reply_draft' => null,
                'last_message_at' => $last,
                'created_at' => $times[0],
                'updated_at' => $last,
            ], 'id');

            $rows = [];
            foreach ($thread as $k => $m) {
                [$dir, $text] = $m;
                $outStatus = match (true) {
                    $failed && $k === count($thread) - 1 => 'failed',
                    $times[$k]->gt($now->copy()->subHours(3)) => ['sent', 'delivered'][mt_rand(0, 1)],
                    default => mt_rand(0, 6) ? 'read' : 'delivered',
                };
                $rows[] = [
                    'wa_conversation_id' => $id,
                    'direction' => $dir,
                    'sender' => ($m[2] ?? null) === 'bot' ? 'bot' : null,
                    'body' => strtr($text, $vars),
                    'status' => $dir === 'in' ? 'received' : $outStatus,
                    'error' => $dir === 'out' && $outStatus === 'failed' ? 'Larika: Nomor tidak terdaftar di WhatsApp' : null,
                    'provider_message_id' => $dir === 'in' ? "demo-{$phone}-{$k}" : null,
                    'sent_at' => $times[$k],
                    'created_at' => $times[$k],
                    'updated_at' => $times[$k],
                ];
            }
            DB::table('wa_messages')->insert($rows);
        }

        $this->command?->info(self::COUNT . ' percakapan demo WhatsApp dibuat (1 Maret 2026 s.d. sekarang).');
    }
}
