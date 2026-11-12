{{-- Success / error / info messages appear as toasts that fade after 5 seconds --}}
@php($toasts = collect(['success' => 'success', 'error' => 'danger', 'status' => 'info'])->filter(fn ($t, $k) => session($k)))
@if($toasts->isNotEmpty())
    <div class="toast-stack no-print" id="toastStack">
        @foreach($toasts as $key => $type)
            <div class="toast-card {{ $type }}" role="status">
                <i class="bi {{ $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') }}"></i>
                <div>{{ session($key) }}</div>
                <button type="button" class="btn-close no-loading" aria-label="Close" onclick="this.parentElement.remove()"></button>
            </div>
        @endforeach
    </div>
    <script>setTimeout(() => document.querySelectorAll('#toastStack .toast-card').forEach(t => { t.style.transition = 'opacity .4s'; t.style.opacity = 0; setTimeout(() => t.remove(), 450); }), 5000);</script>
@endif

{{-- Business-rule errors (stock, duplicate return, etc.) stay visible inline; field errors show under each input --}}
@if($errors->has('status') || $errors->has('items'))
    <div class="alert alert-danger d-flex gap-2 no-print" role="alert">
        <i class="bi bi-exclamation-octagon"></i>
        <div>
            @foreach($errors->get('status') as $e) <div>{{ $e }}</div> @endforeach
            @foreach($errors->get('items') as $e) <div>{{ $e }}</div> @endforeach
        </div>
    </div>
@endif
