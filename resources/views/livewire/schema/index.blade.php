@php
    $projects = $this->projects->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'tables' => $p->tables_count,
        'edited' => optional($p->updated_at)->diffForHumans() ?? 'just now',
        'colors' => $p->tables->pluck('color')->unique()->values()->take(4)->all() ?: ['blue'],
        'favorite' => (bool) $p->favorite,
        'url' => route('schemas.builder', $p),
    ])->values();

    $user = auth()->user();
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('') ?: mb_strtoupper(mb_substr($user->email, 0, 1));
    $memberSince = optional($user->created_at)->format('F Y') ?? '—';
    $verified = ! method_exists($user, 'hasVerifiedEmail') || $user->hasVerifiedEmail();
@endphp

<div class="dash screen-fade" x-data="schematicDashboard(@js($projects))" x-cloak>

    {{-- ---------- top bar with brand + account menu ---------- --}}
    <header class="dash-topbar" x-data="{ menu: false, profile: false }" @keydown.escape.window="menu = false; profile = false">
        <a class="topbar-brand" href="{{ route('home') }}" wire:navigate>
            <span class="topbar-logo" x-html="icon('Database', { size: 17 })"></span>
            Schematic
        </a>

        <div class="topbar-spacer"></div>

        {{-- account dropdown --}}
        <div class="acct" @click.outside="menu = false">
            <button type="button" class="acct-btn" :class="{ open: menu }" @click="menu = !menu" aria-label="Account menu">
                <span class="avatar">{{ $initials }}</span>
                <span class="acct-name">{{ $user->name }}</span>
                <span class="acct-chev" :class="{ flip: menu }" x-html="icon('ChevronDown', { size: 14 })"></span>
            </button>

            <div class="menu acct-menu" x-show="menu" x-transition.origin.top.right style="display:none" @click="menu = false">
                <div class="acct-head">
                    <span class="avatar avatar-lg">{{ $initials }}</span>
                    <div class="acct-head-meta">
                        <div class="acct-head-name">{{ $user->name }}</div>
                        <div class="acct-head-email">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="menu-sep"></div>
                <button type="button" class="menu-item" @click="profile = true">
                    <span x-html="icon('Search', { size: 15 })" style="display:flex"></span>
                    View profile
                </button>
                <a class="menu-item" href="{{ route('profile.edit') }}" wire:navigate>
                    <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span>
                    Edit profile
                </a>
                <a class="menu-item" href="{{ route('appearance.edit') }}" wire:navigate>
                    <span x-html="icon('Palette', { size: 15 })" style="display:flex"></span>
                    Appearance
                </a>
                <div class="menu-sep"></div>
                <button type="button" class="menu-item danger" wire:click="logout">
                    <span x-html="icon('X', { size: 15 })" style="display:flex"></span>
                    Log out
                </button>
            </div>
        </div>

        {{-- ---------- view-profile modal ---------- --}}
        <template x-teleport="body">
            <div class="pf-scrim" x-show="profile" x-transition.opacity style="display:none" @click="profile = false">
                <div class="pf-card" @click.stop x-show="profile" x-transition.scale.origin.top>
                    <button type="button" class="pf-close" @click="profile = false" aria-label="Close" x-html="icon('X', { size: 16 })"></button>

                    <div class="pf-hero">
                        <span class="avatar avatar-xl">{{ $initials }}</span>
                        <div class="pf-name">{{ $user->name }}</div>
                        <div class="pf-email">{{ $user->email }}</div>
                        @if ($verified)
                            <span class="pf-badge ok"><span x-html="icon('Check', { size: 12 })" style="display:flex"></span> Verified</span>
                        @else
                            <span class="pf-badge warn">Unverified email</span>
                        @endif
                    </div>

                    <div class="pf-stats">
                        <div class="pf-stat">
                            <div class="pf-stat-n" x-text="projects.length"></div>
                            <div class="pf-stat-l">Projects</div>
                        </div>
                        <div class="pf-stat">
                            <div class="pf-stat-n" x-text="projects.reduce((s, p) => s + p.tables, 0)"></div>
                            <div class="pf-stat-l">Tables</div>
                        </div>
                        <div class="pf-stat">
                            <div class="pf-stat-n pf-stat-sm">{{ $memberSince }}</div>
                            <div class="pf-stat-l">Member since</div>
                        </div>
                    </div>

                    <div class="pf-actions">
                        <a class="btn btn-primary" href="{{ route('profile.edit') }}" wire:navigate>
                            <span x-html="icon('Edit', { size: 14 })" style="display:flex"></span>
                            Edit profile
                        </a>
                        <button type="button" class="btn" @click="profile = false">Close</button>
                    </div>
                </div>
            </div>
        </template>
    </header>

    <div class="dash-main">
        <div class="dash-head">
            <div>
                <h1 class="dash-h1">Your schemas</h1>
                <p class="dash-sub" x-text="projects.length + ' project' + (projects.length === 1 ? '' : 's') + ' · Laravel workspace'"></p>
            </div>
            <div class="search search-dash" @keydown.escape.stop="q = ''">
                <span x-html="icon('Search', { size: 15 })" style="display:flex"></span>
                <input placeholder="Search projects" x-model="q" />
                <button type="button" class="search-clear" x-show="q" @click="q = ''" aria-label="Clear search"
                        x-html="icon('X', { size: 13 })"></button>
            </div>
        </div>

        <div class="dash-tabs">
            <template x-for="t in tabs" :key="t">
                <button class="dash-tab" :class="{ active: tab === t }" @click="tab = t">
                    <template x-if="tabIcon(t)">
                        <span class="dash-tab-ic" x-html="icon(tabIcon(t), { size: 13 })"></span>
                    </template>
                    <span x-text="t"></span>
                    <template x-if="tabCount(t) !== null">
                        <span class="dash-tab-count" x-text="tabCount(t)"></span>
                    </template>
                </button>
            </template>
        </div>

        <div class="proj-grid">
            <button class="proj-new" @click="newProject()">
                <div class="proj-new-ic" x-html="icon('Plus', { size: 20 })"></div>
                <div style="font-size: 13.5px; font-weight: 560;">New Project</div>
                <div style="font-size: 11.5px; color: var(--faint);">Start from a blank canvas</div>
            </button>

            <template x-for="(p, i) in shown" :key="p.id">
                <a class="proj-card" :href="p.url" wire:navigate @click="recordView(p.id)">
                    <div class="proj-actions">
                        <button type="button" class="fav-star" :class="{ 'is-fav': p.favorite }"
                                @click.prevent.stop="toggleFav(p)"
                                :title="p.favorite ? 'Remove from favorites' : 'Add to favorites'"
                                :aria-pressed="p.favorite"
                                x-html="icon('Star', { size: 15, fill: p.favorite })"></button>
                    </div>
                    <div class="proj-thumb" x-html="miniThumb(p.colors, i)"></div>
                    <div class="proj-body">
                        <div class="proj-card-name" x-text="p.name"></div>
                        <div class="proj-foot">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span x-html="icon('Database', { size: 12 })" style="display:flex"></span>
                                <span><span class="proj-foot-num" x-text="p.tables"></span> tables</span>
                            </span>
                            <span class="dot"></span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span x-html="icon('Clock', { size: 12 })" style="display:flex"></span>
                                <span x-text="p.edited"></span>
                            </span>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        <template x-if="projects.length && !shown.length">
            <div>
                <template x-if="tab === 'Recent' && !q">
                    <div class="dash-empty">
                        <div class="dash-empty-ic" x-html="icon('Clock', { size: 20 })"></div>
                        <div class="dash-empty-h">Nothing opened yet</div>
                        <div class="dash-empty-sub">Projects you open will show up here so you can jump back in fast.</div>
                        <button type="button" class="dash-empty-cta" @click="tab = 'All'">
                            <span x-html="icon('Database', { size: 14 })" style="display:flex"></span> Browse all projects
                        </button>
                    </div>
                </template>
                <template x-if="tab === 'Favorites' && !q">
                    <div class="dash-empty">
                        <div class="dash-empty-ic is-fav" x-html="icon('Star', { size: 20 })"></div>
                        <div class="dash-empty-h">No favorites yet</div>
                        <div class="dash-empty-sub">Hover any project and tap the star to pin it here for quick access.</div>
                    </div>
                </template>
                <template x-if="(tab !== 'Recent' && tab !== 'Favorites') || q">
                    <div class="dash-empty">
                        <div class="dash-empty-ic" x-html="icon('Search', { size: 20 })"></div>
                        <div class="dash-empty-h">No matching projects</div>
                        <div class="dash-empty-sub">Nothing matches <span style="color:var(--ink-2);font-weight:560">“<span x-text="q"></span>”</span>. Try a different term.</div>
                        <button type="button" class="dash-empty-cta" x-show="q" @click="q = ''">
                            <span x-html="icon('X', { size: 14 })" style="display:flex"></span> Clear search
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
