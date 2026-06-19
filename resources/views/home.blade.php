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
<title>Schematic — The visual database schema builder</title>

{{-- ============ SEO / META ============ --}}
<meta name="description" content="Design your database schema visually. Schematic is a free online ERD tool — drag tables, export to MySQL, PostgreSQL, Laravel, Prisma, DBML or Supabase." />
<meta name="author" content="Schematic Labs" />
<meta name="robots" content="index, follow" />
<link rel="canonical" href="{{ url('/') }}" />

<meta name="theme-color" content="#5b5bd6" />
<meta name="color-scheme" content="light" />
<meta name="format-detection" content="telephone=no" />
<meta name="referrer" content="strict-origin-when-cross-origin" />

{{-- ---------- Open Graph ---------- --}}
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Schematic" />
<meta property="og:title" content="Schematic — Design your database visually" />
<meta property="og:description" content="A free online ERD tool and visual database schema builder. Drag tables, draw relationships, then export to MySQL, PostgreSQL, Laravel, Prisma, DBML or push to Supabase." />
<meta property="og:url" content="{{ url('/') }}" />
<meta property="og:image" content="{{ url('og.png') }}" />
<meta property="og:image:type" content="image/png" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="Schematic — visual database schema builder. Tables and relationships on a light ERD canvas with an indigo accent." />
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}" />

{{-- ---------- Twitter / X ---------- --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="Schematic — Design your database visually" />
<meta name="twitter:description" content="A free online ERD tool and visual database schema builder. Export to MySQL, PostgreSQL, Laravel, Prisma, DBML or push to Supabase." />
<meta name="twitter:image" content="{{ url('og.png') }}" />
<meta name="twitter:image:alt" content="Schematic — visual database schema builder." />

{{-- ============ JSON-LD STRUCTURED DATA ============ --}}
@php
    $appUrl  = rtrim(config('app.url'), '/');
    $homeUrl = url('/');
    $logoUrl = $appUrl . '/logo.png';
    $imgUrl  = $appUrl . '/og.png';

    $ldOrganization = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => 'Schematic Labs',
        'url'         => $homeUrl,
        'logo'        => [
            '@type'  => 'ImageObject',
            'url'    => $logoUrl,
            'width'  => 512,
            'height' => 512,
        ],
        'description' => 'Schematic Labs builds Schematic — the visual database schema builder for developers.',
    ];

    $ldSoftwareApplication = [
        '@context'            => 'https://schema.org',
        '@type'               => 'WebApplication',
        'name'                => 'Schematic',
        'description'         => 'Schematic is a free online ERD tool and visual database schema builder. Drag out tables, draw entity-relationship diagrams on a canvas, then export to MySQL, PostgreSQL, Laravel migrations, Prisma, DBML or JSON, or push straight to PostgreSQL and Supabase.',
        'url'                 => $homeUrl,
        'applicationCategory' => 'DeveloperApplication',
        'operatingSystem'     => 'All',
        'browserRequirements' => 'Requires JavaScript. Runs in any modern browser.',
        'image'               => $imgUrl,
        'softwareHelp'        => $homeUrl,
        'offers'              => [
            '@type'         => 'Offer',
            'price'         => '0',
            'priceCurrency' => 'USD',
            'url'           => $homeUrl,
        ],
        'publisher'           => [
            '@type' => 'Organization',
            'name'  => 'Schematic Labs',
            'url'   => $homeUrl,
        ],
    ];

    $ldWebSite = [
        '@context'  => 'https://schema.org',
        '@type'     => 'WebSite',
        'name'      => 'Schematic',
        'url'       => $homeUrl,
        'publisher' => [
            '@type' => 'Organization',
            'name'  => 'Schematic Labs',
        ],
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp
<script type="application/ld+json">{!! json_encode($ldOrganization, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($ldSoftwareApplication, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($ldWebSite, $jsonFlags) !!}</script>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500;540;560;600;620;640;680;700;720&family=Geist+Mono:wght@400;450;500;640&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}" />
</head>
<body>

@include('partials.landing-nav', ['navBase' => '', 'navActive' => null])

<span id="top"></span>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-grid-bg"></div>
  <div class="hero-inner">
    <span class="eyebrow"><span class="pill">NEW</span> Push schemas straight to Postgres &amp; Supabase</span>
    <h1 class="hero-title">Design your database<br>visually. Ship to <span class="grad">SQL, Prisma &amp; Supabase</span> in minutes.</h1>
    <p class="hero-sub">Schematic is the visual schema builder for modern dev teams. Drag out tables, draw relationships, then export to MySQL, PostgreSQL, Laravel, Prisma, or DBML — or push straight to a Supabase database. No more second-guessing your foreign keys.</p>
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
        <span class="tl tl-r"></span>
        <span class="tl tl-y"></span>
        <span class="tl tl-g"></span>
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
            <div class="dcard-h t-blue">users</div>
            <div class="dcard-r"><span class="dot-ic t-blue"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">name</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">email</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">created_at</span><span class="ty">timestamp</span></div>
          </div>
          <!-- posts -->
          <div class="dcard" style="left:430px; top:40px;" data-card="posts">
            <div class="dcard-h t-green">posts</div>
            <div class="dcard-r"><span class="dot-ic t-green"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic fk"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">user_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">title</span><span class="ty">varchar</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">body</span><span class="ty">text</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">published_at</span><span class="ty">datetime?</span></div>
          </div>
          <!-- comments -->
          <div class="dcard" style="left:430px; top:268px;" data-card="comments">
            <div class="dcard-h t-purple">comments</div>
            <div class="dcard-r"><span class="dot-ic t-purple"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic fk"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">post_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">body</span><span class="ty">text</span></div>
          </div>
        </div>
        <div class="float-badge t-green" style="right:26px; top:24px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
          3 relationships detected
        </div>
      </div>
    </div>
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
        <h3>Framework-native types, built in</h3>
        <p>Every column maps to a real type — <code class="code-inline">foreignId</code>, <code class="code-inline">string</code>, <code class="code-inline">json</code> and more — exporting straight to SQL, Laravel, or Prisma.</p>
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
        <h3>Export anywhere — or push live</h3>
        <p>One click gives you MySQL or PostgreSQL DDL, Laravel migrations, Prisma, DBML, or JSON — or push your schema straight into a Postgres / Supabase database.</p>
        <div class="type-chips">
          <span class="type-chip">MySQL</span>
          <span class="type-chip">PostgreSQL</span>
          <span class="type-chip">Laravel</span>
          <span class="type-chip">Prisma</span>
          <span class="type-chip">DBML</span>
          <span class="type-chip">JSON</span>
          <span class="type-chip on">Push&nbsp;→&nbsp;Supabase</span>
        </div>
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
            <div class="dcard-h t-blue">users</div>
            <div class="dcard-r"><span class="dot-ic t-blue"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">name</span><span class="ty">varchar</span></div>
          </div>
          <div class="dcard" style="left:266px; top:188px; width:184px;" data-card="posts">
            <div class="dcard-h t-green">posts</div>
            <div class="dcard-r"><span class="dot-ic t-green"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 7-7M16 4l3 3"/></svg></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic fk"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6"/><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3"/></svg></span><span class="nm">user_id</span><span class="ty">bigint</span></div>
            <div class="dcard-r"><span class="dot-ic"><i></i></span><span class="nm">title</span><span class="ty">varchar</span></div>
          </div>
        </div>
      </div>

      <div class="code-card reveal">
        <div class="code-bar">
          <span class="tl tl-r"></span>
          <span class="tl tl-y"></span>
          <span class="tl tl-g"></span>
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
        <p>Add tables and columns visually. Pick column types from a dropdown — no syntax to memorize.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">2</div>
        <h3>Connect the dots</h3>
        <p>Point a foreign key at another table and Schematic draws the relationship with crow's-foot notation.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">3</div>
        <h3>Export &amp; ship</h3>
        <p>Download ready-to-run migrations, SQL, Prisma, or DBML — or push your schema straight to Supabase. Drop it in your repo and migrate.</p>
      </div>
    </div>

    <div class="stats reveal mt-xl">
      <div class="stat"><div class="stat-n">12k+</div><div class="stat-l">Schemas designed</div></div>
      <div class="stat"><div class="stat-n">40+</div><div class="stat-l">Column types</div></div>
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Export formats</div></div>
      <div class="stat"><div class="stat-n">&lt;5 min</div><div class="stat-l">To first migration</div></div>
    </div>
  </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="section" id="about">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">ABOUT US</span>
      <h2>Built by <span class="grad">Schematic Labs</span></h2>
      <p>We're a small product studio building SaaS applications and developer web tools that make everyday engineering faster. Schematic is one of them.</p>
    </div>

    <div class="bento">
      <div class="cell span-3 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <h3>Our mission</h3>
        <p>Ship SaaS products and web tools that remove friction from the developer workflow — visual where it helps, code where it counts, no bloat in between.</p>
      </div>

      <div class="cell span-3 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/></svg></div>
        <h3>What we build</h3>
        <p>SaaS applications and focused web tools — from this visual database schema builder to the next tool on our roadmap. Practical software, built by developers for developers.</p>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg></div>
        <h3>Developer-first</h3>
        <p>Real types, real exports, real output you can drop into your repo. We sweat the details devs actually care about.</p>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
        <h3>Lean &amp; fast</h3>
        <p>A small team shipping quickly. Less overhead, faster iteration, tools that stay simple as they grow.</p>
      </div>

      <div class="cell span-2 reveal">
        <div class="cell-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></div>
        <h3>Built to ship</h3>
        <p>Every tool earns its place by getting real work done. If it doesn't move your project forward, we don't build it.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
{{-- <section class="section" id="pricing">
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
    </div>
  </div>
</section> --}}

<!-- ============ CTA BAND ============ -->
<section class="cta-band">
  <div class="wrap">
    <div class="cta-box reveal">
      <div class="cta-dots"></div>
      <h2>Design your next schema in the open</h2>
      <p>Join thousands of developers building their database visually.</p>
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
        <p class="footer-brand-desc">The visual database schema builder for modern dev teams. A Schematic Labs product.</p>
      </div>
      <div class="footer-col"><h4>Product</h4><a href="#features">Features</a><a href="#how">How it works</a><a href="{{ $tryUrl }}">Live demo</a></div>
      <div class="footer-col"><h4>Company</h4><a href="#about">About</a><a href="{{ route('contact') }}">Contact</a></div>
      <div class="footer-col"><h4>Legal</h4><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a></div>
    </div>
    <div class="footer-bot">
      <span class="copy">© 2026 Schematic Labs. All rights reserved.</span>
    </div>
  </div>
</footer>

<script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}"></script>
</body>
</html>
