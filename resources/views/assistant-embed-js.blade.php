/* Sapa Madani (PPID Kabupaten Empat Lawang) on any site:
 *   <script src="{{ route('assistant.embed-script') }}" defer></script>
 * Adds a floating "Sapa Madani" button; the chat is the PPID panel in an iframe ({{ route('assistant.embed') }}).
 * Options on the script tag: data-offset="24" (px from the bottom), data-mobile-fab="false" (no button on phones,
 * e.g. when the site has its own bottom bar). Any element with [data-sapa-madani] opens/closes it;
 * window.SapaMadani.open() / .close() / .toggle() too. The site's origin must be in PPID's ASSISTANT_EMBED_ORIGINS. */
(function () {
    if (window.SapaMadani) return;
    var script = document.currentScript || {};
    var data = script.dataset || {};
    var FRAME = @json(route('assistant.embed'));
    var LOGO = @json(asset('assets/images/sapa-madani.png'));
    var ORIGIN = new URL(FRAME).origin;
    var offset = parseInt(data.offset || '24', 10);

    var css = ''
        + '.smx-fab{position:fixed;right:18px;bottom:' + offset + 'px;z-index:2147483000;display:flex;align-items:center;gap:10px;'
        + 'padding:6px 18px 6px 6px;border:0;border-radius:999px;color:#fff;text-align:left;cursor:pointer;font:inherit;'
        + 'background:linear-gradient(135deg,#0369a1,#075985);box-shadow:0 8px 22px rgba(3,105,161,.35);transition:transform .2s}'
        + '.smx-fab:hover{transform:translateY(-2px)}'
        + '.smx-logo{position:relative;width:42px;height:42px;flex:none;border-radius:50%;background:#fff;padding:1px}'
        + '.smx-logo img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block}'
        + '.smx-logo b{position:absolute;top:-5px;right:-7px;padding:1px 5px;border-radius:6px;background:#facc15;color:#0f172a;font-size:10px;line-height:1.3}'
        + '.smx-fab strong{display:block;font-size:15px;line-height:1.15}.smx-fab small{display:block;font-size:12px;opacity:.85}'
        + '.smx-box{position:fixed;right:18px;bottom:' + (offset + 66) + 'px;z-index:2147483001;width:400px;max-width:calc(100vw - 24px);'
        + 'height:680px;max-height:calc(100vh - ' + (offset + 86) + 'px);border-radius:20px;overflow:hidden;background:#fff;'
        + 'box-shadow:0 24px 60px rgba(15,23,42,.22),0 4px 14px rgba(15,23,42,.08);opacity:0;transform:translateY(12px) scale(.96);'
        + 'transform-origin:bottom right;transition:opacity .2s,transform .2s}'
        + '.smx-box.smx-open{opacity:1;transform:none}.smx-box iframe{width:100%;height:100%;border:0;display:block}'
        + '@media (max-width:767.98px){.smx-fab{padding:6px}.smx-fab .smx-text{display:none}'
        + (data.mobileFab === 'false' ? '.smx-fab{display:none}' : '')
        + '.smx-box{inset:0;width:100%;max-width:none;height:100%;max-height:none;border-radius:0}'
        + 'html.smx-lock,html.smx-lock body{overflow:hidden}}'
        + '@media (prefers-reduced-motion:reduce){.smx-fab,.smx-box{transition:none}}';

    var style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);

    var fab = document.createElement('button');
    fab.type = 'button';
    fab.className = 'smx-fab';
    fab.setAttribute('aria-label', 'Tanya Asisten AI Sapa Madani');
    fab.setAttribute('aria-expanded', 'false');
    fab.innerHTML = '<span class="smx-logo"><img alt=""><b>AI</b></span><span class="smx-text"><strong>Sapa Madani</strong><small>Tanya Asisten AI</small></span>';
    fab.querySelector('img').src = LOGO;

    var box = null;
    function isOpen() { return !!box && box.classList.contains('smx-open'); }
    function open() {
        if (!box) {
            box = document.createElement('div');
            box.className = 'smx-box';
            box.setAttribute('role', 'dialog');
            box.setAttribute('aria-label', 'Sapa Madani');
            var frame = document.createElement('iframe');
            frame.src = FRAME;
            frame.title = 'Sapa Madani — Asisten AI PPID Empat Lawang';
            frame.allow = 'clipboard-write';
            box.appendChild(frame);
            document.body.appendChild(box);
        }
        box.hidden = false;
        requestAnimationFrame(function () { box.classList.add('smx-open'); });
        document.documentElement.classList.add('smx-lock');
        fit();
        sync(true);
    }
    function close() {
        if (!box) return;
        box.classList.remove('smx-open');
        document.documentElement.classList.remove('smx-lock');
        setTimeout(function () { if (!isOpen()) box.hidden = true; }, 200);
        sync(false);
    }
    // phones: the keyboard shrinks only the visual viewport, so the browser pans the page and the header scrolls away;
    // pin the box to the visual viewport instead (header stays, the input rides above the keyboard)
    var vv = window.visualViewport;
    function fit() {
        if (!box) return;
        var phone = window.matchMedia('(max-width:767.98px)').matches;
        box.style.top = phone && vv ? vv.offsetTop + 'px' : '';
        box.style.height = phone && vv ? vv.height + 'px' : '';
        box.style.bottom = phone && vv ? 'auto' : '';
    }
    if (vv) { vv.addEventListener('resize', fit); vv.addEventListener('scroll', fit); }
    function toggle() { isOpen() ? close() : open(); }
    function sync(on) {
        document.querySelectorAll('.smx-fab,[data-sapa-madani]').forEach(function (b) { b.setAttribute('aria-expanded', String(on)); });
    }

    // the panel's own close button / Escape inside the frame
    window.addEventListener('message', function (e) { if (e.origin === ORIGIN && e.data === 'sapa-madani:close') close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen()) close(); });
    document.addEventListener('click', function (e) {
        var t = e.target.closest && e.target.closest('.smx-fab,[data-sapa-madani]');
        if (t) { e.preventDefault(); toggle(); }
    });

    function mount() { document.body.appendChild(fab); }
    document.body ? mount() : document.addEventListener('DOMContentLoaded', mount);
    window.SapaMadani = { open: open, close: close, toggle: toggle };
})();
