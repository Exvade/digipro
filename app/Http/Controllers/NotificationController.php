<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checksheet;
use App\Models\DowntimeEvent;
use Carbon\Carbon;

use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['leader', 'foreman', 'kabag', 'super_admin'])) {
            return response()->json(['notifications' => []]);
        }

        $cacheKey = "user_notifs_{$user->id}_{$user->role}_" . ($user->bagian ?? 'all') . "_" . ($user->grup_id ?? 0);

        $notifications = Cache::remember($cacheKey, 15, function () use ($user) {

        // 1. Get Checksheet Notifications (Approved by Kabag)
        $checksheetQuery = \App\Models\Checksheet::with(['user', 'part', 'meja', 'mesin', 'approver'])
            ->where('approval_status', 'approved')
            ->whereNotNull('approved_by')
            ->whereHas('approver', function ($q) {
                $q->where('role', 'kabag');
            });

        // Filter by section (bagian) for roles that are section-specific (like leader)
        if ($user->role === 'leader' && $user->bagian) {
            $checksheetQuery->whereHas('user', function ($q) use ($user) {
                $q->where('bagian', $user->bagian);
            });
        }

        if ($user->grup_id && !in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            $checksheetQuery->where('grup_id', $user->grup_id);
        }

        $checksheetQuery->where('approved_at', '>=', now()->subDay());
        $checksheets = $checksheetQuery->orderBy('approved_at', 'desc')->get();

        $checksheetNotifications = $checksheets->map(function ($c) {
            return [
                'id' => 'checksheet_' . $c->id,
                'type' => 'checksheet',
                'time' => $c->approved_at,
                'time_display' => $c->approved_at ? $c->approved_at->format('H:i') : '-',
                'message' => 'Checksheet ' . ($c->meja ? 'Meja: ' . $c->meja->nama_meja : 'Mesin: ' . $c->mesin->nama_mesin) . ' telah di-approve oleh Kabag.',
                'icon' => 'fas fa-check-circle',
                'color' => 'text-success'
            ];
        });

        // 2. Get Support Call Notifications (Andon)
        $callQuery = \App\Models\DowntimeEvent::with(['mesin', 'user'])
            ->where('status', 'pending')
            ->where('tanggal', '>=', Carbon::today()->subDays(2)) // Show calls from up to 2 days ago if still pending
            ->where(function ($q) {
                $q->where('description', 'like', '%CALL:%')
                    ->orWhere('title', 'like', '%CALL:%')
                    ->orWhere('title', 'like', '%Panggilan%'); // Add more matches
            });

        // Filter by section (bagian) for roles like leader
        if ($user->role === 'leader' && $user->bagian) {
            $callQuery->whereHas('user', function ($q) use ($user) {
                $q->where('bagian', $user->bagian);
            });
        }

        // Filter calls by machine/meja group if user has group (and is not admin/kabag/manager)
        if ($user->grup_id && !in_array($user->role, ['super_admin', 'kabag', 'manager_plant'])) {
            $callQuery->whereHas('user', function ($q) use ($user) {
                $q->where('grup_id', $user->grup_id);
            });
        }

        $calls = $callQuery->orderBy('created_at', 'desc')->get();

        $callNotifications = $calls->map(function ($call) {
            $locationName = $call->mesin ? $call->mesin->nama_mesin : null;

            // If machine is not set, try to find the workbench (meja) from the operator's active plotting
            if (!$locationName) {
                $ploting = \App\Models\Ploting::with('planning.meja')
                    ->where('user_id', $call->user_id)
                    ->where('status', 'on_progress')
                    ->whereDate('created_at', Carbon::today())
                    ->latest()
                    ->first();

                if ($ploting && $ploting->planning && $ploting->planning->meja) {
                    $locationName = $ploting->planning->meja->nama_meja;
                } else {
                    $locationName = 'Area Kerja';
                }
            }

            $category = 'Support';
            if (preg_match('/CALL: (.*)/', $call->title, $matches)) {
                $category = $matches[1];
            }

            return [
                'id' => 'call_' . $call->id,
                'type' => 'call',
                'time' => $call->created_at,
                'time_display' => $call->created_at ? $call->created_at->format('H:i') : '-',
                'message' => "SOS: {$locationName} memanggil {$category}!",
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'text-danger'
            ];
        });

        // Merge and sort by time
        return $checksheetNotifications->concat($callNotifications)
            ->sortByDesc('time')
            ->values();
        });

        return response()->json([
            'notifications' => $notifications
        ]);
    }
}
