@php
    // Marketing CTAs resolve to real app routes:
    //  - "try / demo" CTAs let guests into the no-account sandbox
    //  - "sign up / pricing" CTAs send guests to register
    //  - authenticated users go straight into their schema list
    $tryUrl    = auth()->check() ? route('schemas.index') : route('schemas.demo');
    $signupUrl = auth()->check() ? route('schemas.index') : route('register');
    $signInUrl = route('login');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Schematic — The visual database schema builder for Laravel</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500;540;560;600;620;640;680;700;720&family=Geist+Mono:wght@400;450;500;640&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/landing.css') }}" />
</head>
<body>

<!-- ============ NAV ============ -->
<header class="nav" id="nav">
  <div class="nav-inner">
    <a class="brand" href="#top">
      <span class="brand-logo">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg>
      </span>
      Schematic
    </a>
    <nav class="nav-links">
      <a class="nav-link" href="#features">Features</a>
      <a class="nav-link" href="#how">How it works</a>
      <a class="nav-link" href="#pricing">Pricing</a>
      <a class="nav-link" href="{{ $tryUrl }}">Live demo</a>
    </nav>
    <div class="nav-spacer"></div>
    <div class="nav-cta">
      @auth
        <a class="btn btn-primary btn-sm" href="{{ route('schemas.index') }}">Your diagrams
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </a>
      @else
        <a class="btn btn-ghost btn-sm" href="{{ $signInUrl }}">Sign in</a>
        <a class="btn btn-primary btn-sm" href="{{ $tryUrl }}">Open app
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </a>
      @endauth
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
  </div>
</header>
<div class="mobile-menu" id="mobileMenu">
  <a href="#features">Features</a>
  <a href="#how">How it works</a>
  <a href="#pricing">Pricing</a>
  <a href="{{ $tryUrl }}">Live demo</a>
  @auth
    <a class="btn btn-primary" href="{{ route('schemas.index') }}">Your diagrams</a>
  @else
    <a class="btn btn-primary" href="{{ $tryUrl }}">Open app</a>
  @endauth
</div>

<span id="top"></span>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-grid-bg"></div>
  <div class="hero-inner">
    <span class="eyebrow"><span class="pill">NEW</span> Schema diffing &amp; migration preview</span>
    <h1 class="hero-title">Design your database<br>visually. Ship <span class="grad">Laravel migrations</span> in minutes.</h1>
    <p class="hero-sub">Schematic is the visual schema builder for Laravel teams. Drag out tables, draw relationships, and export clean migrations — no more second-guessing your foreign keys.</p>
    <div class="hero-cta">
      <a class="btn btn-primary btn-lg" href="{{ $tryUrl }}">Start building — no sign-up</a>
      <a class="btn btn-ghost btn-lg" href="{{ $tryUrl }}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m10 9 5 3-5 3z" fill="currentColor" stroke="none"/></svg>
        Watch demo
      </a>
    </div>
    <div class="hero-note">
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> No credit card</span>
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Free for solo devs</span>
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Export anytime</span>
    </div>
  </div>

  <!-- hero product shot -->
  <div class="hero-shot reveal">
    <div class="browser">
      <div class="browser-bar">
        <span class="tl" style="background:#fb7185"></span>
        <span class="tl" style="background:#fbbf24"></span>
        <span class="tl" style="background:#34d399"></span>
        <span class="browser-url">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
          app.schematic.dev/blog-platform
        </span>
      </div>
      <div class="browser-body">
        <div class="dots"></div>
        <svg class="diagram-svg" id="heroSvg"></svg>
        <div class="diagram" id="heroDiagram">
          <!-- users -->
          <div class="dcard" style="left:40px; top:64px;" data-card="users">
            <div class="dcard-h" style="background:var(--c-blue-t); color:var(--c-blue-d);">users</div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--c-blue-d)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">name</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">email</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">created_at</span><span class="ty">timestamp</span></div>
          </div>
          <!-- posts -->
          <div class="dcard" style="left:430px; top:40px;" data-card="posts">
            <div class="dcard-h" style="background:var(--c-green-t); color:var(--c-green-d);">posts</div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--c-green-d)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--faint)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">user_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">title</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">body</span><span class="ty">text</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">published_at</span><span class="ty">datetime?</span></div>
          </div>
          <!-- comments -->
          <div class="dcard" style="left:430px; top:268px;" data-card="comments">
            <div class="dcard-h" style="background:var(--c-purple-t); color:var(--c-purple-d);">comments</div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--c-purple-d)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--faint)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">post_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">body</span><span class="ty">text</span></div>
          </div>
        </div>
        <div class="float-badge" style="right:26px; top:24px; color:var(--c-green-d);">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
          3 relationships detected
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ LOGOS ============ -->
<section class="logos wrap">
  <div class="logos-label">Trusted by teams shipping with Laravel</div>
  <div class="logos-row">
    <span class="logo-word">◆ Forge</span>
    <span class="logo-word">⬡ Octane</span>
    <span class="logo-word">✦ Nova</span>
    <span class="logo-word">▲ Vapor</span>
    <span class="logo-word">● Pulse</span>
    <span class="logo-word">◐ Envoyer</span>
  </div>
</section>

<!-- ============ FEATURES / BENTO ============ -->
<section class="section" id="features">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">FEATURES</span>
      <h2>Everything your schema needs, nothing it doesn't</h2>
      <p>From the first table to the final migration, Schematic keeps your data model visual, versioned, and ready to ship.</p>
    </div>

    <div class="bento">
      <div class="cell span-3 tall reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg></div>
        <h3>An infinite visual canvas</h3>
        <p>Drag tables anywhere, pan and zoom freely, and let auto-arrange untangle the mess. Your whole data model at a glance.</p>
        <div class="cell-visual">
          <svg viewBox="0 0 420 150" width="100%" style="border:1px solid var(--border); border-radius:10px; background:var(--bg-3);">
            <defs><pattern id="bd" width="14" height="14" patternUnits="userSpaceOnUse"><circle cx="1.2" cy="1.2" r="1" fill="#d8d8df"/></pattern></defs>
            <rect width="420" height="150" fill="url(#bd)"/>
            <g>
              <rect x="28" y="34" width="92" height="74" rx="7" fill="#fff" stroke="#e2e2e6"/>
              <path d="M28 41 a7 7 0 0 1 7 -7 h78 a7 7 0 0 1 7 7 v9 h-92 z" fill="var(--c-blue-t)"/>
              <rect x="40" y="58" width="40" height="5" rx="2.5" fill="#cfcfd6"/><rect x="40" y="71" width="60" height="5" rx="2.5" fill="#e2e2e8"/><rect x="40" y="84" width="50" height="5" rx="2.5" fill="#e2e2e8"/>
              <rect x="220" y="20" width="92" height="74" rx="7" fill="#fff" stroke="#e2e2e6"/>
              <path d="M220 27 a7 7 0 0 1 7 -7 h78 a7 7 0 0 1 7 7 v9 h-92 z" fill="var(--c-green-t)"/>
              <rect x="232" y="44" width="40" height="5" rx="2.5" fill="#cfcfd6"/><rect x="232" y="57" width="60" height="5" rx="2.5" fill="#e2e2e8"/><rect x="232" y="70" width="46" height="5" rx="2.5" fill="#e2e2e8"/>
              <rect x="296" y="92" width="92" height="58" rx="7" fill="#fff" stroke="#e2e2e6"/>
              <path d="M296 99 a7 7 0 0 1 7 -7 h78 a7 7 0 0 1 7 7 v9 h-92 z" fill="var(--c-purple-t)"/>
              <rect x="308" y="116" width="40" height="5" rx="2.5" fill="#cfcfd6"/><rect x="308" y="129" width="56" height="5" rx="2.5" fill="#e2e2e8"/>
              <path d="M120 71 C 170 71, 175 50, 220 50" fill="none" stroke="#3b82f6" stroke-width="1.8"/>
              <path d="M312 60 C 330 60, 320 116, 296 116" fill="none" stroke="#10b981" stroke-width="1.8"/>
            </g>
          </svg>
        </div>
      </div>

      <div class="cell span-3 tall reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 16 4-4-4-4M6 8l-4 4 4 4M14.5 4l-5 16"/></svg></div>
        <h3>Laravel-native types, built in</h3>
        <p>Every column maps to a real migration method — <code style="font-family:var(--mono);font-size:13px;color:var(--accent)">$table-&gt;foreignId()</code>, <code style="font-family:var(--mono);font-size:13px;color:var(--accent)">string()</code>, <code style="font-family:var(--mono);font-size:13px;color:var(--accent)">json()</code> and more.</p>
        <div class="type-chips" id="typeChips">
          <span class="type-chip">id</span>
          <span class="type-chip on">foreignId</span>
          <span class="type-chip">string</span>
          <span class="type-chip">text</span>
          <span class="type-chip">boolean</span>
          <span class="type-chip">timestamp</span>
          <span class="type-chip">json</span>
          <span class="type-chip">decimal</span>
          <span class="type-chip">uuid</span>
          <span class="type-chip">enum</span>
          <span class="type-chip">bigInteger</span>
        </div>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg></div>
        <h3>One-click export</h3>
        <p>Generate timestamped migration files or raw SQL for MySQL, Postgres, and SQLite.</p>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5 8.6 10.5"/></svg></div>
        <h3>Share &amp; collaborate</h3>
        <p>Send a link, leave comments, and design your schema together in real time.</p>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></div>
        <h3>Smart relationships</h3>
        <p>Foreign keys render as crow's-foot connectors that reroute as you drag. One-to-many, never guessed.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ SHOWCASE: diagram -> migration ============ -->
<section class="section showcase">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">DIAGRAM → CODE</span>
      <h2>Your diagram is the source of truth</h2>
      <p>Draw the relationship once. Schematic writes the migration — foreign keys, indexes, and constraints included.</p>
    </div>
    <div class="showcase-grid">
      <div class="showcase-diagram reveal">
        <div class="dots"></div>
        <svg class="diagram-svg" id="scSvg"></svg>
        <div class="diagram" id="scDiagram">
          <div class="dcard" style="left:34px; top:54px; width:184px;" data-card="users">
            <div class="dcard-h" style="background:var(--c-blue-t); color:var(--c-blue-d);">users</div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--c-blue-d)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">name</span><span class="ty">varchar</span></div>
          </div>
          <div class="dcard" style="left:266px; top:188px; width:184px;" data-card="posts">
            <div class="dcard-h" style="background:var(--c-green-t); color:var(--c-green-d);">posts</div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--c-green-d)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic" style="color:var(--faint)"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">user_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">title</span><span class="ty">varchar</span></div>
          </div>
        </div>
      </div>

      <div class="code-card reveal">
        <div class="code-bar">
          <span class="tl" style="background:#fb7185"></span>
          <span class="tl" style="background:#fbbf24"></span>
          <span class="tl" style="background:#34d399"></span>
          <span class="fn" id="codeFn">2026_06_13_create_posts_table.php</span>
          <div class="code-tabs">
            <button class="code-tab on" data-tab="laravel">Migration</button>
            <button class="code-tab" data-tab="sql">SQL</button>
          </div>
        </div>
        <div class="code-body" id="codeBody"></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="section" id="how">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">HOW IT WORKS</span>
      <h2>From blank canvas to migration in three steps</h2>
    </div>
    <div class="steps">
      <div class="step reveal">
        <div class="step-num">1</div>
        <h3>Draw your tables</h3>
        <p>Add tables and columns visually. Pick Laravel types from a dropdown — no syntax to memorize.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">2</div>
        <h3>Connect the dots</h3>
        <p>Point a foreign key at another table and Schematic draws the relationship with crow's-foot notation.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">3</div>
        <h3>Export &amp; ship</h3>
        <p>Download ready-to-run migration files or copy the SQL. Drop them into your repo and migrate.</p>
      </div>
    </div>

    <div class="stats reveal" style="margin-top:72px;">
      <div class="stat"><div class="stat-n">12k+</div><div class="stat-l">Schemas designed</div></div>
      <div class="stat"><div class="stat-n">40+</div><div class="stat-l">Column types</div></div>
      <div class="stat"><div class="stat-n">3</div><div class="stat-l">SQL dialects</div></div>
      <div class="stat"><div class="stat-n">&lt;5 min</div><div class="stat-l">To first migration</div></div>
    </div>
  </div>
</section>

<!-- ============ QUOTE ============ -->
<section class="section showcase">
  <div class="wrap">
    <div class="quote-card reveal">
      <svg width="34" height="34" viewBox="0 0 24 24" fill="var(--accent)" style="margin:0 auto 22px; opacity:.85;"><path d="M9.5 6C6.5 7.5 5 10 5 13v5h6v-6H8c0-2 .8-3.3 2.5-4L9.5 6Zm9 0C15.5 7.5 14 10 14 13v5h6v-6h-3c0-2 .8-3.3 2.5-4L18.5 6Z"/></svg>
      <p class="quote">"We replaced a wall of half-correct migration files with one <span class="hl">Schematic diagram</span>. New engineers understand our database in minutes, not days."</p>
      <div class="quote-by">
        <div class="q-avatar">RT</div>
        <div class="q-meta">
          <div class="q-name">Rina Takahashi</div>
          <div class="q-role">Staff Engineer · Lumenpay</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
<section class="section" id="pricing">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">PRICING</span>
      <h2>Start free. Upgrade when your team grows.</h2>
      <p>Every plan includes unlimited tables, visual editing, and SQL export.</p>
    </div>
    <div class="price-grid">
      <div class="plan reveal">
        <div class="plan-name">Solo</div>
        <div class="plan-price">$0<span> /mo</span></div>
        <div class="plan-desc">For individual developers and side projects.</div>
        <ul class="plan-feats">
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>3 projects</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Unlimited tables</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Migration &amp; SQL export</li>
        </ul>
        <a class="btn btn-ghost" style="width:100%" href="{{ $signupUrl }}">Get started</a>
      </div>
      <div class="plan featured reveal">
        <div class="plan-badge">MOST POPULAR</div>
        <div class="plan-name">Team</div>
        <div class="plan-price">$12<span> /user/mo</span></div>
        <div class="plan-desc">For teams designing schemas together.</div>
        <ul class="plan-feats">
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Unlimited projects</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Real-time collaboration</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Schema diffing &amp; history</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Comments &amp; share links</li>
        </ul>
        <a class="btn btn-primary" style="width:100%" href="{{ $signupUrl }}">Start free trial</a>
      </div>
      <div class="plan reveal">
        <div class="plan-name">Enterprise</div>
        <div class="plan-price">Custom</div>
        <div class="plan-desc">For organizations with advanced needs.</div>
        <ul class="plan-feats">
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>SSO &amp; SCIM</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Self-hosted option</li>
          <li><span class="ck"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></span>Priority support &amp; SLA</li>
        </ul>
        <a class="btn btn-ghost" style="width:100%" href="#">Contact sales</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA BAND ============ -->
<section class="cta-band">
  <div class="wrap">
    <div class="cta-box reveal">
      <div class="cta-dots"></div>
      <h2>Design your next schema in the open</h2>
      <p>Join thousands of Laravel developers building their database visually.</p>
      <div class="hero-cta">
        <a class="btn btn-white btn-lg" href="{{ $tryUrl }}">Open the builder</a>
        <a class="btn btn-ondark btn-lg" href="#features">See features</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <a class="brand" href="#top">
          <span class="brand-logo"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg></span>
          Schematic
        </a>
        <p class="footer-brand-desc">The visual database schema builder built for Laravel developers.</p>
      </div>
      <div class="footer-col"><h4>Product</h4><a href="#features">Features</a><a href="#pricing">Pricing</a><a href="{{ $tryUrl }}">Live demo</a><a href="#">Changelog</a></div>
      <div class="footer-col"><h4>Resources</h4><a href="#">Documentation</a><a href="#">Laravel guide</a><a href="#">Templates</a><a href="#">API</a></div>
      <div class="footer-col"><h4>Company</h4><a href="#">About</a><a href="#">Blog</a><a href="#">Careers</a><a href="#">Contact</a></div>
      <div class="footer-col"><h4>Legal</h4><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Security</a></div>
    </div>
    <div class="footer-bot">
      <span class="copy">© 2026 Schematic Labs. Not affiliated with Laravel.</span>
      <div class="footer-social">
        <a href="#" aria-label="GitHub"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.3 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.3 9.3 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.3 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5a10.3 10.3 0 0 0 6.8-9.7C22 6.6 17.5 2 12 2Z"/></svg></a>
        <a href="#" aria-label="X"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.2 2h3.3l-7.2 8.2L22.8 22h-6.6l-5.2-6.8L4.9 22H1.6l7.7-8.8L1.2 2h6.8l4.7 6.2L18.2 2Zm-1.2 18h1.8L7.1 3.9H5.2L17 20Z"/></svg></a>
        <a href="#" aria-label="Discord"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M19.3 5.3A17 17 0 0 0 15.1 4l-.2.4a13 13 0 0 1 3.7 1.9 12 12 0 0 0-10.3 0A13 13 0 0 1 12 4.4L11.8 4a17 17 0 0 0-4.2 1.3C4.9 9.3 4.2 13.2 4.5 17a17 17 0 0 0 5.2 2.6l.4-.6a11 11 0 0 1-1.8-.9l.4-.3a12 12 0 0 0 10.4 0l.4.3a11 11 0 0 1-1.8.9l.4.6a17 17 0 0 0 5.2-2.6c.4-4.4-.7-8.3-3-11.7ZM9.7 14.7c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Zm4.6 0c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Z"/></svg></a>
      </div>
    </div>
  </div>
</footer>

<script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>
