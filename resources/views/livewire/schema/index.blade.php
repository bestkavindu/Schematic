@php
    $projects = $this->projects->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'tables' => $p->tables_count,
        'edited' => optional($p->updated_at)->diffForHumans() ?? 'just now',
        'colors' => $p->tables->pluck('color')->unique()->values()->take(4)->all() ?: ['blue'],
        'url' => route('schemas.builder', $p),
    ])->values();
    $user = auth()->user();
@endphp

<div class="dash screen-fade" x-data="schematicDashboard(@js($projects))" x-cloak>
    {{-- Main-site navbar — shared look with the landing header --}}
    <header class="site-nav">
        <div class="site-nav-inner">
            <a class="site-brand" href="{{ route('schemas.index') }}" wire:navigate>
                <span class="site-brand-logo">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></svg>
                </span>
                Schematic
            </a>
            <nav class="site-nav-links">
                <a class="site-nav-link active" href="{{ route('schemas.index') }}" wire:navigate>Schemas</a>
                <a class="site-nav-link" href="{{ route('home') }}#features">Features</a>
                <a class="site-nav-link" href="{{ route('home') }}#pricing">Pricing</a>
                <a class="site-nav-link" href="{{ route('schemas.demo') }}" wire:navigate>Live demo</a>
            </nav>
            <div class="site-nav-spacer"></div>
            <div class="site-nav-cta">
                <button class="btn btn-primary" @click="newProject()">
                    <span x-html="icon('Plus', { size: 15 })" style="display:flex"></span> New Project
                </button>
                <div class="avatar" title="{{ $user->name }}">{{ $user->initials() }}</div>
            </div>
        </div>
    </header>

    <div class="dash-main">
        <div class="dash-head">
            <div>
                <h1 class="dash-h1">Your schemas</h1>
                <p class="dash-sub" x-text="projects.length + ' project' + (projects.length === 1 ? '' : 's') + ' · Laravel workspace'"></p>
            </div>
            <div class="search" style="width: 280px;">
                <span x-html="icon('Search', { size: 15 })" style="display:flex"></span>
                <input placeholder="Search projects" x-model="q" />
            </div>
        </div>

        <div class="dash-tabs">
            <template x-for="t in tabs" :key="t">
                <button class="dash-tab" :class="{ active: tab === t }" @click="tab = t" x-text="t"></button>
            </template>
        </div>

        <div class="proj-grid">
            <button class="proj-new" @click="newProject()">
                <div class="proj-new-ic" x-html="icon('Plus', { size: 20 })"></div>
                <div style="font-size: 13.5px; font-weight: 560;">New Project</div>
                <div style="font-size: 11.5px; color: var(--faint);">Start from a blank canvas</div>
            </button>

            <template x-for="(p, i) in shown" :key="p.id">
                <a class="proj-card" :href="p.url" wire:navigate>
                    <div class="proj-thumb" x-html="miniThumb(p.colors, i)"></div>
                    <div class="proj-body">
                        <div class="proj-card-name" x-text="p.name"></div>
                        <div class="proj-foot">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span x-html="icon('Database', { size: 12 })" style="display:flex"></span>
                                <span x-text="p.tables + ' tables'"></span>
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
            <div style="text-align: center; color: var(--faint); font-size: 13px; padding: 48px 0;">
                No projects match “<span x-text="q"></span>”
            </div>
        </template>
    </div>
</div>
