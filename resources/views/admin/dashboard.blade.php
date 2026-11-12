@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="card welcome mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p><strong class="text-white">{{ $stats['pending'] }}</strong> request(s) waiting for review · <strong class="text-white">{{ $stats['overdue'] }}</strong> overdue return(s) · <strong class="text-white">{{ $insights['due_soon'] }}</strong> due in the next 3 days</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="btn btn-primary btn-sm"><i class="bi bi-inbox"></i> Review requests</a>
            <a href="{{ route('admin.equipment.create') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-plus-lg"></i> Add equipment</a>
            <a href="{{ route('admin.returns.index') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-arrow-return-left"></i> Returns</a>
            <a href="{{ route('admin.reports') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="box-seam" tone="navy" label="Total equipment" :value="$stats['total']" :hint="$stats['total_types'].' item types'" /></div>
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="people" tone="blue" label="Total borrowers" :value="$stats['borrowers']" /></div>
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="hourglass-split" tone="amber" label="Pending requests" :value="$stats['pending']" /></div>
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="patch-check" tone="green" label="Approved requests" :value="$stats['approved']" /></div>
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="box-arrow-up-right" tone="slate" label="Currently borrowed" :value="$stats['borrowed']" /></div>
    <div class="col-6 col-md-4 col-xl-2"><x-stat-card icon="alarm" tone="red" label="Overdue equipment" :value="$stats['overdue']" /></div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3"><div class="card mini-stat">
        <div class="stat-icon sm tone-red"><i class="bi bi-speedometer2"></i></div>
        <div class="flex-grow-1"><div class="lbl">Equipment utilization</div><div class="val">{{ $insights['utilization'] }}%</div>
            <div class="bar-track mt-1"><div class="bar-fill" style="width: {{ $insights['utilization'] }}%"></div></div></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card mini-stat">
        <div class="stat-icon sm tone-amber"><i class="bi bi-calendar-event"></i></div>
        <div><div class="lbl">Due in next 3 days</div><div class="val">{{ $insights['due_soon'] }}</div></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card mini-stat">
        <div class="stat-icon sm tone-green"><i class="bi bi-graph-up-arrow"></i></div>
        <div><div class="lbl">Requests this month</div><div class="val">{{ $insights['this_month'] }}</div></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="card mini-stat">
        <div class="stat-icon sm tone-red"><i class="bi bi-exclamation-triangle"></i></div>
        <div><div class="lbl">Low-stock items</div><div class="val">{{ $insights['low_stock'] }}</div></div>
    </div></div>
</div>

<div class="row g-2 mb-3">
    <div class="col-xl-5"><div class="card h-100"><div class="card-header">Monthly borrowing activity</div><div class="card-body"><canvas id="chartMonthly" height="150"></canvas></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-header">Equipment availability</div><div class="card-body d-flex align-items-center justify-content-center"><canvas id="chartAvail" style="max-height:200px"></canvas></div></div></div>
    <div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-header">Request statistics</div><div class="card-body"><canvas id="chartStatus" height="150"></canvas></div></div></div>
</div>

<div class="row g-2">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">Recent activity <a href="{{ route('admin.history') }}" class="small fw-semibold">View history</a></div>
            <ul class="activity">
                @forelse($activity as $row)
                    <li>
                        <span class="dot tone-{{ $row['tone'] }}"><i class="bi bi-{{ $row['icon'] }}"></i></span>
                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('admin.requests.show', $row['request']) }}" class="fw-semibold text-dark">{{ $row['request']->user->name }}</a>
                            <span class="text-muted">{{ $row['verb'] }}</span> {{ $row['items'] }}
                        </div>
                        <small class="text-muted text-nowrap" title="{{ $row['when']->format('M j, Y g:i A') }}">{{ $row['when']->diffForHumans() }}</small>
                    </li>
                @empty
                    <li><x-empty-state icon="activity" message="No activity yet." /></li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 d-flex flex-column gap-2">
        <div class="card">
            <div class="card-header">Most borrowed equipment</div>
            <ul class="rank-list">
                @forelse($top as $i => $row)
                    <li>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="n">{{ $i + 1 }}</span>
                            <span class="flex-grow-1 text-truncate fw-semibold">{{ $row->display_name }}</span>
                            <span class="text-muted small">{{ (int) $row->total }} units</span>
                        </div>
                        <div class="bar-track"><div class="bar-fill" style="width: {{ round($row->total / $topMax * 100) }}%"></div></div>
                    </li>
                @empty
                    <li><x-empty-state icon="bar-chart" message="No borrowing data yet." /></li>
                @endforelse
            </ul>
        </div>
        <div class="card flex-grow-1">
            <div class="card-header">Upcoming returns <span class="small text-muted">next 7 days</span></div>
            <div class="list-group list-group-flush">
                @forelse($dueList as $r)
                    <a href="{{ route('admin.requests.show', $r) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between gap-2">
                            <strong class="text-truncate">{{ $r->user->name }}</strong>
                            <small class="text-nowrap {{ $r->expected_return_date->isToday() ? 'text-warning' : 'text-muted' }}">{{ $r->expected_return_date->isToday() ? 'Today' : $r->expected_return_date->format('M j') }}</small>
                        </div>
                        <small class="text-muted">{{ $r->items->map(fn ($i) => $i->display_name.' ×'.$i->quantity)->implode(', ') }}</small>
                    </a>
                @empty
                    <x-empty-state icon="calendar-check" message="No returns due this week." />
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card mb-2">
            <div class="card-header">Waiting for review <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="small fw-semibold">View all</a></div>
            <div class="list-group list-group-flush">
                @forelse($latest as $r)
                    <a href="{{ route('admin.requests.show', $r) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between"><strong>{{ $r->user->name }}</strong><small class="text-muted">{{ $r->created_at->diffForHumans() }}</small></div>
                        <small class="text-muted">{{ $r->items->map(fn ($i) => $i->display_name.' ×'.$i->quantity)->implode(', ') }}</small>
                    </a>
                @empty
                    <x-empty-state icon="inbox" message="No pending requests." />
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-header">Overdue returns <a href="{{ route('admin.returns.index', ['filter' => 'overdue']) }}" class="small fw-semibold">View all</a></div>
            <div class="list-group list-group-flush">
                @forelse($lateList as $r)
                    <a href="{{ route('admin.requests.show', $r) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between"><strong>{{ $r->user->name }}</strong><small class="text-danger">Due {{ $r->expected_return_date->format('M j') }}</small></div>
                        <small class="text-muted">{{ $r->items->map(fn ($i) => $i->display_name.' ×'.$i->quantity)->implode(', ') }}</small>
                    </a>
                @empty
                    <x-empty-state icon="emoji-smile" message="Nothing is overdue." />
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const monthly = @json($monthly), avail = @json($availability), status = @json($requestStats);
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#A3A3A3';
    Chart.defaults.borderColor = '#2B2B2B';
    new Chart(document.getElementById('chartMonthly'), {
        type: 'bar',
        data: { labels: monthly.labels, datasets: [{ label: 'Requests', data: monthly.data, backgroundColor: '#E00000', hoverBackgroundColor: '#FF1A1A', borderRadius: 8, maxBarThickness: 38 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#2B2B2B' } }, x: { grid: { display: false } } } }
    });
    new Chart(document.getElementById('chartAvail'), {
        type: 'doughnut',
        data: { labels: avail.labels, datasets: [{ data: avail.data, backgroundColor: ['#22C55E', '#FF0000', '#3F3F46'], borderWidth: 0 }] },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, cutout: '68%' }
    });
    new Chart(document.getElementById('chartStatus'), {
        type: 'bar',
        data: { labels: status.labels, datasets: [{ data: status.data, backgroundColor: ['#F59E0B', '#22C55E', '#FF0000', '#E5E5E5', '#FF7A45', '#71717A'], borderRadius: 6 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#2B2B2B' } }, y: { grid: { display: false } } } }
    });
</script>
@endpush
