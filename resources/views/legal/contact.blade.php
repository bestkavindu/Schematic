@php
    $tryUrl = auth()->check() ? route('schemas.index') : route('schemas.demo');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Contact · Schematic</title>

<meta name="description" content="Contact Schematic Labs — questions, a demo for your team, or help migrating a tricky schema. A real engineer will get back to you." />
<meta name="author" content="Schematic Labs" />
<meta name="robots" content="index, follow" />
<link rel="canonical" href="{{ route('contact') }}" />
<meta name="theme-color" content="#5b5bd6" />

<meta property="og:type" content="website" />
<meta property="og:site_name" content="Schematic" />
<meta property="og:title" content="Contact — Schematic" />
<meta property="og:description" content="Questions, a demo, or help with your data model — drop us a line." />
<meta property="og:url" content="{{ route('contact') }}" />
<meta property="og:image" content="{{ url('og.png') }}" />
<meta name="twitter:card" content="summary_large_image" />

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;460;500;520;540;560;600;620;640;680;700;720&family=Geist+Mono:wght@400;450;500;640&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}" />
<link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ filemtime(public_path('css/contact.css')) }}" />
@livewireStyles
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
    <nav class="nav-links">
      <a class="nav-link" href="{{ route('home') }}#features">Features</a>
      <a class="nav-link" href="{{ route('home') }}#how">How it works</a>
      <a class="nav-link" href="{{ route('home') }}#pricing">Pricing</a>
      <a class="nav-link is-active" href="{{ route('contact') }}" aria-current="true">Contact</a>
      <a class="nav-link" href="{{ route('schemas.demo') }}">Live demo</a>
    </nav>
    <div class="nav-spacer"></div>
    <div class="nav-cta">
      <a class="btn btn-ghost btn-sm" href="{{ route('home') }}">Home</a>
      <a class="btn btn-primary btn-sm" href="{{ $tryUrl }}">Open app
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
      </a>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
  </div>
</header>
<div class="mobile-menu" id="mobileMenu">
  <a href="{{ route('home') }}#features">Features</a>
  <a href="{{ route('home') }}#how">How it works</a>
  <a href="{{ route('home') }}#pricing">Pricing</a>
  <a href="{{ route('contact') }}" aria-current="true">Contact</a>
  <a href="{{ route('schemas.demo') }}">Live demo</a>
  <a class="btn btn-primary" href="{{ $tryUrl }}">Open app</a>
</div>

<!-- ============ HEADER ============ -->
<section class="contact-hero">
  <div class="hero-grid-bg"></div>
  <div class="inner">
    <span class="eyebrow"><span class="pill">CONTACT</span> We usually reply within a few hours</span>
    <h1>Let's talk about your data model</h1>
    <p>Questions about Schematic, a demo for your team, or help migrating a tricky schema — drop us a line and a real engineer will get back to you.</p>
  </div>
</section>

<!-- ============ MAIN ============ -->
<section class="contact-main">
  <div class="wrap">
    <div class="contact-grid">

      <!-- FORM -->
      @livewire('contact-form')

      <!-- ASIDE -->
      <div class="contact-aside">
        <div class="method">
          <div class="method-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 0-7 7v3l-2 4h18l-2-4V9a7 7 0 0 0-7-7Z"/><path d="M9 18a3 3 0 0 0 6 0"/></svg></div>
          <div>
            <h3>Support</h3>
            <p>Stuck on a migration or hit a bug? We'll help.</p>
            <a href="mailto:support@schematic.dev">support@schematic.dev <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
          </div>
        </div>
        <div class="info-card">
          <div class="info-row">
            <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <span><span class="k">HQ</span> &nbsp;<b>Colombo, Sri Lanka</b> · remote-first team</span>
          </div>
          <div class="info-row">
            <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <span><span class="k">Hours</span> &nbsp;<b>Mon–Fri, 9–6</b> (GMT+5:30)</span>
          </div>
          <div class="info-row">
            <span class="ic"><span class="status-dot"></span></span>
            <span><b>Support is online</b> — typical reply in under 4 hours</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="faq-sec">
  <div class="wrap">
    <div class="section-head">
      <span class="tag">BEFORE YOU ASK</span>
      <h2>Frequently asked</h2>
      <p>Quick answers to the things people email us most.</p>
    </div>
    <div class="faq-list" id="faqList">
      <div class="faq-item">
        <button class="faq-q">Do I need to install anything to try Schematic?<span class="chev"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></span></button>
        <div class="faq-a"><div class="faq-a-inner">No. Schematic runs entirely in your browser — open the builder and start dragging out tables. Export migration files only when you're ready to drop them into your repo.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Which databases do you export for?<span class="chev"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></span></button>
        <div class="faq-a"><div class="faq-a-inner">We generate native Laravel migrations plus raw SQL for MySQL, PostgreSQL, and SQLite. Foreign keys, indexes, and constraints are included automatically.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Can my whole team collaborate on one schema?<span class="chev"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></span></button>
        <div class="faq-a"><div class="faq-a-inner">Yes — on the Team plan you get real-time collaboration, share links, comments, and schema history so everyone designs from the same source of truth.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q">Do you offer a self-hosted or enterprise option?<span class="chev"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></span></button>
        <div class="faq-a"><div class="faq-a-inner">We do. Enterprise includes SSO/SCIM, a self-hosted deployment option, and a dedicated support SLA. Pick "Partnership" or "Sales" above and we'll set up a call.</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA BAND ============ -->
<section class="cta-band">
  <div class="wrap">
    <div class="cta-box">
      <div class="cta-dots"></div>
      <h2>Rather just start building?</h2>
      <p>You don't need to talk to us first. Open the builder and design your schema right now.</p>
      <div class="hero-cta">
        <a class="btn btn-white btn-lg" href="{{ $tryUrl }}">Open the builder</a>
        <a class="btn btn-ondark btn-lg" href="{{ route('home') }}#features">See features</a>
      </div>
    </div>
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
        <p class="footer-brand-desc">The visual database schema builder built for Laravel developers.</p>
      </div>
      <div class="footer-col"><h4>Product</h4><a href="{{ route('home') }}#features">Features</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ route('schemas.demo') }}">Live demo</a></div>
      <div class="footer-col"><h4>Company</h4><a href="{{ route('home') }}#about">About</a><a href="{{ route('contact') }}">Contact</a></div>
      <div class="footer-col"><h4>Legal</h4><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a></div>
    </div>
    <div class="footer-bot">
      <span class="copy">© 2026 Schematic Labs. All rights reserved.</span>
      <div class="footer-social">
        <a href="#" aria-label="GitHub"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.3 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.3 9.3 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.3 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5a10.3 10.3 0 0 0 6.8-9.7C22 6.6 17.5 2 12 2Z"/></svg></a>
        <a href="#" aria-label="X"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.2 2h3.3l-7.2 8.2L22.8 22h-6.6l-5.2-6.8L4.9 22H1.6l7.7-8.8L1.2 2h6.8l4.7 6.2L18.2 2Zm-1.2 18h1.8L7.1 3.9H5.2L17 20Z"/></svg></a>
        <a href="#" aria-label="Discord"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M19.3 5.3A17 17 0 0 0 15.1 4l-.2.4a13 13 0 0 1 3.7 1.9 12 12 0 0 0-10.3 0A13 13 0 0 1 12 4.4L11.8 4a17 17 0 0 0-4.2 1.3C4.9 9.3 4.2 13.2 4.5 17a17 17 0 0 0 5.2 2.6l.4-.6a11 11 0 0 1-1.8-.9l.4-.3a12 12 0 0 0 10.4 0l.4.3a11 11 0 0 1-1.8.9l.4.6a17 17 0 0 0 5.2-2.6c.4-4.4-.7-8.3-3-11.7ZM9.7 14.7c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Zm4.6 0c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Z"/></svg></a>
      </div>
    </div>
  </div>
</footer>

@livewireScripts
<script src="{{ asset('js/contact.js') }}?v={{ filemtime(public_path('js/contact.js')) }}"></script>
</body>
</html>
