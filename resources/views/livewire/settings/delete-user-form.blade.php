<section class="set-card set-card-danger" x-data="{ open: @js($errors->isNotEmpty()) }" @keydown.escape.window="open = false">
    <div class="set-card-head">
        <h2 class="set-card-title">Delete account</h2>
        <p class="set-card-desc">Permanently delete your account and all of its schemas. This cannot be undone.</p>
    </div>

    <div class="set-actions">
        <button type="button" class="btn btn-danger" @click="open = true">
            <span x-html="icon('Trash', { size: 15 })" style="display:flex"></span> Delete account
        </button>
    </div>

    <template x-teleport="body">
        <div class="pf-scrim" x-show="open" x-transition.opacity style="display:none" @click="open = false">
            <div class="pf-card" @click.stop x-show="open" x-transition.scale.origin.top>
                <button type="button" class="pf-close" @click="open = false" aria-label="Close" x-html="icon('X', { size: 16 })"></button>

                <div style="margin-bottom: 18px;">
                    <h3 style="font-size: 17px; font-weight: 660; letter-spacing: -.01em; margin: 0 0 6px;">Delete your account?</h3>
                    <p style="font-size: 13px; line-height: 1.5; color: var(--muted); margin: 0;">
                        Once deleted, all of your schemas and data are permanently removed. Enter your password to confirm.
                    </p>
                </div>

                <form wire:submit="deleteUser" class="set-form">
                    <div class="field">
                        <span class="field-label">Password</span>
                        <input class="input" type="password" wire:model="password" autocomplete="current-password"
                               x-ref="pw" x-effect="open && $nextTick(() => $refs.pw.focus())" />
                        @error('password') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="set-modal-actions">
                        <button type="button" class="btn" @click="open = false">Cancel</button>
                        <button type="submit" class="btn btn-danger-solid">Delete account</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</section>
