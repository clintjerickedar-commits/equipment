<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        $stats = $dashboard->adminStats();
        $top   = $dashboard->topEquipment();
        $hour  = now()->hour; // uses APP_TIMEZONE from .env

        return view('admin.dashboard', [
            'greeting'     => $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening'),
            'firstName'    => explode(' ', trim(auth()->user()->name))[0],
            'stats'        => $stats,
            'insights'     => $dashboard->insights($stats),
            'top'          => $top,
            'topMax'       => max(1, (int) ($top->first()->total ?? 1)),
            'dueList'      => BorrowingRequest::with('user', 'items.equipment')->outstanding()
                ->whereBetween('expected_return_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->orderBy('expected_return_date')->take(4)->get(),
            'activity'     => $dashboard->activityFeed(),
            'latest'       => BorrowingRequest::with('user', 'items.equipment')->where('status', 'pending')->latest()->take(5)->get(),
            'lateList'     => BorrowingRequest::with('user', 'items.equipment')->late()->orderBy('expected_return_date')->take(5)->get(),
            'monthly'      => $dashboard->monthlyActivity(),
            'availability' => $dashboard->availability($stats),
            'requestStats' => $dashboard->requestStatistics(),
        ]);
    }
}
