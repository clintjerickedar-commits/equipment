@if(config('services.recaptcha.site'))
    <div class="mb-3">
        <div class="g-recaptcha" data-theme="dark" data-sitekey="{{ config('services.recaptcha.site') }}"></div>
        @error('g-recaptcha-response') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
