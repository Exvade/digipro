<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checksheet;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['leader', 'foreman'])) {
            return response()->json(['notifications' => []]);
        }

        $query = \App\Models\Checksheet::with(['user', 'part', 'meja', 'mesin', 'approver'])
            ->where('approval_status', 'approved')
            ->whereNotNull('approved_by')
            ->whereHas('approver', function($q) {
                $q->where('role', 'kabag');
            });

        // Filter sesuai grup user (jika ada)
        if ($user->grup_id) {
            $query->where('grup_id', $user->grup_id);
        }

        // Hanya 1 hari terakhir (bisa diubah sesuai kebutuhan)
        $query->where('approved_at', '>=', now()->subDay());

        $checksheets = $query->orderBy('approved_at', 'desc')->get();

        // Kustom format notifikasi
        $notifications = $checksheets->map(function($c) {
            return [
                'id' => $c->id,
                'part' => $c->part->nama_part ?? '-',
                'user' => $c->user->nama ?? '-',
                'meja' => $c->meja->nama_meja ?? null,
                'mesin' => $c->mesin->nama_mesin ?? null,
                'approved_at' => $c->approved_at ? $c->approved_at->format('d/m/Y H:i') : '-',
                'message' => 'Checksheet ' . ($c->meja ? 'Meja: '.$c->meja->nama_meja : 'Mesin: '.$c->mesin->nama_mesin) . ' telah di-approve oleh Kabag.',
            ];
        });

        return response()->json([
            'notifications' => $notifications
        ]);
    }
}
