@props(['status'])
@php($label = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'returned' => 'Returned', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled',
    'active' => 'Active', 'maintenance' => 'Maintenance', 'retired' => 'Retired', 'inactive' => 'Deactivated'][$status] ?? ucfirst($status))
<span class="badge-soft b-{{ $status }}">{{ $label }}</span>
