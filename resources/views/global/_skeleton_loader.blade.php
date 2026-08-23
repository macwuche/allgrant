@php
    // 'full'   = skeleton shows immediately; the Lottie animation only joins
    //            on top of it if the page is still loading 2s later
    //            (admin panel, user dashboard — @include(...) with no
    //            $mode passed defaults here).
    // 'lottie' = Lottie-only, shown immediately, no skeleton shapes (auth
    //            pages — login/register/forgot-password/OTP — a login
    //            form isn't dashboard-shaped, so a sidebar/card skeleton
    //            wouldn't read as anything real).
    $pskl_mode = ($mode ?? 'full') === 'lottie' ? 'lottie' : 'full';
@endphp
{{--
    Full-page loading screen. Included right after <body> so it paints
    before anything else, masking the gap between "user took an action that
    leads to a new page" and "the new page is actually ready" — the gap
    that got a lot more noticeable once the DB moved off local MySQL onto a
    remote Supabase connection.

    Shown by default (no JS needed for the very first paint), hidden once
    the page finishes loading, and re-shown the instant the user clicks an
    internal link (and, in 'lottie' mode, submits a form) so it covers the
    wait on the *next* page too. Degrades safely if JS is disabled or a
    resource hangs (see <noscript> + the safety timeout below) — it never
    permanently hides the page.
--}}
<div id="page-skeleton-loader" data-psk-mode="{{ $pskl_mode }}" aria-hidden="true">
    @if ($pskl_mode === 'full')
        <div class="pskl-topbar">
            <div class="pskl-bar pskl-bar--brand"></div>
            <div class="pskl-spacer"></div>
            <div class="pskl-bar pskl-bar--pill"></div>
            <div class="pskl-circle"></div>
        </div>
        <div class="pskl-body">
            <div class="pskl-sidebar">
                @for ($i = 0; $i < 7; $i++)
                    <div class="pskl-bar pskl-bar--nav"></div>
                @endfor
            </div>
            <div class="pskl-content">
                <div class="pskl-row">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="pskl-card"></div>
                    @endfor
                </div>
                <div class="pskl-block pskl-block--tall"></div>
                <div class="pskl-block"></div>
                <div id="page-lottie-loader"></div>
            </div>
        </div>
    @else
        <div id="page-lottie-loader"></div>
    @endif
</div>

<noscript>
    <style>#page-skeleton-loader { display: none !important; }</style>
</noscript>

<style>
    #page-skeleton-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        background: #f4f6fa;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transition: opacity .25s ease, visibility 0s linear 0s;
    }

    #page-skeleton-loader[data-psk-mode="lottie"] {
        align-items: center;
        justify-content: center;
    }

    body.dark-theme #page-skeleton-loader {
        background: #12151c;
    }

    #page-skeleton-loader.pskl-hide {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .25s ease, visibility 0s linear .25s;
    }

    #page-skeleton-loader .pskl-bar,
    #page-skeleton-loader .pskl-circle,
    #page-skeleton-loader .pskl-card,
    #page-skeleton-loader .pskl-block {
        position: relative;
        overflow: hidden;
        background: #e3e7ef;
        border-radius: 8px;
    }

    body.dark-theme #page-skeleton-loader .pskl-bar,
    body.dark-theme #page-skeleton-loader .pskl-circle,
    body.dark-theme #page-skeleton-loader .pskl-card,
    body.dark-theme #page-skeleton-loader .pskl-block {
        background: #1f2430;
    }

    #page-skeleton-loader .pskl-bar::after,
    #page-skeleton-loader .pskl-circle::after,
    #page-skeleton-loader .pskl-card::after,
    #page-skeleton-loader .pskl-block::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
        animation: pskl-shimmer 1.4s ease-in-out infinite;
    }

    body.dark-theme #page-skeleton-loader .pskl-bar::after,
    body.dark-theme #page-skeleton-loader .pskl-circle::after,
    body.dark-theme #page-skeleton-loader .pskl-card::after,
    body.dark-theme #page-skeleton-loader .pskl-block::after {
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .08), transparent);
    }

    @keyframes pskl-shimmer {
        100% { transform: translateX(100%); }
    }

    #page-skeleton-loader .pskl-topbar {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 16px;
        height: 64px;
        padding: 0 24px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    body.dark-theme #page-skeleton-loader .pskl-topbar {
        border-bottom-color: rgba(255, 255, 255, .06);
    }

    #page-skeleton-loader .pskl-bar--brand { width: 120px; height: 22px; }
    #page-skeleton-loader .pskl-spacer { flex: 1; }
    #page-skeleton-loader .pskl-bar--pill { width: 90px; height: 32px; border-radius: 999px; }
    #page-skeleton-loader .pskl-circle { width: 36px; height: 36px; border-radius: 50%; flex: 0 0 auto; }

    #page-skeleton-loader .pskl-body {
        flex: 1 1 auto;
        display: flex;
        min-height: 0;
    }

    #page-skeleton-loader .pskl-sidebar {
        flex: 0 0 auto;
        width: 250px;
        padding: 24px 16px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        border-right: 1px solid rgba(0, 0, 0, .06);
    }

    body.dark-theme #page-skeleton-loader .pskl-sidebar {
        border-right-color: rgba(255, 255, 255, .06);
    }

    #page-skeleton-loader .pskl-bar--nav { height: 16px; border-radius: 6px; }
    #page-skeleton-loader .pskl-bar--nav:nth-child(3n+1) { width: 85%; }
    #page-skeleton-loader .pskl-bar--nav:nth-child(3n+2) { width: 65%; }
    #page-skeleton-loader .pskl-bar--nav:nth-child(3n+3) { width: 75%; }

    #page-skeleton-loader .pskl-content {
        position: relative; /* anchors the absolutely-centered #page-lottie-loader below */
        flex: 1 1 auto;
        min-width: 0;
        padding: 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow: hidden;
    }

    #page-skeleton-loader .pskl-row {
        display: flex;
        gap: 20px;
    }

    #page-skeleton-loader .pskl-card {
        flex: 1;
        height: 96px;
    }

    #page-skeleton-loader .pskl-block { height: 64px; }
    #page-skeleton-loader .pskl-block--tall { height: 220px; }

    @media (max-width: 782px) {
        #page-skeleton-loader .pskl-sidebar { display: none; }
        #page-skeleton-loader .pskl-row { flex-direction: column; }
    }

    /* The Lottie animation itself, floating above the skeleton — centered
       within the content area (mode="full") or centered in the whole
       viewport with nothing else around it (mode="lottie", auth pages).
       No card/backdrop: the animation's own canvas is transparent, and it
       stays that way here — no background/box-shadow to fake one. Hidden/
       faded via .pskl-lottie-visible rather than removed, so it's ready
       the instant it's needed. */
    #page-lottie-loader {
        width: 130px;
        height: 130px;
        background: transparent;
        opacity: 0;
        transition: opacity .3s ease;
        pointer-events: none;
    }

    #page-lottie-loader.pskl-lottie-visible {
        opacity: 1;
    }

    #page-skeleton-loader[data-psk-mode="full"] #page-lottie-loader {
        position: absolute;
        inset: 0;
        margin: auto;
    }
</style>

<script>
    (function () {
        var el = document.getElementById('page-skeleton-loader');
        if (!el) return;

        var mode = el.getAttribute('data-psk-mode') || 'full';
        var lottieHolder = document.getElementById('page-lottie-loader');
        var anim = null;
        var animReady = false;
        var lottieDelayTimer = null;

        function loadLottieScript(onReady) {
            if (window.lottie) { onReady(); return; }
            var s = document.createElement('script');
            s.src = "{{ asset('global/js/lottie-light.min.js') }}";
            s.onload = onReady;
            document.head.appendChild(s);
        }

        function ensureAnim(onReady) {
            if (animReady) { onReady(); return; }
            if (!lottieHolder) return;
            loadLottieScript(function () {
                if (!window.lottie) return;
                anim = window.lottie.loadAnimation({
                    container: lottieHolder,
                    renderer: 'svg',
                    loop: true,
                    autoplay: false,
                    // Real cacheable static asset (not inlined) — this
                    // partial renders on every single page, so a shared
                    // browser-cached file beats shipping the same ~17KB of
                    // animation data in every page's HTML.
                    path: "{{ asset('global/lottie/loading.json') }}"
                });
                animReady = true;
                onReady();
            });
        }

        function playLottie() {
            if (!lottieHolder) return;
            lottieHolder.classList.add('pskl-lottie-visible');
            ensureAnim(function () {
                anim.goToAndPlay(0, true);
            });
        }

        function stopLottie() {
            if (lottieDelayTimer) {
                clearTimeout(lottieDelayTimer);
                lottieDelayTimer = null;
            }
            if (lottieHolder) lottieHolder.classList.remove('pskl-lottie-visible');
            if (anim) anim.stop();
        }

        // 'lottie' mode (auth pages): no skeleton to fill the gap with, so
        // the animation plays immediately. 'full' mode (admin/dashboard):
        // the skeleton alone covers the first 2s; only bring the Lottie in
        // on top of it if the page is genuinely still loading past that.
        function armLottie() {
            if (mode === 'lottie') {
                playLottie();
                return;
            }
            if (lottieDelayTimer) clearTimeout(lottieDelayTimer);
            lottieDelayTimer = setTimeout(playLottie, 2000);
        }

        var hide = function () {
            el.classList.add('pskl-hide');
            stopLottie();
        };
        var show = function () {
            el.classList.remove('pskl-hide');
            armLottie();
        };

        // Covers this page's own load too, not just the next navigation.
        armLottie();

        if (document.readyState === 'complete') {
            hide();
        } else {
            window.addEventListener('load', hide);
        }

        // Backstop: never let this get stuck on screen indefinitely if a
        // resource hangs or 'load' never fires.
        setTimeout(hide, 10000);

        // Restore correctly if the page is served from the back/forward
        // cache (bfcache) — 'load' won't fire again in that case.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) hide();
        });

        // Re-show immediately on click of a real internal navigation link,
        // so it covers the wait for the *next* page too, not just this one.
        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            var link = event.target.closest('a[href]');
            if (!link) return;
            if (link.hasAttribute('data-no-skeleton') || link.hasAttribute('download')) return;
            if (link.target && link.target !== '_self') return;

            var href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
                href.startsWith('mailto:') || href.startsWith('tel:')) return;

            // Skip same-page anchor/hash-only links and links pointing at a
            // different origin (nothing to skeleton over on the way out).
            if (link.origin !== window.location.origin) return;
            if (link.pathname === window.location.pathname && link.hash) return;

            show();
        }, true);

        // Auth pages (mode="lottie") are simple full-page-POST forms —
        // login, register, forgot-password, OTP — not ajax widgets, so
        // it's safe to cover every submit here (unlike the dashboard,
        // which has enough unaudited ajax forms that this stays link-only
        // there).
        if (mode === 'lottie') {
            document.addEventListener('submit', function (event) {
                if (event.defaultPrevented) return;
                if (event.target.hasAttribute('data-no-skeleton')) return;
                show();
            }, true);
        }
    })();
</script>
