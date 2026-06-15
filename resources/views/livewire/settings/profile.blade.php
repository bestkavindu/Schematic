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
                <a class="set-nav-item active" href="{{ route('profile.edit') }}" wire:navigate>
                    <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span> Profile
                </a>
                <a class="set-nav-item" href="{{ route('security.edit') }}" wire:navigate>
                    <span x-html="icon('Key', { size: 15 })" style="display:flex"></span> Security
                </a>
            </nav>

            {{-- ── content ── --}}
            <div class="set-content">
                <section class="set-card">
                    <div class="set-card-head">
                        <h2 class="set-card-title">Profile</h2>
                        <p class="set-card-desc">Update your name and email address</p>
                    </div>

                    <form wire:submit="updateProfileInformation" class="set-form">
                        <div class="field">
                            <span class="field-label">Name</span>
                            <input class="input" type="text" wire:model="name" required autofocus autocomplete="name" />
                            @error('name') <span class="field-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <span class="field-label">Email</span>
                            <input class="input" type="email" wire:model="email" required autocomplete="email" />
                            @error('email') <span class="field-error">{{ $message }}</span> @enderror

                            @if ($this->hasUnverifiedEmail)
                                <div class="set-note warn">
                                    Your email address is unverified.
                                    <button type="button" class="set-link" wire:click.prevent="resendVerificationNotification">
                                        Re-send verification email
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="set-actions">
                            <button type="submit" class="btn btn-primary">
                                <span x-html="icon('Save', { size: 15 })" style="display:flex"></span> Save
                            </button>
                        </div>
                    </form>
                </section>

                @if ($this->showDeleteUser)
                    <livewire:settings.delete-user-form />
                @endif
            </div>
        </div>
    </div>

    {{-- ───────── toast ───────── --}}
    <div class="toast" x-show="toast" x-transition style="display:none">
        <span class="tk" x-html="icon('Check', { size: 15 })"></span>
        <span x-text="toast"></span>
    </div>
</div>
