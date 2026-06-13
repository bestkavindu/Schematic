@extends('layouts.auth-schematic')

@section('docTitle', 'Sign up · Schematic')
@section('asideTitle', 'Start designing your schema in under five minutes.')
@section('asideSub', 'Free for solo developers. No credit card required — bring your tables and start connecting.')

@section('formCol')
  <div class="form-head">
    <h1>Create your account</h1>
    <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
  </div>

  <!-- social (decorative — wire to an OAuth provider to enable) -->
  <div class="social">
    <button class="social-btn" type="button">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.6 2 12.3c0 4.5 2.9 8.3 6.8 9.7.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.4-3.4-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.7 1a9.3 9.3 0 0 1 5 0c1.9-1.3 2.7-1 2.7-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.3 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5a10.3 10.3 0 0 0 6.8-9.7C22 6.6 17.5 2 12 2Z"/></svg>
      GitHub
    </button>
    <button class="social-btn" type="button">
      <svg width="17" height="17" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.5 12.2c0-.7-.1-1.4-.2-2H12v3.9h5.9a5 5 0 0 1-2.2 3.3v2.7h3.5c2-1.9 3.3-4.7 3.3-7.9Z"/><path fill="#34A853" d="M12 23c3 0 5.5-1 7.3-2.7l-3.5-2.7c-1 .7-2.3 1.1-3.8 1.1-2.9 0-5.3-1.9-6.2-4.6H2.2v2.8A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.8 14.1a6.6 6.6 0 0 1 0-4.2V7.1H2.2a11 11 0 0 0 0 9.8l3.6-2.8Z"/><path fill="#EA4335" d="M12 5.4c1.6 0 3 .6 4.2 1.6l3.1-3.1A11 11 0 0 0 2.2 7.1l3.6 2.8C6.7 7.3 9.1 5.4 12 5.4Z"/></svg>
      Google
    </button>
  </div>

  <div class="divider"><span>or continue with email</span></div>

  <form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="field @error('name') error @enderror" id="fName">
      <label class="field-label" for="name">Full name</label>
      <div class="input-wrap">
        <input class="input @error('name') invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Amir Karimi" autocomplete="name" autofocus required />
        <span class="lead"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span>
      </div>
      <div class="field-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('name'){{ $message }}@else Please enter your name @enderror</span></div>
    </div>

    <div class="field @error('email') error @enderror" id="fEmail">
      <label class="field-label" for="email">Email</label>
      <div class="input-wrap">
        <input class="input @error('email') invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required />
        <span class="lead"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
      </div>
      <div class="field-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('email'){{ $message }}@else Enter a valid email address @enderror</span></div>
    </div>

    <div class="field show-strength @error('password') error @enderror" id="fPass">
      <label class="field-label" for="password"><span>Password</span></label>
      <div class="input-wrap">
        <input class="input has-toggle @error('password') invalid @enderror" id="password" name="password" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <span class="lead"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
        <button class="eye" type="button" aria-label="Show password">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="field-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('password'){{ $message }}@else Password is required @enderror</span></div>
      <div class="strength">
        <div class="strength-bars"><span class="strength-bar"></span><span class="strength-bar"></span><span class="strength-bar"></span><span class="strength-bar"></span></div>
        <div class="strength-label" id="strengthLabel">Use 8+ characters with a mix of letters &amp; numbers</div>
      </div>
    </div>

    <div class="field @error('password_confirmation') error @enderror" id="fPassConfirm">
      <label class="field-label" for="password_confirmation"><span>Confirm password</span></label>
      <div class="input-wrap">
        <input class="input has-toggle @error('password_confirmation') invalid @enderror" id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required />
        <span class="lead"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
        <button class="eye" type="button" aria-label="Show password">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="field-error"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('password_confirmation'){{ $message }}@else Passwords must match @enderror</span></div>
    </div>

    <p class="terms" id="termsRow">
      <label class="check" style="align-items:flex-start;">
        <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} />
        <span class="box"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
      </label>
    </p>

    <button class="btn-submit" type="submit" id="submitBtn" data-test="register-user-button">
      <span class="label">Create account</span>
      <span class="spin"></span>
    </button>
  </form>
@endsection
