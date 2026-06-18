@php
    $tryUrl = auth()->check() ? route('schemas.index') : route('schemas.demo');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title') — Schematic</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500;540;560;600;620;640;680;700;720&family=Geist+Mono:wght@400;450;500;640&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}" />

<style>
  .legal-hero { padding: 64px 0 28px; border-bottom: 1px solid var(--border); background: var(--bg-2); }
  .legal-hero .eyebrow { font-family: var(--mono); font-size: 13px; color: var(--accent); letter-spacing: .04em; text-transform: uppercase; }
  .legal-hero h1 { font-size: 40px; line-height: 1.1; margin: 10px 0 8px; color: var(--ink); letter-spacing: -.02em; }
  .legal-hero .updated { color: var(--muted); font-size: 14px; }
  .legal-body { padding: 44px 0 72px; }
  .legal-body .wrap { max-width: 760px; }
  .legal-body h2 { font-size: 22px; margin: 38px 0 12px; color: var(--ink); letter-spacing: -.01em; }
  .legal-body h2:first-child { margin-top: 0; }
  .legal-body h3 { font-size: 17px; margin: 24px 0 8px; color: var(--ink-2); }
  .legal-body p, .legal-body li { color: var(--ink-3); font-size: 15.5px; line-height: 1.7; }
  .legal-body p { margin: 0 0 14px; }
  .legal-body ul { margin: 0 0 16px; padding-left: 22px; }
  .legal-body li { margin: 0 0 7px; }
  .legal-body a { color: var(--accent-2); text-decoration: underline; text-underline-offset: 2px; }
  .legal-back { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); font-size: 14px; text-decoration: none; margin-bottom: 18px; }
  .legal-back:hover { color: var(--ink-2); }
</style>
</head>
<body>

<!-- ============ NAV ============ -->
<header class="nav" id="nav">
  <div class="nav-inner">
    <a class="brand" href="{{ route('home') }}">
      <span class="brand-logo">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg>
      </span>
      Schematic
    </a>
    <div class="nav-spacer"></div>
    <div class="nav-cta">
      <a class="btn btn-ghost btn-sm" href="{{ route('home') }}">Home</a>
      <a class="btn btn-primary btn-sm" href="{{ $tryUrl }}">Open app
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
      </a>
    </div>
  </div>
</header>

<!-- ============ HERO ============ -->
<section class="legal-hero">
  <div class="wrap">
    <a class="legal-back" href="{{ route('home') }}">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      Back to home
    </a>
    <div class="eyebrow">Legal</div>
    <h1>@yield('title')</h1>
    <p class="updated">Last updated @yield('updated')</p>
  </div>
</section>

<!-- ============ BODY ============ -->
<section class="legal-body">
  <div class="wrap">
    @yield('content')
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <a class="brand" href="{{ route('home') }}">
          <span class="brand-logo"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg></span>
          Schematic
        </a>
        <p class="footer-brand-desc">The visual database schema builder for modern dev teams.</p>
      </div>
      <div class="footer-col"><h4>Product</h4><a href="{{ route('home') }}#features">Features</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ $tryUrl }}">Live demo</a></div>
      <div class="footer-col"><h4>Company</h4><a href="{{ route('home') }}">About</a><a href="{{ route('home') }}">Contact</a></div>
      <div class="footer-col"><h4>Legal</h4><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a></div>
    </div>
    <div class="footer-bot">
      <span class="copy">© 2026 Schematic Labs. All rights reserved.</span>
    </div>
  </div>
</footer>

</body>
</html>
