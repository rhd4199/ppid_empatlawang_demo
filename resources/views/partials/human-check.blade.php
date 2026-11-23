{{-- Bot protection for public forms (App\Http\Middleware\HumanCheck): honeypot + Cloudflare Turnstile when configured. --}}
<div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off" value=""></label>
</div>
@if (filled(config('services.turnstile.site_key')))
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
    <div class="cf-turnstile mb-3" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="id" data-theme="light"></div>
@endif
@error('human')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
