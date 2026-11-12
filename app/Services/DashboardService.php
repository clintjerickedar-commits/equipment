<?php

namespace App\Services;

use App\Models\BorrowingRequest;
use App\Models\BorrowingRequestItem;
use App\Models\Equipment;
use App\Models\User;

/** Every number on the dashboard comes from real database queries. */
class DashboardService
{
    public function adminStats(): array
    {
        $totalUnits = (int) Equipment::sum('quantity');
        $available  = (int) Equipment::sum('available_quantity');

        $borrowed = (int) BorrowingRequestItem::whereHas('request', fn ($q) => $q->outstanding())->sum('quantity');

        return [
            'total_types' => Equipment::count(),
            'borrowers'   => User::borrowers()->count(),
            'total'       => $totalUnits,
            'available'   => $available,
            'borrowed'    => $borrowed,
            'pending'     => BorrowingRequest::where('status', 'pending')->count(),
            'approved'    => BorrowingRequest::where('status', 'approved')->count(),
            'overdue'     => BorrowingRequest::late()->count(),
        ];
    }

    /** Extra insight numbers for the dashboard strip (all real queries). */
    public function insights(array $stats): array
    {
        $today = now()->toDateString();

        $lowStock = Equipment::where('status', 'active')->get(['quantity', 'available_quantity'])
            ->filter(fn ($e) => $e->available_quantity <= max(1, (int) floor($e->quantity * 0.25)))->count();

        return [
            'utilization' => $stats['total'] > 0 ? (int) round($stats['borrowed'] / $stats['total'] * 100) : 0,
            'due_soon'    => BorrowingRequest::outstanding()->whereBetween('expected_return_date', [$today, now()->addDays(3)->toDateString()])->count(),
            'this_month'  => BorrowingRequest::where('created_at', '>=', now()->startOfMonth())->count(),
            'low_stock'   => $lowStock,
        ];
    }

    /** Most borrowed equipment (units across approved, overdue and returned requests). */
    public function topEquipment(int $limit = 5)
    {
        return BorrowingRequestItem::query()
            ->selectRaw('equipment_id, SUM(quantity) as total')
            ->whereNotNull('equipment_id') // equipment that still exists
            ->whereHas('request', fn ($q) => $q->whereIn('status', ['approved', 'overdue', 'returned']))
            ->groupBy('equipment_id')->orderByDesc('total')->take($limit)
            ->with('equipment')->get();
    }

    /**
     * Recent activity rows, fully prepared for the view (icon, colour, wording, equipment list),
     * so the Blade template needs no PHP of its own.
     */
    public function activityFeed(int $limit = 7): array
    {
        $map = [
            'pending'   => ['inbox', 'amber', 'requested'],
            'approved'  => ['check2-circle', 'green', 'had a request approved:'],
            'rejected'  => ['x-circle', 'red', 'had a request rejected:'],
            'returned'  => ['arrow-return-left', 'slate', 'returned'],
            'overdue'   => ['alarm', 'red', 'is overdue with'],
            'cancelled' => ['slash-circle', 'slate', 'cancelled a request for'],
        ];

        return $this->recentActivity($limit)->map(function ($r) use ($map) {
            [$icon, $tone, $verb] = $map[$r->display_status] ?? ['circle', 'slate', ''];

            return [
                'request' => $r,
                'icon'    => $icon,
                'tone'    => $tone,
                'verb'    => $verb,
                'items'   => $r->items->map(fn ($i) => $i->display_name . ' ×' . $i->quantity)->implode(', '),
                'when'    => $r->updated_at,
            ];
        })->all();
    }

    /** Latest changes to requests, newest first (for the "Recent activity" feed). */
    public function recentActivity(int $limit = 7)
    {
        return BorrowingRequest::with('user', 'items.equipment')->orderByDesc('updated_at')->take($limit)->get();
    }

    /** Requests created per month for the last 6 months. */
    public function monthlyActivity(): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $counts = BorrowingRequest::where('created_at', '>=', $start)->get(['created_at'])
            ->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map->count();

        $labels = $data = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M Y');
            $data[]   = $counts[$month->format('Y-m')] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function availability(array $stats): array
    {
        $unavailable = max(0, $stats['total'] - $stats['available'] - $stats['borrowed']);
        return [
            'labels' => ['Available', 'Borrowed', 'Other'],
            'data'   => [$stats['available'], $stats['borrowed'], $unavailable],
        ];
    }

    public function requestStatistics(): array
    {
        $counts = BorrowingRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $labels = $data = [];
        foreach (BorrowingRequest::STATUSES as $status) {
            $labels[] = ucfirst($status);
            $data[]   = (int) ($counts[$status] ?? 0);
        }
        return compact('labels', 'data');
    }
}
