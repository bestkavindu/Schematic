<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<title>@yield('docTitle', 'Schematic')</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;450;500;520;540;560;600;640;680;700&family=Geist+Mono:wght@400;450;500;640&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
</head>
<body>
<div class="auth">

  <!-- ============ LEFT: FORM ============ -->
  <div class="auth-left">
    <a class="brand" href="{{ route('home') }}">
      <span class="brand-logo"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg></span>
      Schematic
    </a>
    <a class="back-home" href="{{ route('home') }}">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      Back to home
    </a>

    <div class="form-col">
      @yield('formCol')
    </div>

    <div class="legal">
      <a href="#">Privacy</a><a href="#">Terms</a><a href="#">© 2026 Schematic Labs</a>
    </div>
  </div>

  <!-- ============ RIGHT: BRANDED ASIDE ============ -->
  <aside class="auth-aside">
    <div class="aside-dots"></div>
    <div class="aside-glow g1"></div>
    <div class="aside-glow g2"></div>
    <div class="aside-inner">
      <span class="aside-eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
        Visual schema design
      </span>
      <h2 class="aside-h2" id="asideTitle">@yield('asideTitle', 'Your database, drawn the way you think about it.')</h2>
      <p class="aside-p" id="asideSub">@yield('asideSub', 'Drag out tables, connect foreign keys, and export clean Laravel migrations — all from one shared canvas.')</p>

      <!-- floating diagram -->
      <div class="aside-stage" id="asideStage">
        <svg class="aside-svg" id="asideSvg"></svg>
        <div class="gcard" style="left:8px; top:24px;" data-card="users">
          <div class="gcard-h" style="color:#bfdbfe;">users</div>
          <div class="gcard-r"><span class="gdot" style="background:#60a5fa;"></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
          <div class="gcard-r"><span class="gdot"></span><span class="nm">name</span><span class="ty">varchar</span></div>
          <div class="gcard-r"><span class="gdot"></span><span class="nm">email</span><span class="ty">varchar</span></div>
        </div>
        <div class="gcard" style="right:8px; top:120px;" data-card="posts">
          <div class="gcard-h" style="color:#a7f3d0;">posts</div>
          <div class="gcard-r"><span class="gdot" style="background:#34d399;"></span><span class="nm pk">id</span><span class="ty">bigint</span></div>
          <div class="gcard-r"><span class="gdot" style="background:#a5b4fc;"></span><span class="nm">user_id</span><span class="ty">bigint</span></div>
          <div class="gcard-r"><span class="gdot"></span><span class="nm">title</span><span class="ty">varchar</span></div>
        </div>
      </div>

      <div class="aside-quote">
        <p class="aq-text">"Schematic replaced a wall of half-correct migrations with one diagram. New engineers get our database in minutes."</p>
        <div class="aq-by">
          <div class="aq-avatar">RT</div>
          <div>
            <div class="aq-name">Rina Takahashi</div>
            <div class="aq-role">Staff Engineer · Lumenpay</div>
          </div>
        </div>
        <div class="aside-stats">
          <div class="aside-stat"><div class="n">12k+</div><div class="l">Schemas designed</div></div>
          <div class="aside-stat"><div class="n">40+</div><div class="l">Column types</div></div>
          <div class="aside-stat"><div class="n">&lt;5 min</div><div class="l">To first migration</div></div>
        </div>
      </div>
    </div>
  </aside>

</div>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
