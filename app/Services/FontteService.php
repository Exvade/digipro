<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Mesin;
use App\Models\PlanningProduksi;
use Carbon\Carbon;

class FontteService
{
    protected $apiUrl;
    protected $token;
    protected $target; // Group ID or Phone Number

    public function __construct()
    {
        $this->apiUrl = 'https://api.fonnte.com/send';
        $this->token = env('FONNTE_TOKEN', '');
        // Prioritize FONNTE_TARGET, fallback to FONNTE_GROUP_ID
        $this->target = env('FONNTE_TARGET', env('FONNTE_GROUP_ID', ''));
    }

    /**
     * Send notification to WhatsApp group
     *
     * @param string $message
     * @return array
     */
    public function sendGroupNotification($message)
    {
        try {
            if (empty($this->token)) {
                Log::warning('Fonnte token not configured in .env (FONNTE_TOKEN)');
                return [
                    'success' => false,
                    'message' => 'Fonnte token not configured'
                ];
            }

            if (empty($this->target)) {
                // Check for the old/confusing variable name just in case
                $this->target = env('FONNTE_GROUP_URL', '');
            }

            if (empty($this->target)) {
                Log::warning('Fonnte target not configured in .env (FONNTE_TARGET or FONNTE_GROUP_ID)');
                return [
                    'success' => false,
                    'message' => 'Fonnte target not configured'
                ];
            }

            // Validate if target is a URL (which is wrong for API)
            if (filter_var($this->target, FILTER_VALIDATE_URL) || strpos($this->target, 'chat.whatsapp.com') !== false) {
                Log::warning('Fonnte target appears to be a URL. It should be a Group ID (e.g., 120363xxx@g.us) or Phone Number.', ['target' => $this->target]);
                return [
                    'success' => false,
                    'message' => 'Invalid target format. Please use Group ID (found via \'php artisan fonnte:get-groups\') instead of Invite Link.'
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target' => $this->target,
                'message' => $message,
                'countryCode' => '62', // Indonesia country code
            ]);

            $result = $response->json();

            // Log full response for debugging
            Log::info('Fonnte API Response', [
                'status' => $response->status(),
                'body' => $result
            ]);

            if (isset($result['status']) && !$result['status']) {
                return [
                    'success' => false,
                    'message' => $result['reason'] ?? 'Fonnte API Error'
                ];
            }

            return [
                'success' => $response->successful(),
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send Fonnte notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Format machine status change message
     *
     * @param object $mesin
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $oldReason
     * @param string|null $newReason
     * @param string|null $keterangan
     * @return string
     */
    public function formatMachineStatusMessage($mesin, $oldStatus, $newStatus, $oldReason = null, $newReason = null, $keterangan = null)
    {
        $statusLabels = [
            'running' => ['label' => 'RUNNING', 'emoji' => '🟢'],
            'stop' => ['label' => 'STAND BY', 'emoji' => '🟡'],
            'maintenance' => ['label' => 'BREAKDOWN', 'emoji' => '🔴'],
            'offline' => ['label' => 'STOP / OFF', 'emoji' => '⚫']
        ];

        $oldInfo = $statusLabels[$oldStatus] ?? ['label' => strtoupper($oldStatus), 'emoji' => ''];
        $newInfo = $statusLabels[$newStatus] ?? ['label' => strtoupper($newStatus), 'emoji' => ''];

        $oldText = $oldInfo['label'] . ($oldReason ? " ({$oldReason})" : "");
        $newText = $newInfo['label'] . ($newReason ? " ({$newReason})" : "");

        $now = now()->locale('id');
        $message = "🏭 *LAPORAN UPDATE STATUS MESIN*\n";
        $message .= "*PT MADA WIKRI TUNGGAL – PLANT 3*\n";
        $message .= "🗓 " . $now->translatedFormat('l, d F Y') . "\n";
        $message .= "⏰ " . $now->format('H.i') . " WIB\n\n";

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🔄 *PERUBAHAN STATUS*\n";
        $message .= "🛠 {$mesin->nama_mesin}\n";
        $message .= "{$oldInfo['emoji']} Dari : {$oldText}\n";
        $message .= "{$newInfo['emoji']} Ke   : *{$newText}*\n";

        if (!empty($keterangan)) {
            $message .= "📌 Catatan: {$keterangan}\n";
        }

        $message .= "\n━━━━━━━━━━━━━━━━━━\n\n";

        $user = auth()->user();
        $userName = $user ? ($user->name ?? $user->username ?? 'User') : 'System';
        $message .= "👤 *Updated By:* {$userName}";

        return $message;
    }

    /**
     * Format full machine status report with optional change summary
     * 
     * @param object|null $mesin
     * @param string|null $oldStatus
     * @param string|null $newStatus
     * @param string|null $oldReason
     * @param string|null $newReason
     * @param string|null $keterangan
     * @return string
     */
    public function formatFullMachineReport($mesin = null, $oldStatus = null, $newStatus = null, $oldReason = null, $newReason = null, $keterangan = null)
    {
        $statusMap = [
            'running' => ['label' => 'RUNNING', 'emoji' => '🟢'],
            'stop' => ['label' => 'STAND BY', 'emoji' => '🟡'],
            'maintenance' => ['label' => 'BREAKDOWN', 'emoji' => '🔴'],
            'offline' => ['label' => 'STOP / OFF', 'emoji' => '⚫'],
        ];

        // Build Header
        $now = \Carbon\Carbon::now()->locale('id');
        $message = "🏭 *LAPORAN UPDATE STATUS MESIN*\n";
        $message .= "*PT MADA WIKRI TUNGGAL – PLANT 3*\n";
        $message .= "🗓 " . $now->translatedFormat('l, d F Y') . "\n";
        $message .= "⏰ " . $now->format('H.i') . " WIB\n\n";

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";

        // Perubahan Status (Summary)
        if ($mesin && $oldStatus && $newStatus) {
            $oldEmoji = $statusMap[$oldStatus]['emoji'] ?? '';
            $oldLabel = $statusMap[$oldStatus]['label'] ?? strtoupper($oldStatus);
            $oldText = $oldLabel . ($oldReason ? " ({$oldReason})" : "");

            $newEmoji = $statusMap[$newStatus]['emoji'] ?? '';
            $newLabel = $statusMap[$newStatus]['label'] ?? strtoupper($newStatus);
            $newText = $newLabel . ($newReason ? " ({$newReason})" : "");

            $message .= "🔄 *PERUBAHAN STATUS*\n";
            $message .= "🛠 {$mesin->nama_mesin}\n";
            $message .= "{$oldEmoji} Dari : {$oldText}\n";
            $message .= "{$newEmoji} Ke   : *{$newText}*\n";

            if (!empty($keterangan)) {
                $message .= "📌 Catatan: {$keterangan}\n";
            }

            $message .= "\n━━━━━━━━━━━━━━━━━━\n\n";
        }

        // Get all machines sorted naturally
        $machines = \App\Models\Mesin::orderByRaw('CAST(SUBSTRING_INDEX(nama_mesin, " ", -1) AS UNSIGNED)')->get();

        $groups = [
            'maintenance' => [],
            'stop' => [],
            'offline' => [],
            'running' => []
        ];

        foreach ($machines as $m) {
            $groups[$m->status][] = $m;
        }

        // 🔴 BREAKDOWN SECTION
        if (!empty($groups['maintenance'])) {
            $count = count($groups['maintenance']);
            $message .= "🔴 *BREAKDOWN ({$count} MC)*\n";
            foreach ($groups['maintenance'] as $m) {
                $message .= "{$m->nama_mesin}\n";
                $reason = $m->stop_reason ?? 'Dalam Perbaikan';
                $message .= "* {$reason}\n";
            }
            $message .= "\n";
        }

        // 🟡 STANDBY SECTION
        if (!empty($groups['stop'])) {
            $count = count($groups['stop']);
            $message .= "🟡 *STANDBY ({$count} MC)*\n";
            foreach ($groups['stop'] as $m) {
                $reason = $m->stop_reason ?? 'Menunggu';
                $message .= "{$m->nama_mesin} – {$reason}\n";
            }
            $message .= "\n";
        }

        // ⚫ STOP / OFF SECTION
        if (!empty($groups['offline'])) {
            $count = count($groups['offline']);
            $message .= "⚫ *STOP / OFF ({$count} MC)*\n";
            foreach ($groups['offline'] as $m) {
                $reason = $m->stop_reason ?? 'Tidak ada jadwal produksi';
                $message .= "{$m->nama_mesin} – {$reason}\n";
            }
            $message .= "\n";
        }

        // 🟢 RUNNING SECTION
        if (!empty($groups['running'])) {
            $count = count($groups['running']);
            $message .= "🟢 *RUNNING ({$count} MC)*\n";
            foreach ($groups['running'] as $m) {
                $message .= "{$m->nama_mesin}\n";
            }
            $message .= "\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n\n";

        $user = auth()->user();
        $userName = $user ? ($user->name ?? $user->username ?? 'User') : 'System';
        $message .= "👤 *Updated By:* {$userName}";

        return trim($message);
    }
}
