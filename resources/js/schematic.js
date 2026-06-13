/*
 * Schematic — visual database schema builder (Alpine.js port of the design prototype).
 *
 * The canvas is fully client-side (drag / zoom / pan / live crow's-foot relationship
 * lines); the surrounding Livewire component handles persistence via $wire.save().
 */

// ---------- static data (ported from the design's data.js) ----------
const GEO = { CARD_W: 264, HEADER_H: 46, ROW_H: 34 };

const COLOR_KEYS = ['blue', 'green', 'purple', 'amber', 'red', 'teal', 'orange', 'pink'];

const LARAVEL_TYPES = [
    'id', 'bigInteger', 'unsignedBigInteger', 'integer', 'string', 'text',
    'boolean', 'date', 'datetime', 'timestamp', 'json', 'decimal', 'float', 'uuid',
];

// Referential actions offered for relationships (ON DELETE / ON UPDATE).
const FK_ACTIONS = ['cascade', 'restrict', 'set null', 'no action'];

const TYPE_LABEL = {
    id: 'bigint', bigInteger: 'bigint', unsignedBigInteger: 'bigint',
    integer: 'int', string: 'varchar', text: 'text', boolean: 'boolean',
    date: 'date', datetime: 'datetime', timestamp: 'timestamp', json: 'json',
    decimal: 'decimal', float: 'float', uuid: 'uuid',
};

// SQL types for the (real) Export SQL feature.
const SQL_TYPE = {
    id: 'BIGINT', bigInteger: 'BIGINT', unsignedBigInteger: 'BIGINT UNSIGNED',
    integer: 'INTEGER', string: 'VARCHAR(255)', text: 'TEXT', boolean: 'TINYINT(1)',
    date: 'DATE', datetime: 'DATETIME', timestamp: 'TIMESTAMP', json: 'JSON',
    decimal: 'DECIMAL(8,2)', float: 'DOUBLE', uuid: 'CHAR(36)',
};

// ---------- color palettes (ported from app.jsx tweaks) ----------
function hexToRgb(h) { const n = parseInt(h.slice(1), 16); return [n >> 16 & 255, n >> 8 & 255, n & 255]; }
function mix(hex, target, t) {
    const a = hexToRgb(hex), b = hexToRgb(target);
    const r = a.map((v, i) => Math.round(v + (b[i] - v) * t));
    return '#' + r.map((v) => v.toString(16).padStart(2, '0')).join('');
}
function colorObj(bar) {
    return { bar, tint: mix(bar, '#ffffff', 0.9), text: mix(bar, '#1a1a1a', 0.32), soft: mix(bar, '#ffffff', 0.8) };
}
const BAR_PALETTES = {
    Vivid: { blue: '#3b82f6', green: '#10b981', purple: '#8b5cf6', amber: '#f59e0b', red: '#ef4444', teal: '#14b8a6', orange: '#f97316', pink: '#ec4899' },
    Pastel: { blue: '#7aa5f5', green: '#5ec79a', purple: '#a892ef', amber: '#f3c06b', red: '#f08989', teal: '#6cc8bd', orange: '#f5a86b', pink: '#f0a0c4' },
    Muted: { blue: '#5b7aa8', green: '#5f9476', purple: '#7e6fa3', amber: '#b89253', red: '#b56a6a', teal: '#5f9690', orange: '#bb8158', pink: '#b07394' },
    Ocean: { blue: '#0ea5e9', green: '#06b6d4', purple: '#6366f1', amber: '#0891b2', red: '#3b82f6', teal: '#14b8a6', orange: '#0d9488', pink: '#8b5cf6' },
};
function paletteMap(name) {
    const bars = BAR_PALETTES[name] || BAR_PALETTES.Vivid;
    const map = {};
    for (const k in bars) map[k] = colorObj(bars[k]);
    return map;
}
const ACCENTS = {
    Indigo: ['#5b5bd6', '#eeeefb'],
    Blue: ['#2563eb', '#e8eefe'],
    Emerald: ['#059669', '#e3f6ee'],
    Violet: ['#7c3aed', '#f1e9fe'],
};

// ---------- ids ----------
let _newId = 1000;
const uid = (p) => p + (_newId++);

function blankColumn(name = 'column', type = 'string') {
    return { id: uid('c'), name, type, nullable: false, pk: false, unique: false, index: false, default: '', fk: null };
}
function blankTable(x, y, color) {
    return {
        id: uid('t'), name: 'new_table', color: color || COLOR_KEYS[Math.floor(Math.random() * 8)],
        x, y,
        columns: [blankColumn('id', 'id'), blankColumn('created_at', 'timestamp'), blankColumn('updated_at', 'timestamp')].map((c, i) => {
            if (i === 0) c.pk = true;
            if (i > 0) c.nullable = true;
            return c;
        }),
        indexes: [],
    };
}

// ---------- geometry ----------
function cardHeight(t) {
    let h = GEO.HEADER_H + t.columns.length * GEO.ROW_H;
    if (t.indexes && t.indexes.length) h += 16 + 14 + t.indexes.length * 16;
    return h;
}
function rowCenterY(t, colIndex) {
    return t.y + GEO.HEADER_H + colIndex * GEO.ROW_H + GEO.ROW_H / 2;
}
function relPath(ax, ay, bx, by, sDir, tDir) {
    const dx = Math.max(46, Math.abs(bx - ax) * 0.45);
    const c1x = ax + sDir * dx, c2x = bx + tDir * dx;
    return `M ${ax} ${ay} C ${c1x} ${ay}, ${c2x} ${by}, ${bx} ${by}`;
}

// ---------- icons (ported from the design's icons.jsx) ----------
const ICON_PATHS = {
    Search: '<circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" />',
    Plus: '<path d="M12 5v14M5 12h14" />',
    ChevronRight: '<path d="m9 6 6 6-6 6" />',
    ChevronDown: '<path d="m6 9 6 6 6-6" />',
    Kebab: '<circle cx="12" cy="5" r="1.4" /><circle cx="12" cy="12" r="1.4" /><circle cx="12" cy="19" r="1.4" />',
    Table: '<rect x="3" y="3" width="18" height="18" rx="2" /><path d="M3 9h18M3 15h18M9 3v18" />',
    Key: '<circle cx="7.5" cy="15.5" r="4.5" /><path d="m10.5 12.5 7-7M16 4l3 3M14 6l3 3" />',
    Link: '<path d="M9 12h6" /><path d="M10 7H7a5 5 0 0 0 0 10h3M14 7h3a5 5 0 0 1 0 10h-3" />',
    Hash: '<path d="M4 9h16M4 15h16M10 3 8 21M16 3l-2 18" />',
    Trash: '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />',
    Copy: '<rect x="9" y="9" width="12" height="12" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />',
    Edit: '<path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />',
    Database: '<ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5" /><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3" />',
    ZoomIn: '<circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3M11 8v6M8 11h6" />',
    ZoomOut: '<circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3M8 11h6" />',
    Fit: '<path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3" />',
    Layout: '<path d="M21 8V5a2 2 0 0 0-2-2h-4M3 8V5a2 2 0 0 1 2-2h4M9 21H5a2 2 0 0 1-2-2v-3M19 21h0a2 2 0 0 0 2-2v-3M12 9v6M9 12h6" />',
    X: '<path d="M18 6 6 18M6 6l12 12" />',
    Check: '<path d="M20 6 9 17l-5-5" />',
    Palette: '<circle cx="13.5" cy="6.5" r="1" fill="currentColor" stroke="none" /><circle cx="17.5" cy="10.5" r="1" fill="currentColor" stroke="none" /><circle cx="8.5" cy="7.5" r="1" fill="currentColor" stroke="none" /><circle cx="6.5" cy="12.5" r="1" fill="currentColor" stroke="none" /><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c1.7 0 2.5-1 2.5-2.2 0-.6-.2-1-.6-1.4-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2H17c2.8 0 5-2.2 5-5 0-4.4-4.5-8-10-8Z" />',
    Grip: '<circle cx="9" cy="6" r="1.3" fill="currentColor" stroke="none" /><circle cx="15" cy="6" r="1.3" fill="currentColor" stroke="none" /><circle cx="9" cy="12" r="1.3" fill="currentColor" stroke="none" /><circle cx="15" cy="12" r="1.3" fill="currentColor" stroke="none" /><circle cx="9" cy="18" r="1.3" fill="currentColor" stroke="none" /><circle cx="15" cy="18" r="1.3" fill="currentColor" stroke="none" />',
    Clock: '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />',
};
function icon(name, opts = {}) {
    const size = opts.size || 16;
    const color = opts.color ? ` style="color:${opts.color}"` : '';
    return `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" `
        + `stroke-width="2" stroke-linecap="round" stroke-linejoin="round"${color}>${ICON_PATHS[name] || ''}</svg>`;
}

// Faux mini-diagram for the dashboard thumbnails (ported from the design's shared.jsx MiniThumb).
function miniThumb(colors, seed = 0) {
    const layouts = [
        [[16, 22, 4], [92, 14, 5], [54, 70, 3], [128, 64, 4]],
        [[20, 16, 5], [104, 30, 4], [40, 74, 4], [120, 80, 3]],
        [[14, 40, 4], [78, 18, 5], [88, 78, 3], [150, 50, 4]],
        [[24, 30, 3], [96, 20, 4], [60, 82, 5], [140, 74, 3]],
        [[18, 24, 4], [86, 60, 4], [120, 18, 5], [44, 78, 3]],
    ];
    const L = layouts[seed % layouts.length];
    const cardW = 46;
    const pal = paletteMap('Vivid');
    const cards = L.map((p, i) => ({ x: p[0], y: p[1], rows: p[2], color: colors[i % colors.length] || 'blue' }));
    const cy = (c) => c.y + 8 + c.rows * 4;
    const conn = (a, b, d) => `<path d="M ${a.x + cardW} ${cy(a)} C ${a.x + cardW + d} ${cy(a)}, ${b.x - d} ${cy(b)}, ${b.x} ${cy(b)}" fill="none" stroke="#d2d2d9" stroke-width="1.4" />`;
    let body = '';
    body += conn(cards[0], cards[1], 24);
    body += conn(cards[2], cards[3], 20);
    cards.forEach((c) => {
        const tc = pal[c.color] || pal.blue;
        const h = 11 + c.rows * 8;
        body += `<g><rect x="${c.x}" y="${c.y}" width="${cardW}" height="${h}" rx="3.5" fill="#fff" stroke="#e6e6ea" stroke-width="1" />`;
        body += `<path d="M ${c.x} ${c.y + 3.5} a3.5 3.5 0 0 1 3.5 -3.5 h${cardW - 7} a3.5 3.5 0 0 1 3.5 3.5 v6 h-${cardW} z" fill="${tc.tint}" />`;
        body += `<rect x="${c.x + 6}" y="${c.y + 4}" width="${cardW - 22}" height="3" rx="1.5" fill="${tc.bar}" opacity="0.85" />`;
        for (let r = 0; r < c.rows; r++) {
            body += `<rect x="${c.x + 6}" y="${c.y + 14 + r * 8}" width="${cardW - 12 - (r % 2) * 8}" height="2.5" rx="1.25" fill="#dadae0" />`;
        }
        body += '</g>';
    });
    return `<svg viewBox="0 0 220 134" width="100%" height="100%" preserveAspectRatio="xMidYMid slice" style="display:block">`
        + `<rect width="220" height="134" fill="#fbfbfc" />${body}</svg>`;
}

// ---------- builder component ----------
function schematicBuilder(initial) {
    return {
        projectName: initial.name || 'Untitled Schema',
        tables: clone(initial.tables || []),

        selectedIds: [],
        editorId: null,
        editorOpenCols: [],
        sidebarCollapsed: false,
        view: { scale: 0.85, tx: 40, ty: 30 },
        expanded: [],
        filter: '',
        renamingId: null,
        sbDragId: null,
        sbOverId: null,

        cardMenu: null,
        sbMenu: null,
        avatarMenu: false,

        tweaks: { tablePalette: 'Vivid', accent: 'Indigo', radius: 10, showGrid: true },
        tweaksOpen: false,
        palette: paletteMap('Vivid'),

        toastMsg: null,
        _toastTimer: null,
        _drag: null,
        dragging: [],
        dirty: false,
        saving: false,

        // relationship drag-to-connect + selection state
        linkDraft: null,
        selectedRelId: null,
        relMenu: null,

        colorKeys: COLOR_KEYS,
        types: LARAVEL_TYPES,
        fkActions: FK_ACTIONS,

        init() {
            this.applyAccent();
            this.applyRadius();
            this.$watch('tweaks.tablePalette', (v) => { this.palette = paletteMap(v); });
            this.$watch('tweaks.accent', () => this.applyAccent());
            this.$watch('tweaks.radius', () => this.applyRadius());
            this.$watch('tables', () => { this.dirty = true; }, { deep: true });
            this.$watch('projectName', () => { this.dirty = true; });
            this.$nextTick(() => { if (this.tables.length) this.fitToScreen(); });
        },

        // ----- tweaks -----
        applyAccent() {
            const a = ACCENTS[this.tweaks.accent] || ACCENTS.Indigo;
            const root = document.documentElement;
            root.style.setProperty('--accent', a[0]);
            root.style.setProperty('--accent-soft', a[1]);
        },
        applyRadius() {
            const r = this.tweaks.radius, root = document.documentElement;
            root.style.setProperty('--r-sm', (r * 0.6) + 'px');
            root.style.setProperty('--r', r + 'px');
            root.style.setProperty('--r-lg', (r * 1.4) + 'px');
        },
        accentColor(name) { return (ACCENTS[name] || ACCENTS.Indigo)[0]; },
        palettePreview(name) { return Object.values(BAR_PALETTES[name]).slice(0, 5); },

        // ----- color helpers -----
        tableColor(t) { return this.palette[t.color] || this.palette.blue; },
        icon,

        // ----- derived -----
        get filteredTables() {
            const f = this.filter.toLowerCase();
            return this.tables.filter((t) => t.name.toLowerCase().includes(f));
        },
        get editorTable() { return this.tables.find((t) => t.id === this.editorId) || null; },
        get columnTotal() { return this.tables.reduce((a, t) => a + t.columns.length, 0); },
        get zoomPct() { return Math.round(this.view.scale * 100); },

        get rels() {
            const byId = Object.fromEntries(this.tables.map((t) => [t.id, t]));
            const W = GEO.CARD_W, out = [];
            this.tables.forEach((src) => {
                src.columns.forEach((c, ci) => {
                    if (!c.fk) return;
                    const tgt = byId[c.fk.table];
                    if (!tgt) return;
                    const ti = tgt.columns.findIndex((x) => x.name === c.fk.column);
                    if (ti < 0) return;
                    const sC = src.x + W / 2, tC = tgt.x + W / 2;
                    const sRight = sC < tC;
                    const sx = src.x + (sRight ? W : 0), sy = rowCenterY(src, ci);
                    const tRight = !sRight;
                    const tx = tgt.x + (tRight ? W : 0), ty = rowCenterY(tgt, ti);
                    const sDir = sRight ? 1 : -1, tDir = tRight ? 1 : -1;
                    const foot = 14, ax = sx + sDir * foot;
                    const bx = tx + tDir * 10, by = ty, barX = tx + tDir * 6;
                    out.push({
                        id: src.id + '.' + c.id, srcId: src.id, tgtId: tgt.id,
                        color: (this.palette[src.color] || this.palette.blue).bar,
                        path: relPath(ax, sy, bx, by, sDir, tDir),
                        footD: `M ${ax} ${sy} L ${sx} ${sy - 6} M ${ax} ${sy} L ${sx} ${sy} M ${ax} ${sy} L ${sx} ${sy + 6}`,
                        sx, sy, tx, bx, by, barX,
                    });
                });
            });
            return out;
        },
        relActive(r) { return this.selectedIds.includes(r.srcId) || this.selectedIds.includes(r.tgtId); },
        relStroke(r) { return this.relActive(r) ? r.color : '#cdcdd4'; },
        relW(r) { return this.relActive(r) ? 2 : 1.5; },

        // Build the relationship layer as one SVG string (x-html keeps children in the SVG namespace).
        relsSvg() {
            return this.rels.map((r) => {
                const sel = this.selectedRelId === r.id;
                const on = this.relActive(r) || sel;
                const stroke = on ? r.color : '#cdcdd4';
                const w = sel ? 2.6 : (on ? 2 : 1.5);
                const cr = on ? 2.4 : 0;
                return `<g opacity="${on ? 1 : 0.85}" style="transition:opacity .15s">`
                    + `<path d="${r.path}" fill="none" stroke="${stroke}" stroke-width="${w}"></path>`
                    + `<path d="${r.footD}" fill="none" stroke="${stroke}" stroke-width="${w}" stroke-linecap="round"></path>`
                    + `<line x1="${r.barX}" y1="${r.by - 6}" x2="${r.barX}" y2="${r.by + 6}" stroke="${stroke}" stroke-width="${w}" stroke-linecap="round"></line>`
                    + `<line x1="${r.tx}" y1="${r.by}" x2="${r.bx}" y2="${r.by}" stroke="${stroke}" stroke-width="${w}"></line>`
                    + `<circle cx="${r.barX}" cy="${r.by}" r="${cr}" fill="${stroke}"></circle>`
                    + '</g>';
            }).join('');
        },

        // Transparent, fat, clickable copies of each line so relationships can be selected.
        relsHitSvg() {
            return this.rels.map((r) =>
                `<path d="${r.path}" data-rel="${r.id}" fill="none" stroke="transparent"`
                + ` stroke-width="12" style="pointer-events:stroke;cursor:pointer"></path>`
            ).join('');
        },

        // Live preview line while dragging a new relationship.
        linkPreviewSvg() {
            const ld = this.linkDraft;
            if (!ld) return '';
            const src = this.tables.find((x) => x.id === ld.srcTableId);
            if (!src) return '';
            const color = (this.palette[src.color] || this.palette.blue).bar;
            const ax = ld.anchor.x, ay = ld.anchor.y;
            let bx = ld.cur.x, by = ld.cur.y, snapped = false, tDir = -1;
            if (ld.hover) {
                const tgt = this.tables.find((x) => x.id === ld.hover.tableId);
                if (tgt) {
                    const sC = src.x + GEO.CARD_W / 2, tC = tgt.x + GEO.CARD_W / 2;
                    const tRight = tC < sC;
                    bx = tgt.x + (tRight ? GEO.CARD_W : 0);
                    by = rowCenterY(tgt, ld.hover.colIndex);
                    tDir = tRight ? 1 : -1;
                    snapped = true;
                }
            }
            const sDir = ld.side === 'right' ? 1 : -1;
            const path = relPath(ax, ay, bx, by, sDir, tDir);
            const dash = snapped ? '' : ' stroke-dasharray="5 5"';
            return `<path d="${path}" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round"${dash} opacity="0.9"></path>`
                + `<circle cx="${ax}" cy="${ay}" r="3.5" fill="${color}"></circle>`
                + (snapped ? `<circle cx="${bx}" cy="${by}" r="4" fill="${color}"></circle>` : '');
        },

        // related-column highlight map for selected tables
        get related() {
            const map = {};
            if (!this.selectedIds.length) return map;
            const byId = Object.fromEntries(this.tables.map((t) => [t.id, t]));
            this.tables.forEach((t) => { map[t.id] = []; });
            this.tables.forEach((src) => src.columns.forEach((c) => {
                if (c.fk && (this.selectedIds.includes(src.id) || this.selectedIds.includes(c.fk.table))) {
                    map[src.id].push(c.id);
                    const tgt = byId[c.fk.table];
                    const tc = tgt && tgt.columns.find((x) => x.name === c.fk.column);
                    if (tc) (map[tgt.id] = map[tgt.id] || []).push(tc.id);
                }
            }));
            return map;
        },
        isSelected(id) { return this.selectedIds.includes(id); },
        isDimmed(t) {
            return this.selectedIds.length > 0 && !this.selectedIds.includes(t.id) && !(this.related[t.id] && this.related[t.id].length);
        },
        isColHl(t, col) { return this.related[t.id] && this.related[t.id].includes(col.id); },
        isExpanded(id) { return this.expanded.includes(id); },

        colIcon(col, c) {
            if (col.pk) return icon('Key', { size: 12, color: c.text });
            if (col.fk) return icon('Link', { size: 12, color: 'var(--muted)' });
            if (col.unique || col.index) return icon('Hash', { size: 11, color: 'var(--faint)' });
            return '<span style="width:4px;height:4px;border-radius:4px;background:var(--faint)"></span>';
        },
        typeLabel(col) { return (TYPE_LABEL[col.type] || col.type) + (col.nullable ? '?' : ''); },
        colIconEditor(col) {
            if (col.pk) return icon('Key', { size: 13, color: 'var(--accent)' });
            if (col.fk) return icon('Link', { size: 13, color: 'var(--muted)' });
            if (col.unique || col.index) return icon('Hash', { size: 12, color: 'var(--faint)' });
            return '<span style="width:5px;height:5px;border-radius:5px;background:var(--faint)"></span>';
        },
        accentNames() { return Object.keys(ACCENTS); },
        sbMenuTable() { return this.sbMenu ? this.tables.find((x) => x.id === this.sbMenu.id) : null; },

        // ----- selection / expand -----
        toggleExpand(id) {
            this.expanded = this.expanded.includes(id) ? this.expanded.filter((x) => x !== id) : [...this.expanded, id];
        },
        selectOnly(id) { this.selectedIds = [id]; this.editorId = id; },
        openEditor(id) { this.editorId = id; this.selectedIds = [id]; },
        closeEditor() { this.editorId = null; },

        // ----- table ops -----
        addTable(world) {
            const w = world && typeof world === 'object'
                ? world
                : { x: (-this.view.tx + 360) / this.view.scale, y: (-this.view.ty + 200) / this.view.scale };
            const nt = blankTable(Math.round(w.x), Math.round(w.y), COLOR_KEYS[this.tables.length % 8]);
            this.tables.push(nt);
            this.selectedIds = [nt.id];
            this.editorId = nt.id;
            this.toast('Table created');
        },
        duplicateTable(id) {
            const src = this.tables.find((x) => x.id === id);
            if (!src) return;
            const copy = clone(src);
            copy.id = uid('t'); copy.name = src.name + '_copy';
            copy.x = src.x + 40; copy.y = src.y + 40;
            copy.columns = copy.columns.map((c) => ({ ...c, id: uid('c') }));
            this.tables.push(copy);
            this.toast('Table duplicated');
        },
        deleteTable(id) {
            // drop foreign keys pointing at the removed table
            this.tables.forEach((t) => t.columns.forEach((c) => { if (c.fk && c.fk.table === id) c.fk = null; }));
            this.tables = this.tables.filter((t) => t.id !== id);
            if (this.editorId === id) this.editorId = null;
            this.selectedIds = this.selectedIds.filter((x) => x !== id);
            this.toast('Table deleted');
        },
        reorder(dragId, targetId) {
            if (!dragId || dragId === targetId) return;
            const arr = [...this.tables];
            const from = arr.findIndex((x) => x.id === dragId);
            const to = arr.findIndex((x) => x.id === targetId);
            if (from < 0 || to < 0) return;
            const [m] = arr.splice(from, 1);
            arr.splice(to, 0, m);
            this.tables = arr;
        },
        colorTable(id, color) { const t = this.tables.find((x) => x.id === id); if (t) t.color = color; },

        // ----- column ops -----
        addColumn(id) {
            const t = this.tables.find((x) => x.id === id);
            if (!t) return;
            t.columns.push(blankColumn('column_' + (t.columns.length + 1)));
            if (!this.expanded.includes(id)) this.expanded = [...this.expanded, id];
        },
        deleteColumn(table, col) { table.columns = table.columns.filter((c) => c.id !== col.id); },
        toggleEditorCol(id) {
            this.editorOpenCols = this.editorOpenCols.includes(id)
                ? this.editorOpenCols.filter((x) => x !== id) : [...this.editorOpenCols, id];
        },
        fkTargets(table) { return this.tables.filter((t) => t.id !== table.id); },

        // ----- relationships (foreign keys) -----
        // A fully-defaulted fk object; onDelete depends on whether the FK column is nullable.
        fkDefaults(col, tableId, column) {
            return {
                table: tableId,
                column,
                type: '1:N',
                onDelete: col.nullable ? 'set null' : 'cascade',
                onUpdate: 'no action',
            };
        },
        // Best target column for a table: its primary key, else 'id', else the first column.
        fkDefaultColumn(tableId) {
            const t = this.tables.find((x) => x.id === tableId);
            if (!t) return 'id';
            const pk = t.columns.find((c) => c.pk);
            if (pk) return pk.name;
            const id = t.columns.find((c) => c.name === 'id');
            return id ? id.name : (t.columns[0] ? t.columns[0].name : 'id');
        },
        fkColumnsFor(tableId) {
            const t = this.tables.find((x) => x.id === tableId);
            return t ? t.columns : [];
        },
        setFkTable(col, tableId) {
            col.fk = tableId ? this.fkDefaults(col, tableId, this.fkDefaultColumn(tableId)) : null;
        },
        // Back-compat alias for any caller that still uses the old single-arg form.
        setFk(col, tableId) { this.setFkTable(col, tableId); },
        setFkColumn(col, column) { if (col.fk) col.fk.column = column; },
        setFkType(col, type) {
            if (!col.fk) return;
            col.fk.type = type;
            if (type === '1:1') col.unique = true;   // 1:1 ⇔ unique FK column
        },
        setFkAction(col, which, value) { if (col.fk) col.fk[which] = value; },
        clearFk(col) { col.fk = null; },

        // Optional N:M sugar: build a pivot table between two tables with two wired FKs.
        createPivot(aId, bId) {
            const a = this.tables.find((t) => t.id === aId);
            const b = this.tables.find((t) => t.id === bId);
            if (!a || !b) return;
            const piv = blankTable(a.x + 220, a.y + 220, COLOR_KEYS[this.tables.length % 8]);
            piv.name = [a.name, b.name].sort().join('_');
            piv.columns = [
                blankColumn('id', 'id'),
                blankColumn(a.name + '_id', 'unsignedBigInteger'),
                blankColumn(b.name + '_id', 'unsignedBigInteger'),
            ].map((c, i) => { c.id = uid('c'); return c; });
            piv.columns[0].pk = true;
            piv.columns[1].index = true;
            piv.columns[2].index = true;
            piv.columns[1].fk = this.fkDefaults(piv.columns[1], aId, this.fkDefaultColumn(aId));
            piv.columns[2].fk = this.fkDefaults(piv.columns[2], bId, this.fkDefaultColumn(bId));
            this.tables.push(piv);
            this.selectedIds = [piv.id];
            this.editorId = piv.id;
            this.toast('Pivot table created');
        },

        autoArrange() {
            const cols = 3, gapX = 320, gapY = 44, x0 = 80, y0 = 80;
            const yCur = [y0, y0, y0];
            this.tables.forEach((t, i) => {
                const c = i % cols;
                t.x = x0 + c * gapX;
                t.y = yCur[c];
                yCur[c] = t.y + cardHeight(t) + gapY;
            });
            this.$nextTick(() => this.fitToScreen());
            this.toast('Layout auto-arranged');
        },

        // ----- canvas view -----
        screenToWorld(cx, cy) {
            const r = this.$refs.wrap.getBoundingClientRect();
            return { x: (cx - r.left - this.view.tx) / this.view.scale, y: (cy - r.top - this.view.ty) / this.view.scale };
        },
        gridStyle() {
            return {
                backgroundImage: 'radial-gradient(circle, #d6d6dd 1.1px, transparent 1.1px)',
                backgroundSize: `${24 * this.view.scale}px ${24 * this.view.scale}px`,
                backgroundPosition: `${this.view.tx}px ${this.view.ty}px`,
            };
        },
        stageStyle() { return { transform: `translate(${this.view.tx}px, ${this.view.ty}px) scale(${this.view.scale})` }; },
        zoom(delta) { this.view = { ...this.view, scale: Math.min(2.2, Math.max(0.25, this.view.scale + delta)) }; },
        onWheel(e) {
            const rect = this.$refs.wrap.getBoundingClientRect();
            const mx = e.clientX - rect.left, my = e.clientY - rect.top;
            let ns = e.ctrlKey || e.metaKey
                ? this.view.scale * (1 - e.deltaY * 0.01)
                : this.view.scale * (1 - e.deltaY * 0.0015);
            ns = Math.min(2.2, Math.max(0.25, ns));
            const k = ns / this.view.scale;
            this.view = { scale: ns, tx: mx - (mx - this.view.tx) * k, ty: my - (my - this.view.ty) * k };
        },
        fitToScreen() {
            const wrap = this.$refs.wrap;
            if (!this.tables.length || !wrap) return;
            const W = GEO.CARD_W;
            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            this.tables.forEach((t) => {
                minX = Math.min(minX, t.x); minY = Math.min(minY, t.y);
                maxX = Math.max(maxX, t.x + W); maxY = Math.max(maxY, t.y + cardHeight(t));
            });
            const pad = 80, cw = wrap.clientWidth, ch = wrap.clientHeight;
            const bw = maxX - minX + pad * 2, bh = maxY - minY + pad * 2;
            const scale = Math.min(1.4, Math.min(cw / bw, ch / bh));
            this.view = {
                scale,
                tx: (cw - (maxX - minX) * scale) / 2 - minX * scale,
                ty: (ch - (maxY - minY) * scale) / 2 - minY * scale,
            };
        },

        // ----- pointer drag (pan + card move) -----
        onBgPointerDown(e) {
            if (e.button !== 0 && e.button !== 1) return;
            if (e.target.closest('.card') || e.target.closest('.canvas-ctrls') || e.target.closest('.canvas-bar') || e.target.closest('.menu') || e.target.closest('[data-rel]')) return;
            this._drag = { mode: 'pan', startX: e.clientX, startY: e.clientY, tx: this.view.tx, ty: this.view.ty, moved: false };
            if (!e.shiftKey) this.selectedIds = [];
            this.selectedRelId = null;
            this.relMenu = null;
        },

        // ----- relationship drag-to-connect -----
        // Begin dragging a connector out of a column's edge handle.
        startLink(e, tableId, colId, side) {
            if (e.button !== 0) return;
            e.stopPropagation();   // don't let the card drag / openEditor fire
            e.preventDefault();
            const t = this.tables.find((x) => x.id === tableId);
            if (!t) return;
            const ci = t.columns.findIndex((c) => c.id === colId);
            if (ci < 0) return;
            const ax = t.x + (side === 'right' ? GEO.CARD_W : 0);
            const ay = rowCenterY(t, ci);
            const w = this.screenToWorld(e.clientX, e.clientY);
            this._drag = { mode: 'link', moved: false, startX: e.clientX, startY: e.clientY };
            this.linkDraft = {
                srcTableId: tableId, srcColId: colId, srcColIndex: ci, side,
                anchor: { x: ax, y: ay }, cur: { x: w.x, y: w.y }, hover: null,
            };
        },
        // World-coords -> { tableId, colIndex } | null (geometric inverse of rowCenterY).
        hitTestColumn(wx, wy) {
            for (let i = this.tables.length - 1; i >= 0; i--) {
                const t = this.tables[i];
                if (wx < t.x || wx > t.x + GEO.CARD_W) continue;
                const rowsTop = t.y + GEO.HEADER_H;
                const rowsBottom = rowsTop + t.columns.length * GEO.ROW_H;
                if (wy < rowsTop || wy >= rowsBottom) continue;
                const ci = Math.floor((wy - rowsTop) / GEO.ROW_H);
                if (ci < 0 || ci >= t.columns.length) continue;
                return { tableId: t.id, colIndex: ci };
            }
            return null;
        },
        // Commit (or cancel) the in-progress link on pointer-up.
        finishLink() {
            const ld = this.linkDraft;
            if (!ld || !ld.hover) return;   // dropped on empty space / header -> cancel
            const src = this.tables.find((x) => x.id === ld.srcTableId);
            const tgt = this.tables.find((x) => x.id === ld.hover.tableId);
            if (!src || !tgt) return;
            const col = src.columns.find((c) => c.id === ld.srcColId);
            const tgtCol = tgt.columns[ld.hover.colIndex];
            if (!col || !tgtCol) return;
            if (tgt.id === src.id) { this.toast('Cannot link a column to its own table'); return; }
            if (col.fk && col.fk.table === tgt.id && col.fk.column === tgtCol.name) {
                this.toast('That relationship already exists'); return;
            }
            col.fk = this.fkDefaults(col, tgt.id, tgtCol.name);
            this.selectedRelId = src.id + '.' + col.id;
            this.selectedIds = [src.id, tgt.id];
            this.toast(tgtCol.pk || tgtCol.unique
                ? 'Relationship created'
                : `Linked — note: ${tgt.name}.${tgtCol.name} is not a key`);
        },
        onCardPointerDown(e, id) {
            if (e.button !== 0) return;
            e.stopPropagation();
            const onHead = !!e.target.closest('.card-head');
            let sel = [...this.selectedIds];
            if (e.shiftKey) sel = sel.includes(id) ? sel.filter((x) => x !== id) : [...sel, id];
            else if (!sel.includes(id)) sel = [id];
            this.selectedIds = sel;
            if (!onHead) { this.openEditor(id); return; }
            const ids = sel.includes(id) ? [...sel] : [id];
            const starts = {};
            ids.forEach((tid) => { const t = this.tables.find((x) => x.id === tid); if (t) starts[tid] = { x: t.x, y: t.y }; });
            this._drag = { mode: 'card', ids, startX: e.clientX, startY: e.clientY, starts, moved: false };
            this.dragging = ids;
        },
        onPointerMove(e) {
            const d = this._drag;
            if (!d) return;
            const dx = e.clientX - d.startX, dy = e.clientY - d.startY;
            if (Math.abs(dx) + Math.abs(dy) > 3) d.moved = true;
            if (d.mode === 'link') {
                const w = this.screenToWorld(e.clientX, e.clientY);
                this.linkDraft.cur = { x: w.x, y: w.y };
                this.linkDraft.hover = this.hitTestColumn(w.x, w.y);
                return;
            }
            if (d.mode === 'pan') { this.view = { ...this.view, tx: d.tx + dx, ty: d.ty + dy }; return; }
            const sc = this.view.scale;
            d.ids.forEach((tid) => {
                const t = this.tables.find((x) => x.id === tid);
                if (t) { t.x = d.starts[tid].x + dx / sc; t.y = d.starts[tid].y + dy / sc; }
            });
        },
        onPointerUp() {
            if (this._drag && this._drag.mode === 'link') {
                this.finishLink();
                this._drag = null;
                this.linkDraft = null;
                return;
            }
            this.dragging = [];
            this._drag = null;
        },
        isDragging(id) { return this.dragging.includes(id); },

        // ----- relationship selection / delete -----
        onRelPointerDown(e) {
            const el = e.target.closest('[data-rel]');
            if (!el) return;
            this.selectRel(el.getAttribute('data-rel'), e);
        },
        selectRel(relId, e) {
            if (e) e.stopPropagation();
            this.selectedRelId = relId;
            const rel = this.rels.find((r) => r.id === relId);
            if (rel) this.selectedIds = [rel.srcId, rel.tgtId];
            if (e) this.relMenu = { x: e.clientX, y: e.clientY, relId };
        },
        deleteRel(relId) {
            if (!relId) return;
            const dot = relId.indexOf('.');
            const srcId = relId.slice(0, dot), colId = relId.slice(dot + 1);
            const src = this.tables.find((x) => x.id === srcId);
            if (src) { const c = src.columns.find((x) => x.id === colId); if (c) c.fk = null; }
            this.selectedRelId = null;
            this.relMenu = null;
            this.toast('Relationship deleted');
        },
        clearRelSelection() { this.selectedRelId = null; this.relMenu = null; },

        // ----- context menus -----
        onContext(e, id) {
            if (id && !this.selectedIds.includes(id)) this.selectedIds = [id];
            this.cardMenu = { x: e.clientX, y: e.clientY, id, world: this.screenToWorld(e.clientX, e.clientY) };
        },
        openSbMenu(e, id) {
            const r = e.currentTarget.getBoundingClientRect();
            this.sbMenu = { id, x: r.right - 188, y: r.bottom + 6 };
        },
        menuStyle(m) {
            const w = 200;
            let left = m.x, top = m.y;
            if (left + w > window.innerWidth - 8) left = window.innerWidth - w - 8;
            if (top + 240 > window.innerHeight - 8) top = Math.max(8, m.y - 240);
            return { left: left + 'px', top: top + 'px' };
        },

        // ----- toast -----
        toast(msg) {
            this.toastMsg = msg;
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => { this.toastMsg = null; }, 2200);
        },

        // ----- persistence + chrome actions -----
        payload() {
            return JSON.parse(JSON.stringify({
                name: this.projectName,
                tables: this.tables.map((t) => ({
                    id: t.id, name: t.name, color: t.color, x: t.x, y: t.y,
                    indexes: t.indexes || [],
                    columns: t.columns.map((c) => ({
                        id: c.id, name: c.name, type: c.type, nullable: !!c.nullable,
                        pk: !!c.pk, unique: !!c.unique, index: !!c.index,
                        default: c.default || '', fk: c.fk || null,
                    })),
                })),
            }));
        },
        async save() {
            if (this.saving) return;
            this.saving = true;
            try {
                await this.$wire.save(this.payload());
                this.dirty = false;
                this.toast('All changes saved');
            } catch (err) {
                this.toast('Save failed — check the console');
                console.error(err);
            } finally {
                this.saving = false;
            }
        },
        share() {
            const url = window.location.href;
            if (navigator.clipboard) navigator.clipboard.writeText(url).catch(() => {});
            this.toast('Share link copied to clipboard');
        },
        _download(text, ext, mime) {
            const blob = new Blob([text], { type: mime });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = (this.projectName || 'schema').replace(/\s+/g, '_').toLowerCase() + ext;
            a.click();
            URL.revokeObjectURL(a.href);
        },
        exportSql() {
            const q = (s) => '`' + s + '`';
            const lines = [];
            this.tables.forEach((t) => {
                lines.push(`CREATE TABLE ${q(t.name)} (`);
                const defs = t.columns.map((c) => {
                    let d = `  ${q(c.name)} ${SQL_TYPE[c.type] || 'VARCHAR(255)'}`;
                    if (c.pk && c.type === 'id') d += ' AUTO_INCREMENT';
                    d += c.nullable ? ' NULL' : ' NOT NULL';
                    if (c.default !== '' && c.default != null) d += ` DEFAULT ${c.default}`;
                    if (c.unique) d += ' UNIQUE';
                    return d;
                });
                const pk = t.columns.filter((c) => c.pk).map((c) => q(c.name));
                if (pk.length) defs.push(`  PRIMARY KEY (${pk.join(', ')})`);
                t.columns.filter((c) => c.fk).forEach((c) => {
                    const tgt = this.tables.find((x) => x.id === c.fk.table);
                    if (!tgt) return;
                    let fk = `  FOREIGN KEY (${q(c.name)}) REFERENCES ${q(tgt.name)} (${q(c.fk.column)})`;
                    const del = (c.fk.onDelete || '').toUpperCase();
                    const upd = (c.fk.onUpdate || '').toUpperCase();
                    if (del && del !== 'NO ACTION') fk += ` ON DELETE ${del}`;
                    if (upd && upd !== 'NO ACTION') fk += ` ON UPDATE ${upd}`;
                    defs.push(fk);
                });
                lines.push(defs.join(',\n'));
                lines.push(');\n');
            });
            this._download(lines.join('\n'), '.sql', 'text/sql');
            this.toast('Exported schema.sql');
        },
        // Laravel migration export — one Schema::create() per table, with constrained() FKs.
        exportMigration() {
            const MIG_TYPE = {
                id: "id('{n}')", bigInteger: "bigInteger('{n}')", unsignedBigInteger: "unsignedBigInteger('{n}')",
                integer: "integer('{n}')", string: "string('{n}')", text: "text('{n}')", boolean: "boolean('{n}')",
                date: "date('{n}')", datetime: "dateTime('{n}')", timestamp: "timestamp('{n}')", json: "json('{n}')",
                decimal: "decimal('{n}')", float: "float('{n}')", uuid: "uuid('{n}')",
            };
            const ON_DELETE = { 'cascade': 'cascadeOnDelete', 'restrict': 'restrictOnDelete', 'set null': 'nullOnDelete' };
            const ON_UPDATE = { 'cascade': 'cascadeOnUpdate', 'restrict': 'restrictOnUpdate' };
            const blocks = this.tables.map((t) => {
                const rows = [];
                t.columns.forEach((c) => {
                    // A FK column is emitted as foreignId()->constrained()..., not as a plain column.
                    if (c.fk) {
                        const tgt = this.tables.find((x) => x.id === c.fk.table);
                        if (tgt) {
                            let line = `            $table->foreignId('${c.name}')`;
                            if (c.nullable) line += '->nullable()';
                            line += `->constrained('${tgt.name}')`;
                            const del = ON_DELETE[(c.fk.onDelete || '').toLowerCase()];
                            const upd = ON_UPDATE[(c.fk.onUpdate || '').toLowerCase()];
                            if (del) line += `->${del}()`;
                            if (upd) line += `->${upd}()`;
                            rows.push(line + ';');
                            return;
                        }
                    }
                    let m = (MIG_TYPE[c.type] || "string('{n}')").replace('{n}', c.name);
                    let line = `            $table->${m}`;
                    if (c.nullable && c.type !== 'id') line += '->nullable()';
                    if (c.unique && !c.pk) line += '->unique()';
                    else if (c.index) line += '->index()';
                    if (c.default !== '' && c.default != null) line += `->default('${c.default}')`;
                    rows.push(line + ';');
                });
                return `        Schema::create('${t.name}', function (Blueprint $table) {\n${rows.join('\n')}\n        });`;
            });
            const body = blocks.join('\n\n');
            const text = `<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n${body}\n    }\n\n    public function down(): void\n    {\n${this.tables.slice().reverse().map((t) => `        Schema::dropIfExists('${t.name}');`).join('\n')}\n    }\n};\n`;
            this._download(text, '_migration.php', 'text/plain');
            this.toast('Exported Laravel migration');
        },
    };
}

// ---------- dashboard component ----------
function schematicDashboard(projects) {
    return {
        projects: projects || [],
        tab: 'All',
        q: '',
        tabs: ['All', 'Recent', 'Shared with me', 'Archived'],
        miniThumb,
        icon,
        get shown() {
            const q = this.q.toLowerCase();
            return this.projects.filter((p) => p.name.toLowerCase().includes(q));
        },
        async newProject() { await this.$wire.newProject(); },
    };
}

function clone(v) { return JSON.parse(JSON.stringify(v)); }

document.addEventListener('alpine:init', () => {
    window.Alpine.data('schematicBuilder', schematicBuilder);
    window.Alpine.data('schematicDashboard', schematicDashboard);
});
