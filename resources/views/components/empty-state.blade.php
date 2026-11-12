@props(['icon' => 'inbox', 'message' => 'Nothing to show yet.'])
<div class="empty-state">
    <i class="bi bi-{{ $icon }}"></i>
    <p class="mt-2 mb-0">{{ $message }}</p>
    {{ $slot }}
</div>
