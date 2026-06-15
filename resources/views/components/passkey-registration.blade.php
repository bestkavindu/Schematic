@assets
@vite('resources/js/passkeys.js')
@endassets

<div
    x-data="{
        supported: false,
        showForm: false,
        name: '',
        loading: false,
        error: null,
        updateSupport() {
            this.supported = Boolean(window.Passkeys?.isSupported());
        },
        init() {
            this.updateSupport();

            window.addEventListener('passkeys:ready', () => this.updateSupport(), { once: true });
        },
        async register() {
            if (!this.name.trim()) return;

            this.loading = true;
            this.error = null;

            try {
                await window.Passkeys.register({ name: this.name });
                this.name = '';
                this.showForm = false;
                await $wire.loadPasskeys();
            } catch (e) {
                if (e.constructor?.name !== 'UserCancelledError') {
                    this.error = e.message;
                }
            } finally {
                this.loading = false;
            }
        },
        cancel() {
            this.showForm = false;
            this.name = '';
            this.error = null;
        },
    }"
>
    <template x-if="!supported">
        <p class="pk-note">Passkeys are not supported in this browser.</p>
    </template>

    <template x-if="supported && !showForm">
        <button type="button" class="btn btn-primary" x-on:click="showForm = true">
            <span x-html="icon('Plus', { size: 15 })" style="display:flex"></span> Add passkey
        </button>
    </template>

    <template x-if="supported && showForm">
        <div class="pk-form">
            <div class="field">
                <span class="field-label">Passkey name</span>
                <input class="input" x-model="name" placeholder="e.g., MacBook Pro, iPhone"
                       x-on:keydown.enter.prevent="register()"
                       x-ref="passkeyNameInput"
                       x-init="$nextTick(() => $refs.passkeyNameInput?.focus())" />
                <span class="pk-hint">Give this passkey a name to help you identify it later.</span>
            </div>

            <p x-show="error" x-text="error" x-cloak class="field-error"></p>

            <div class="set-actions">
                <button type="button" class="btn btn-primary" x-on:click="register()" x-bind:disabled="loading || !name.trim()">
                    <span x-show="!loading">Register passkey</span>
                    <span x-show="loading" x-cloak>Registering…</span>
                </button>
                <button type="button" class="btn" x-on:click="cancel()">Cancel</button>
            </div>
        </div>
    </template>
</div>
