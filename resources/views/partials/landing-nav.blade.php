{{--
  Shared landing navbar (header + mobile menu) for the marketing pages.
  Params:
    $navBase   — prefix for in-page section anchors. '' on the home page so links
                 stay same-page (#features) and the scroll-spy pill works; on other
                 pages pass route('home') so anchors jump back to home + scroll.
    $navActive — section/page key to render a static active pill (e.g. 'contact').
                 On home the pill is driven by JS scroll-spy instead, so leave null.
--}}
@php
  $navBase   = $navBase   ?? '';
  $navActive = $navActive ?? null;
  $tryUrl    = auth()->check() ? route('schemas.index') : route('schemas.demo');
  $signInUrl = route('login');
  $brandHref = $navBase !== '' ? $navBase : '#top';

  if (auth()->check()) {
      $navUser = auth()->user();
      $navInitials = collect(preg_split('/\s+/', trim((string) $navUser->name)))
          ->filter()
          ->take(2)
          ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
          ->implode('');
      $navInitials = $navInitials !== '' ? $navInitials : mb_strtoupper(mb_substr((string) $navUser->email, 0, 1));
  }
@endphp

<!-- ============ NAV ============ -->
<header class="nav" id="nav">
  <div class="nav-inner">
    <a class="brand" href="{{ $brandHref }}" aria-label="Schematic — home">
      <span class="brand-logo">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg>
      </span>
      Schematic
    </a>

    <nav class="nav-links" id="navLinks" aria-label="Primary">
      <span class="nav-pill" id="navPill" aria-hidden="true"></span>
      <a class="nav-link" href="{{ $navBase }}#features" data-spy="features">Features</a>
      <a class="nav-link" href="{{ $navBase }}#how" data-spy="how">How it works</a>
      <a class="nav-link" href="{{ $navBase }}#about" data-spy="about">About</a>
      <a class="nav-link{{ $navActive === 'contact' ? ' is-active' : '' }}" href="{{ route('contact') }}" @if($navActive === 'contact') aria-current="true" @endif>Contact</a>
      <a class="nav-link nav-link--demo" href="{{ $tryUrl }}">Live demo</a>
    </nav>

    <div class="nav-spacer"></div>

    <div class="nav-cta">
      @auth
        <a class="btn btn-ghost btn-sm nav-diagrams" href="{{ route('schemas.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.6"/><rect x="14" y="3" width="7" height="7" rx="1.6"/><rect x="3" y="14" width="7" height="7" rx="1.6"/><rect x="14" y="14" width="7" height="7" rx="1.6"/></svg>
          Your diagrams
        </a>
        <a class="nav-avatar" href="{{ route('profile.edit') }}" aria-label="{{ $navUser->name ?: $navUser->email }} — account">{{ $navInitials }}</a>
      @else
        <a class="btn btn-ghost btn-sm" href="{{ $signInUrl }}">Sign in</a>
        <a class="btn btn-primary btn-sm" href="{{ $tryUrl }}">Open app
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </a>
      @endauth
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">
      <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
    </button>
  </div>
</header>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <a href="{{ $navBase }}#features">Features</a>
  <a href="{{ $navBase }}#how">How it works</a>
  <a href="{{ $navBase }}#about">About</a>
  <a href="{{ route('contact') }}" @if($navActive === 'contact') aria-current="true" @endif>Contact</a>
  <a href="{{ $tryUrl }}">Live demo</a>
  @auth
    <a class="btn btn-primary" href="{{ route('schemas.index') }}">Your diagrams</a>
  @else
    <a class="btn btn-ghost" href="{{ $signInUrl }}">Sign in</a>
    <a class="btn btn-primary" href="{{ $tryUrl }}">Open app</a>
  @endauth
</div>
