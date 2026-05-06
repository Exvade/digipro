<?php

namespace App\Services;

use App\Models\Mesin;
use App\Models\DowntimeEvent;
use App\Models\PlanningProduksi;
use App\Models\BomDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MesinService
{
    protected FontteService $fontteService;

    public function __construct(FontteService $fontteService)
    {
        $this->fontteService = $fontteService;
    }

    public function handleStore(array $data, int $userId)
    {
        $data['status_since'] = now();
        $mesin = Mesin::create($data);

        $currentHour = (int) date('H');
        $currentShift = ($currentHour >= 7 && $currentHour < 15) ? 'pagi' : (($currentHour >= 15 && $currentHour < 23) ? 'siang' : 'malam');

        $initialEventType = $mesin->status === 'stop' && $mesin->stop_reason ? $mesin->stop_reason : $mesin->status;

        DowntimeEvent::create([
            'mesin_id' => $mesin->id,
            'user_id' => $userId,
            'tanggal' => now()->format('Y-m-d'),
            'start_time' => now()->format('H:i:s'),
            'downtime_type' => $initialEventType,
            'shift' => $currentShift,
            'status' => 'active',
            'keterangan' => 'Status awal saat registrasi mesin'
        ]);

        return $mesin;
    }

    public function handleUpdate(Mesin $mesin, array $data, int $userId)
    {
        $oldStatus = $mesin->status;
        $oldReason = $mesin->stop_reason;
        $oldKeterangan = $mesin->keterangan;
        $newStatus = $data['status'];
        $newReason = $data['stop_reason'] ?? null;

        $stateChanged = ($oldStatus !== $newStatus) || (in_array($newStatus, ['stop', 'maintenance', 'offline']) && $oldReason !== $newReason);

        if ($stateChanged) {
            $mesin->status_since = now();
        }

        $mesin->nama_mesin = $data['nama_mesin'];
        $mesin->brand_mesin = $data['brand_mesin'];
        $mesin->tonase_mesin = $data['tonase_mesin'];
        $mesin->status = $newStatus;
        $mesin->stop_reason = $newReason;
        $mesin->keterangan = $data['keterangan'] ?? null;
        $mesin->save();

        if ($newStatus === 'running' && !empty($data['part_id'])) {
            $this->managePlanningProduksi($mesin, $data);
        }

        $this->manageDowntimeEvents($mesin, $oldStatus, $newStatus, $oldReason, $newReason, $oldKeterangan, $data['keterangan'] ?? null, $stateChanged, $userId);

        if ($stateChanged) {
            $this->sendNotification($mesin, $oldStatus, $newStatus, $oldReason, $newReason, $data['keterangan'] ?? null);
        }

        return $mesin;
    }

    protected function managePlanningProduksi(Mesin $mesin, array $data)
    {
        $planning = PlanningProduksi::where('mesin_id', $mesin->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        if ($planning) {
            $planning->update([
                'part_id' => $data['part_id'],
                'qty_planning_produksi' => $data['target_produksi'],
                'lot_produksi' => $data['lot_produksi'] ?? $planning->lot_produksi ?? '-'
            ]);
        } else {
            $bomDetail = BomDetail::where('part_id', $data['part_id'])->first();

            DB::table('planning_produksi')->insert([
                'mesin_id' => $mesin->id,
                'part_id' => $data['part_id'],
                'qty_planning_produksi' => $data['target_produksi'],
                'lot_produksi' => $data['lot_produksi'] ?? '-',
                'tanggal' => now()->format('Y-m-d'),
                'bagian' => 'inject',
                'status_approval' => 'approved',
                'moldshop_id' => $bomDetail ? $bomDetail->moldshop_id : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function manageDowntimeEvents(Mesin $mesin, $oldStatus, $newStatus, $oldReason, $newReason, $oldKeterangan, $newKeterangan, $stateChanged, $userId)
    {
        $hasOpenEvents = DowntimeEvent::where('mesin_id', $mesin->id)->whereNull('end_time')->exists();
        $now = Carbon::now();
        $currentHour = (int) date('H');
        $currentShift = ($currentHour >= 7 && $currentHour < 15) ? 'pagi' : (($currentHour >= 15 && $currentHour < 23) ? 'siang' : 'malam');

        $formattedOldKeterangan = $oldStatus === 'maintenance' && $oldReason ? $oldReason . ($oldKeterangan ? " - $oldKeterangan" : "") : $oldKeterangan;

        if ($stateChanged || $hasOpenEvents) {
            $openEvents = DowntimeEvent::where('mesin_id', $mesin->id)->whereNull('end_time')->get();

            if ($stateChanged && $openEvents->isEmpty() && $oldStatus) {
                $catchUpStart = $mesin->status_since ? Carbon::parse($mesin->status_since) : $now->copy()->subMinutes(1);
                if ($catchUpStart->lt($now)) {
                    DowntimeEvent::create([
                        'mesin_id' => $mesin->id,
                        'user_id' => $userId,
                        'tanggal' => $catchUpStart->format('Y-m-d'),
                        'start_time' => $catchUpStart->format('H:i:s'),
                        'end_time' => $now->format('H:i:s'),
                        'downtime_type' => $oldStatus === 'stop' && $oldReason ? $oldReason : $oldStatus,
                        'shift' => $currentShift,
                        'duration_minutes' => max(0, $now->diffInMinutes($catchUpStart)),
                        'status' => 'active',
                        'keterangan' => $formattedOldKeterangan ?: 'Log otomatis (perubahan status)'
                    ]);
                }
            }

            foreach ($openEvents as $event) {
                $event->end_time = $now->format('H:i:s');
                if ($oldStatus === $event->downtime_type || ($oldStatus === 'stop' && !in_array($event->downtime_type, ['running', 'aktif', 'maintenance', 'offline']))) {
                    $event->keterangan = $formattedOldKeterangan;
                }
                $event->duration_minutes = max(0, $now->diffInMinutes(Carbon::parse($event->tanggal->format('Y-m-d') . ' ' . $event->start_time)));
                $event->status = 'active';
                $event->save();
            }

            $eventType = $newStatus === 'stop' && $newReason ? $newReason : $newStatus;
            $formattedNewKeterangan = $newStatus === 'maintenance' && $newReason ? $newReason . ($newKeterangan ? " - " . $newKeterangan : "") : $newKeterangan;

            DowntimeEvent::create([
                'mesin_id' => $mesin->id,
                'user_id' => $userId,
                'tanggal' => $now->format('Y-m-d'),
                'start_time' => $now->format('H:i:s'),
                'downtime_type' => $eventType,
                'shift' => $currentShift,
                'status' => 'active',
                'keterangan' => $formattedNewKeterangan
            ]);
        } else {
            $formattedUpdateKeterangan = $newStatus === 'maintenance' && $newReason ? $newReason . ($newKeterangan ? " - " . $newKeterangan : "") : $newKeterangan;
            DowntimeEvent::where('mesin_id', $mesin->id)->whereNull('end_time')->update(['keterangan' => $formattedUpdateKeterangan]);
        }
    }

    protected function sendNotification($mesin, $oldStatus, $newStatus, $oldReason, $newReason, $newKeterangan)
    {
        try {
            $message = $this->fontteService->formatFullMachineReport($mesin, $oldStatus, $newStatus, $oldReason, $newReason, $newKeterangan);
            $this->fontteService->sendGroupNotification($message);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', ['error' => $e->getMessage()]);
        }
    }

    public function handleDelete(Mesin $mesin)
    {
        $mesin->delete();
    }
}
