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
            'running' => '🟢 RUNNING',
            'stop' => '🟡 STAND BY',
            'maintenance' => '🔴 BREAKDOWN',
            'offline' => '⚫ OFF'
        ];


        // Format Old Status
        $baseOld = $statusLabels[$oldStatus] ?? strtoupper($oldStatus);
        if ($oldStatus !== 'running' && $oldReason) {
            $oldStatusText = "{$baseOld} ({$oldReason})";
        } else {
            $oldStatusText = $baseOld;
        }

        // Format New Status
        $baseNew = $statusLabels[$newStatus] ?? strtoupper($newStatus);
        if ($newStatus !== 'running' && $newReason) {
            $newStatusText = "{$baseNew} ({$newReason})";
        } else {
            $newStatusText = $baseNew;
        }

        // Get User Name
        $user = auth()->user();
        $userName = $user ? ($user->name ?? $user->username ?? 'User') : 'System';

        // Construct Message
        $message = "🔔 *UPDATE STATUS MESIN*\n";
        $message .= "========================\n";
        $message .= "⚙️ *Info Mesin*\n";
        $message .= "Nama : *{$mesin->nama_mesin}*\n";
        $message .= "Brand : {$mesin->brand_mesin}\n";
        $message .= "Tonase : {$mesin->tonase_mesin} Ton\n\n";

        $message .= "➡️ *Perubahan*\n";
        $message .= "Dari : {$oldStatusText}\n";
        $message .= "Ke   : *{$newStatusText}*\n\n";

        if (!empty($keterangan)) {
            $message .= "📝 *Keterangan*\n";
            $message .= "_{$keterangan}_\n\n";
        }

        $message .= "👨‍🔧 *Oleh:* {$userName}\n";
        $message .= "🕒 *Waktu:* " . now()->format('d/m/Y H:i');

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
        // Change logic for Summary
        $summary = "";
        if ($mesin && $oldStatus && $newStatus) {
            $statusLabels = [
                'running' => '🟢 RUNNING',
                'stop' => '🟡 STAND BY',
                'maintenance' => '🔴 BREAKDOWN',
                'offline' => '⚫ OFF'
            ];

            // Format Old Status
            $baseOld = $statusLabels[$oldStatus] ?? strtoupper($oldStatus);
            $oldStatusText = ($oldStatus !== 'running' && $oldReason) ? "{$baseOld} ({$oldReason})" : $baseOld;

            // Format New Status
            $baseNew = $statusLabels[$newStatus] ?? strtoupper($newStatus);
            $newStatusText = ($newStatus !== 'running' && $newReason) ? "{$baseNew} ({$newReason})" : $baseNew;

            $shortName = $mesin->nama_mesin;

            $summary = "Perubahan Status\n";
            $summary .= "- {$shortName}\n";
            $summary .= "- - dari {$oldStatusText}\n";
            $summary .= "- - ke {$newStatusText}\n\n";

            if (!empty($keterangan)) {
                $summary .= "Catatan: {$keterangan}\n\n";
            }
        }

        // Get all machines sorted naturally
        $machines = \App\Models\Mesin::orderByRaw('CAST(SUBSTRING_INDEX(nama_mesin, " ", -1) AS UNSIGNED)')->get();
        $today = \Carbon\Carbon::today();

        // Categorized arrays
        $groups = [
            'BREAKDOWN' => [],
            'STANDBY' => [],
            'STOP' => [],
            'RUNNING' => []
        ];

        foreach ($machines as $m) {
            // Get Planning for Part Name
            $planning = \App\Models\PlanningProduksi::with('part')
                ->where('mesin_id', $m->id)
                ->whereDate('tanggal', $today)
                ->latest()
                ->first();

            $partName = ($planning && $planning->part) ? $planning->part->nama_part : 'No Part';
            $shortName = $m->nama_mesin;

            // Determine Category & Detail
            $category = 'STOP'; // Default
            $detail = '';

            if ($m->status === 'running') {
                $category = 'RUNNING';
            } elseif ($m->status === 'maintenance') {
                $category = 'BREAKDOWN';
                $detail = $m->stop_reason ?? 'On Maintenance';
            } elseif ($m->status === 'stop') {
                $category = 'STANDBY';
                $detail = $m->stop_reason ?? 'Wait';
            } elseif ($m->status === 'offline') {
                $category = 'STOP';
                $detail = $m->stop_reason ?? 'Off';
            }

            // Format line: Mc X - Part = Status (Detail)
            $statusDisplay = $category;
            if ($detail) {
                $statusDisplay .= " ({$detail})";
            }

            $groups[$category][] = "{$shortName} = {$statusDisplay}";
        }

        // Build Message
        $now = \Carbon\Carbon::now();
        $now->locale('id');

        $message = $summary; // Start with summary if available
        $message .= "*Laporan Update Status Mesin PT MADA WIKRI TUNGGAL PLANT 3*\n";
        $message .= $now->translatedFormat('l, d F Y') . " | " . $now->format('H.i') . "\n\n";

        // 🔴 BREAKDOWN SECTION (Non-Running First)
        if (!empty($groups['BREAKDOWN'])) {
            $count = count($groups['BREAKDOWN']);
            $message .= "🔴 *DAFTAR MESIN BREAKDOWN ({$count} MC)*\n";
            foreach ($groups['BREAKDOWN'] as $row) {
                $message .= "{$row}\n";
            }
            $message .= "\n";
        }

        // 🟡 STANDBY SECTION
        if (!empty($groups['STANDBY'])) {
            $count = count($groups['STANDBY']);
            $message .= "🟡 *DAFTAR MESIN STANDBY ({$count} MC)*\n";
            foreach ($groups['STANDBY'] as $row) {
                $message .= "{$row}\n";
            }
            $message .= "\n";
        }

        // ⚫ STOP SECTION
        if (!empty($groups['STOP'])) {
            $count = count($groups['STOP']);
            $message .= "⚫ *DAFTAR MESIN STOP (OFF) ({$count} MC)*\n";
            foreach ($groups['STOP'] as $row) {
                $message .= "{$row}\n";
            }
            $message .= "\n";
        }

        // 🟢 RUNNING SECTION (Running Last)
        if (!empty($groups['RUNNING'])) {
            $count = count($groups['RUNNING']);
            $message .= "🟢 *DAFTAR MESIN RUNNING ({$count} MC)*\n";
            foreach ($groups['RUNNING'] as $row) {
                $message .= "{$row}\n";
            }
            $message .= "\n";
        }

        $user = auth()->user();
        $userName = $user ? ($user->name ?? $user->username ?? 'User') : 'System';
        $message .= "👤 *Updated By:* {$userName}";

        return $message;
    }
}
