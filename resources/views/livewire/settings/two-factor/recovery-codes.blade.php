<div class="rc-box" wire:cloak x-data="{ show: false }">
    <div class="rc-head">
        <div class="rc-title">
            <span x-html="icon('Key', { size: 15 })" style="display:flex; color: var(--muted)"></span>
            Recovery codes
        </div>
        <p class="rc-desc">Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.</p>
    </div>

    <div class="rc-actions">
        <button type="button" class="btn" x-show="!show" @click="show = true">
            <span x-html="icon('Search', { size: 14 })" style="display:flex"></span> View codes
        </button>
        <button type="button" class="btn" x-show="show" @click="show = false">Hide codes</button>

        @if (filled($recoveryCodes))
            <button type="button" class="btn" x-show="show" wire:click="regenerateRecoveryCodes">
                Regenerate
            </button>
        @endif
    </div>

    <div x-show="show" x-transition x-cloak style="margin-top: 12px;">
        @error('recoveryCodes')
            <div class="set-note danger">{{ $message }}</div>
        @enderror

        @if (filled($recoveryCodes))
            <div class="rc-codes" role="list" aria-label="Recovery codes">
                @foreach ($recoveryCodes as $code)
                    <div role="listitem" wire:loading.class="rc-loading">{{ $code }}</div>
                @endforeach
            </div>
            <p class="rc-foot">Each code can be used once and is removed after use. Regenerate to get a fresh set.</p>
        @endif
    </div>
</div>
