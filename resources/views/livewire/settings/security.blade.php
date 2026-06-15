@php
    $u = auth()->user();
    $initials = collect(explode(' ', trim($u->name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: mb_strtoupper(mb_substr($u->email, 0, 1));
@endphp

<div class="dash screen-fade" x-data="{ toast: '', _t: null, ping(text) { this.toast = text; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2800); } }"
     @settings-saved.window="ping($event.detail.text)">

    {{-- ───────── top bar ───────── --}}
    <header class="dash-topbar">
        <a class="topbar-brand" href="{{ route('schemas.index') }}" wire:navigate>
            <span class="topbar-logo" x-html="icon('Database', { size: 17 })"></span>
            Schematic
        </a>
        <div class="topbar-spacer"></div>
        <a class="btn btn-ghost" href="{{ route('schemas.index') }}" wire:navigate>
            <span x-html="icon('ChevronRight', { size: 14 })" style="display:flex; transform: rotate(180deg);"></span>
            Back to schemas
        </a>
        <span class="avatar" title="{{ $u->name }}">{{ $initials }}</span>
    </header>

    <div class="set-main">
        <div class="set-head">
            <h1 class="set-h1">Settings</h1>
            <p class="set-sub">Manage your profile and account</p>
        </div>

        <div class="set-grid">
            {{-- ── side nav ── --}}
            <nav class="set-nav" aria-label="Settings">
                <a class="set-nav-item" href="{{ route('profile.edit') }}" wire:navigate>
                    <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span> Profile
                </a>
                <a class="set-nav-item active" href="{{ route('security.edit') }}" wire:navigate>
                    <span x-html="icon('Key', { size: 15 })" style="display:flex"></span> Security
                </a>
            </nav>

            {{-- ── content ── --}}
            <div class="set-content">

                {{-- ===== Password ===== --}}
                <section class="set-card">
                    <div class="set-card-head">
                        <h2 class="set-card-title">Update password</h2>
                        <p class="set-card-desc">Use a long, random password to keep your account secure.</p>
                    </div>

                    <form method="POST" wire:submit="updatePassword" class="set-form">
                        <div class="field">
                            <span class="field-label">Current password</span>
                            <input class="input" type="password" wire:model="current_password" required autocomplete="current-password" />
                            @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <span class="field-label">New password</span>
                            <input class="input" type="password" wire:model="password" required autocomplete="new-password" />
                            @error('password') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <span class="field-label">Confirm password</span>
                            <input class="input" type="password" wire:model="password_confirmation" required autocomplete="new-password" />
                        </div>
                        <div class="set-actions">
                            <button type="submit" class="btn btn-primary">
                                <span x-html="icon('Save', { size: 15 })" style="display:flex"></span> Save
                            </button>
                        </div>
                    </form>
                </section>

                {{-- ===== Two-factor ===== --}}
                @if ($canManageTwoFactor)
                    <section class="set-card" wire:cloak>
                        <div class="set-card-head">
                            <h2 class="set-card-title">
                                Two-factor authentication
                                @if ($twoFactorEnabled)
                                    <span class="set-pill ok">On</span>
                                @else
                                    <span class="set-pill">Off</span>
                                @endif
                            </h2>
                            <p class="set-card-desc">Add an extra layer of security using a TOTP authenticator app.</p>
                        </div>

                        @if ($twoFactorEnabled)
                            <div class="set-actions">
                                <button type="button" class="btn btn-danger" wire:click="disable">Disable 2FA</button>
                            </div>
                            <div style="margin-top: 18px;">
                                <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />
                            </div>
                        @else
                            <div class="set-actions">
                                <button type="button" class="btn btn-primary" wire:click="enable">
                                    <span x-html="icon('Key', { size: 15 })" style="display:flex"></span> Enable 2FA
                                </button>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- ===== Passkeys ===== --}}
                @if ($canManagePasskeys)
                    <section class="set-card" wire:cloak>
                        <div class="set-card-head">
                            <h2 class="set-card-title">Passkeys</h2>
                            <p class="set-card-desc">Sign in without a password using a passkey on your device.</p>
                        </div>

                        <div class="pk-list">
                            @forelse ($passkeys as $passkey)
                                <div class="pk-row">
                                    <span class="pk-ic" x-html="icon('Key', { size: 17 })"></span>
                                    <div class="pk-meta">
                                        <div class="pk-name-row">
                                            <span class="pk-name">{{ $passkey['name'] }}</span>
                                            @if ($passkey['authenticator'])
                                                <span class="set-pill">{{ $passkey['authenticator'] }}</span>
                                            @endif
                                        </div>
                                        <div class="pk-sub">
                                            Added {{ $passkey['created_at_diff'] }}
                                            @if ($passkey['last_used_at_diff'])
                                                <span class="pk-dot"></span> Last used {{ $passkey['last_used_at_diff'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" class="pk-del" title="Remove passkey"
                                            wire:click="confirmDelete({{ $passkey['id'] }})"
                                            x-html="icon('Trash', { size: 15 })"></button>
                                </div>
                            @empty
                                <div class="pk-empty">
                                    <span class="pk-empty-ic" x-html="icon('Key', { size: 20 })"></span>
                                    <div class="pk-empty-h">No passkeys yet</div>
                                    <div class="pk-empty-sub">Add a passkey to sign in without a password.</div>
                                </div>
                            @endforelse
                        </div>

                        <div style="margin-top: 16px;">
                            <x-passkey-registration />
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    {{-- ───────── 2FA setup modal ───────── --}}
    @if ($canManageTwoFactor)
        <div class="pf-scrim" x-show="$wire.showModal" x-transition.opacity style="display:none"
             wire:click="closeModal" @keydown.escape.window="$wire.showModal && $wire.closeModal()">
            <div class="pf-card pf-card-wide" @click.stop x-show="$wire.showModal" x-transition.scale.origin.top>
                <button type="button" class="pf-close" wire:click="closeModal" aria-label="Close" x-html="icon('X', { size: 16 })"></button>

                <div class="set-modal-head">
                    <h3 class="set-modal-title">{{ $this->modalConfig['title'] }}</h3>
                    <p class="set-modal-desc">{{ $this->modalConfig['description'] }}</p>
                </div>

                @if ($showVerificationStep)
                    <form wire:submit="confirmTwoFactor" class="set-form" style="max-width:none">
                        <div class="field">
                            <span class="field-label">Authentication code</span>
                            <input class="input otp-input" inputmode="numeric" autocomplete="one-time-code"
                                   maxlength="6" wire:model.live="code" x-init="$nextTick(() => $el.focus())" />
                            @error('code') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="set-modal-actions">
                            <button type="button" class="btn" wire:click="resetVerification">Back</button>
                            <button type="submit" class="btn btn-primary" x-bind:disabled="($wire.code || '').length < 6">Confirm</button>
                        </div>
                    </form>
                @else
                    @error('setupData')
                        <div class="set-note danger">{{ $message }}</div>
                    @enderror

                    <div class="otp-qr">
                        @empty($qrCodeSvg)
                            <div class="otp-qr-loading">Loading…</div>
                        @else
                            <div class="otp-qr-svg">{!! $qrCodeSvg !!}</div>
                        @endempty
                    </div>

                    @if (filled($manualSetupKey))
                        <div class="field" x-data="{ copied: false, async copy() { try { await navigator.clipboard.writeText('{{ $manualSetupKey }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } catch (e) {} } }">
                            <span class="field-label">Or enter this key manually</span>
                            <div class="otp-key">
                                <input class="input mono" type="text" readonly value="{{ $manualSetupKey }}" style="border:none; box-shadow:none;" />
                                <button type="button" class="otp-key-copy" @click="copy()" :title="copied ? 'Copied' : 'Copy'">
                                    <span x-show="!copied" x-html="icon('Copy', { size: 15 })"></span>
                                    <span x-show="copied" x-cloak class="tk" x-html="icon('Check', { size: 15 })"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="set-modal-actions">
                        <button type="button" class="btn btn-primary" wire:click="showVerificationIfNecessary"
                                @if ($errors->has('setupData')) disabled @endif>
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ───────── delete passkey modal ───────── --}}
    @if ($canManagePasskeys)
        <div class="pf-scrim" x-show="$wire.showDeleteModal" x-transition.opacity style="display:none"
             wire:click="closeDeleteModal" @keydown.escape.window="$wire.showDeleteModal && $wire.closeDeleteModal()">
            <div class="pf-card" @click.stop x-show="$wire.showDeleteModal" x-transition.scale.origin.top>
                <button type="button" class="pf-close" wire:click="closeDeleteModal" aria-label="Close" x-html="icon('X', { size: 16 })"></button>
                <div class="set-modal-head">
                    <h3 class="set-modal-title">Remove passkey</h3>
                    <p class="set-modal-desc">Remove “{{ $deletingPasskeyName }}”? You'll no longer be able to use it to sign in.</p>
                </div>
                <div class="set-modal-actions">
                    <button type="button" class="btn" wire:click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger-solid" wire:click="deletePasskey">Remove passkey</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ───────── toast ───────── --}}
    <div class="toast" x-show="toast" x-transition style="display:none">
        <span class="tk" x-html="icon('Check', { size: 15 })"></span>
        <span x-text="toast"></span>
    </div>
</div>
