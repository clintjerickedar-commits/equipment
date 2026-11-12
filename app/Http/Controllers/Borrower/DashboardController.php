<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use App\Models\Equipment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $mine = BorrowingRequest::where('user_id', $user->id);

        $stats = [
            'pending'  => (clone $mine)->where('status', 'pending')->count(),
            'active'   => (clone $mine)->outstanding()->count(),
            'overdue'  => (clone $mine)->late()->count(),
            'returned' => (clone $mine)->where('status', 'returned')->count(),
        ];

        // Return deadlines, soonest first
        $deadlines = (clone $mine)->outstanding()->with('items.equipment')
            ->orderBy('expected_return_date')->get();

        $recent = (clone $mine)->with('items.equipment')->latest()->take(5)->get();

        // Items that can be borrowed right now (fills the dashboard and gives one-click requests)
        $available = Equipment::borrowable()->with('category')->orderByDesc('available_quantity')->take(6)->get();

        $hour      = now()->hour; // uses APP_TIMEZONE from .env
        $greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName = explode(' ', trim($user->name))[0];

        return view('borrower.dashboard', compact('stats', 'deadlines', 'recent', 'available', 'greeting', 'firstName'));
    }
}
