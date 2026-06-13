@php $user = auth()->user(); $demo = $demo ?? false; @endphp

<div class="app" x-data="schematicBuilder(@js($this->schema))" x-cloak
     @keydown.delete.window="if (selectedRelId && !/^(INPUT|TEXTAREA|SELECT)$/.test($event.target.tagName) && !$event.target.isContentEditable) deleteRel(selectedRelId)"
     @keydown.backspace.window="if (selectedRelId && !/^(INPUT|TEXTAREA|SELECT)$/.test($event.target.tagName) && !$event.target.isContentEditable) deleteRel(selectedRelId)">
    {{-- ───────── Navbar ───────── --}}
    <div class="nav">
        <div class="nav-brand">
            <div class="nav-logo" x-html="icon('Database', { size: 16 })"></div>
            <span class="nav-wordmark">Schematic</span>
        </div>
        <div class="nav-divider"></div>
        @unless($demo)
            <a href="{{ route('schemas.index') }}" wire:navigate class="proj-crumb btn btn-ghost" style="padding: 5px 8px; height: 30px;">Projects</a>
            <span x-html="icon('ChevronRight', { size: 14 })" style="color: var(--faint); display:flex;"></span>
        @else
            <span class="proj-crumb" style="font-size: 12px; font-weight: 600; color: var(--accent); background: var(--accent-tint, rgba(99,102,241,.12)); padding: 4px 9px; border-radius: 6px;">Demo</span>
        @endunless
        <input class="proj-name" x-model="projectName" spellcheck="false" @keydown.enter="$event.target.blur()" />

        <div class="nav-spacer"></div>

        <button class="btn btn-icon btn-ghost" title="Notifications" x-html="icon('Bell', { size: 16 })"></button>
        <button class="btn" @click="share()">
            <span x-html="icon('Share', { size: 15 })" style="display:flex"></span> Share
        </button>
        <button class="btn" @click="exportSql()">
            <span x-html="icon('Download', { size: 15 })" style="display:flex"></span> Export SQL
        </button>
        <button class="btn" @click="exportMigration()" title="Export a Laravel migration">
            <span x-html="icon('Download', { size: 15 })" style="display:flex"></span> Export migration
        </button>
        @unless($demo)
            <button class="btn btn-primary" @click="save()" :disabled="saving">
                <span x-html="icon('Save', { size: 15 })" style="display:flex"></span>
                <span x-text="saving ? 'Saving…' : 'Save'"></span>
            </button>
        @else
            <a class="btn btn-primary" href="{{ route('register') }}">
                <span x-html="icon('Save', { size: 15 })" style="display:flex"></span> Sign up to save
            </a>
        @endunless

        <div class="nav-divider"></div>
        @if($demo)
            <a class="btn btn-ghost" href="{{ route('login') }}">Sign in</a>
        @else
        <div style="position: relative;">
            <div class="avatar" @click="avatarMenu = !avatarMenu" title="{{ $user->name }}">{{ $user->initials() }}</div>
            <template x-if="avatarMenu">
                <div class="menu" style="position: absolute; right: 0; top: 40px; width: 210px;"
                     @click.outside="avatarMenu = false" @keydown.escape.window="avatarMenu = false">
                    <div style="padding: 8px 10px 6px;">
                        <div style="font-size: 13px; font-weight: 600;">{{ $user->name }}</div>
                        <div style="font-size: 11.5px; color: var(--muted);">{{ $user->email }}</div>
                    </div>
                    <div class="menu-sep"></div>
                    <a class="menu-item" href="{{ route('profile.edit') }}" wire:navigate @click="avatarMenu = false">
                        <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span><span>Settings</span>
                    </a>
                    <a class="menu-item" href="{{ route('schemas.index') }}" wire:navigate @click="avatarMenu = false">
                        <span x-html="icon('Layout', { size: 15 })" style="display:flex"></span><span>All schemas</span>
                    </a>
                    <div class="menu-sep"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="menu-item danger" style="width:100%">
                            <span x-html="icon('X', { size: 15 })" style="display:flex"></span><span>Sign out</span>
                        </button>
                    </form>
                </div>
            </template>
        </div>
        @endif
    </div>

    {{-- ───────── Body ───────── --}}
    <div class="body-row">
        {{-- Sidebar --}}
        <div class="sidebar" :class="{ collapsed: sidebarCollapsed }">
            <div class="sb-head">
                <div class="sb-title-row">
                    <span class="sb-title">Tables <span class="sb-count" x-text="'· ' + tables.length"></span></span>
                    <button class="add-table-btn" @click="addTable()">
                        <span x-html="icon('Table', { size: 13 })" style="display:flex"></span> Add Table
                    </button>
                </div>
                <div class="search">
                    <span x-html="icon('Search', { size: 15 })" style="display:flex"></span>
                    <input placeholder="Filter" x-model="filter" />
                    <button x-show="filter" @click="filter = ''" style="color: var(--faint); display: flex;"
                            x-html="icon('X', { size: 14 })"></button>
                </div>
            </div>

            <div class="sb-list">
                <template x-if="!filteredTables.length">
                    <div style="text-align: center; color: var(--faint); font-size: 12.5px; padding: 28px 12px;">
                        No tables match “<span x-text="filter"></span>”
                    </div>
                </template>

                <template x-for="t in filteredTables" :key="t.id">
                    <div class="tbl-item" :class="{ active: selectedIds[0] === t.id, 'drag-over': sbOverId === t.id }"
                         @dragover.prevent="if (sbDragId) sbOverId = t.id"
                         @drop="reorder(sbDragId, t.id); sbDragId = null; sbOverId = null">
                        <div class="tbl-row" @click="selectOnly(t.id)" :style="sbDragId === t.id ? 'opacity:.4' : ''">
                            <span class="grip" draggable="true"
                                  @dragstart="sbDragId = t.id; $event.dataTransfer.effectAllowed = 'move'"
                                  @dragend="sbDragId = null; sbOverId = null"
                                  @click.stop x-html="icon('Grip', { size: 15 })"></span>
                            <span class="tbl-accent" :style="'background:' + tableColor(t).bar"></span>
                            <button class="chev" :style="isExpanded(t.id) ? 'transform: rotate(90deg)' : ''"
                                    @click.stop="toggleExpand(t.id)" x-html="icon('ChevronRight', { size: 15 })"></button>
                            <template x-if="renamingId === t.id">
                                <input class="proj-name" style="flex: 1; height: 24px; padding: 2px 6px; font-family: var(--mono); font-size: 12px;"
                                       x-model="t.name" x-init="$nextTick(() => $el.focus())" @click.stop
                                       @blur="renamingId = null" @keydown.enter="$event.target.blur()" @keydown.escape="renamingId = null" />
                            </template>
                            <template x-if="renamingId !== t.id">
                                <span class="tbl-name" style="font-family: var(--mono); font-size: 12.5px;" x-text="t.name"></span>
                            </template>
                            <span class="tbl-meta" x-text="t.columns.length"></span>
                            <button class="kebab" @click.stop="openSbMenu($event, t.id)" x-html="icon('Kebab', { size: 15 })"></button>
                        </div>

                        <template x-if="isExpanded(t.id)">
                            <div class="cols">
                                <template x-for="col in t.columns" :key="col.id">
                                    <div class="col-row">
                                        <span class="col-ic" x-html="colIcon(col, tableColor(t))"></span>
                                        <span class="col-name" style="font-family: var(--mono);" x-text="col.name"></span>
                                        <span class="col-type" x-text="typeLabel(col)"></span>
                                    </div>
                                </template>
                                <button class="add-col-row" @click="addColumn(t.id)">
                                    <span x-html="icon('Plus', { size: 13 })" style="display:flex"></span> Add column
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Sidebar collapse tab --}}
        <button class="sb-toggle" :style="'left:' + (sidebarCollapsed ? '12px' : 'calc(var(--sidebar-w) - 13px)')"
                @click="sidebarCollapsed = !sidebarCollapsed" :title="sidebarCollapsed ? 'Show tables' : 'Hide tables'">
            <span style="display:flex; transition: transform .2s;" :style="sidebarCollapsed ? '' : 'transform: rotate(180deg)'"
                  x-html="icon('ChevronRight', { size: 15 })"></span>
        </button>

        {{-- Canvas --}}
        <div class="canvas-wrap" x-ref="wrap"
             @pointerdown="onBgPointerDown($event)"
             @pointermove.window="onPointerMove($event)"
             @pointerup.window="onPointerUp()"
             @wheel.prevent="onWheel($event)"
             @contextmenu.prevent="if (!$event.target.closest('.card')) onContext($event, null)"
             :style="_drag && _drag.mode === 'pan' ? 'cursor: grabbing' : (_drag && _drag.mode === 'link' ? 'cursor: crosshair' : '')">

            <div class="canvas-grid" x-show="tweaks.showGrid" :style="gridStyle()"></div>

            <template x-if="!tables.length">
                <div style="position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none;">
                    <div style="text-align: center; pointer-events: auto;">
                        <div style="width: 96px; height: 96px; margin: 0 auto 22px; border-radius: 24px; display: grid; place-items: center; background: var(--surface); border: 1px solid var(--border-strong); box-shadow: var(--shadow-card); color: var(--accent);"
                             x-html="icon('Database', { size: 40 })"></div>
                        <div style="font-size: 18px; font-weight: 660; letter-spacing: -.02em;">This canvas is empty</div>
                        <div style="font-size: 13.5px; color: var(--muted); margin: 6px 0 20px; max-width: 280px;">Add your first table to start designing the schema for your Laravel app.</div>
                        <button class="btn btn-primary" style="height: 38px; padding: 0 18px; margin: 0 auto;" @click="addTable({ x: 220, y: 150 })">
                            <span x-html="icon('Plus', { size: 16 })" style="display:flex"></span> Create your first table
                        </button>
                    </div>
                </div>
            </template>

            <div class="canvas-stage" :style="stageStyle()">
                <svg class="lines-svg" width="1" height="1" x-html="relsSvg()"></svg>

                <template x-for="t in tables" :key="t.id">
                    <div class="card" :class="{ selected: isSelected(t.id), dim: isDimmed(t), dragging: isDragging(t.id) }"
                         :style="'transform: translate(' + t.x + 'px, ' + t.y + 'px)'"
                         @pointerdown="onCardPointerDown($event, t.id)"
                         @contextmenu.prevent.stop="onContext($event, t.id)">
                        <div class="card-head" :style="'background:' + tableColor(t).tint + '; color:' + tableColor(t).text"
                             @dblclick="openEditor(t.id)">
                            <span class="card-head-ic" x-html="icon('Table', { size: 14 })"></span>
                            <span class="card-title" x-text="t.name"></span>
                        </div>
                        <template x-for="col in t.columns" :key="col.id">
                            <div class="card-row" :class="{ hl: isColHl(t, col), 'link-target': linkDraft && linkDraft.hover && linkDraft.hover.tableId === t.id && t.columns[linkDraft.hover.colIndex] === col }">
                                <span class="link-handle link-handle-l" title="Drag to connect"
                                      @pointerdown="startLink($event, t.id, col.id, 'left')"></span>
                                <span class="card-row-ic" x-html="colIcon(col, tableColor(t))"></span>
                                <span class="card-col-name"><span :class="{ 'pk-name': col.pk }" x-text="col.name"></span></span>
                                <span class="card-type" x-text="typeLabel(col)"></span>
                                <span class="link-handle link-handle-r" title="Drag to connect"
                                      @pointerdown="startLink($event, t.id, col.id, 'right')"></span>
                            </div>
                        </template>
                        <template x-if="t.indexes && t.indexes.length">
                            <div class="card-idx">
                                <div class="card-idx-head"><span x-html="icon('Hash', { size: 9 })" style="display:flex"></span> Indexes</div>
                                <template x-for="(ix, i) in t.indexes" :key="i">
                                    <div class="card-idx-item" x-text="ix"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- live preview while drawing a new relationship --}}
                <svg class="link-preview-svg" width="1" height="1" x-show="linkDraft" x-html="linkPreviewSvg()"></svg>
                {{-- transparent fat copies of each line so relationships are clickable --}}
                <svg class="rels-hit-svg" width="1" height="1" x-html="relsHitSvg()" @pointerdown="onRelPointerDown($event)"></svg>
            </div>

            {{-- canvas info bar --}}
            <div class="canvas-bar">
                <span class="cb-chip">
                    <span x-html="icon('Database', { size: 13 })" style="display:flex"></span>
                    <span x-text="tables.length + ' tables'"></span>
                </span>
                <span class="cb-chip" style="color: var(--faint);"><span x-text="'· ' + columnTotal + ' columns'"></span></span>
            </div>

            {{-- floating zoom controls --}}
            <div class="canvas-ctrls">
                <div class="zoom-label" x-text="zoomPct + '%'"></div>
                <button class="cc-btn" title="Zoom in" @click="zoom(0.15)" x-html="icon('ZoomIn', { size: 17 })"></button>
                <div class="cc-sep"></div>
                <button class="cc-btn" title="Zoom out" @click="zoom(-0.15)" x-html="icon('ZoomOut', { size: 17 })"></button>
                <div class="cc-sep"></div>
                <button class="cc-btn" title="Fit to screen" @click="fitToScreen()" x-html="icon('Fit', { size: 17 })"></button>
                <div class="cc-sep"></div>
                <button class="cc-btn" title="Auto-arrange" @click="autoArrange()" x-html="icon('Layout', { size: 17 })"></button>
            </div>

            {{-- canvas / card context menu --}}
            <template x-if="cardMenu">
                <div class="menu" :style="menuStyle(cardMenu)" @click.outside="cardMenu = null" @keydown.escape.window="cardMenu = null">
                    <template x-if="cardMenu.id">
                        <div>
                            <button class="menu-item" @click="openEditor(cardMenu.id); cardMenu = null">
                                <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span><span>Edit columns</span>
                            </button>
                            <button class="menu-item" @click="duplicateTable(cardMenu.id); cardMenu = null">
                                <span x-html="icon('Copy', { size: 15 })" style="display:flex"></span><span>Duplicate</span>
                            </button>
                            <template x-if="selectedIds.length === 2">
                                <button class="menu-item" @click="createPivot(selectedIds[0], selectedIds[1]); cardMenu = null">
                                    <span x-html="icon('Link', { size: 15 })" style="display:flex"></span><span>Create pivot (N:M)</span>
                                </button>
                            </template>
                            <div class="menu-sep"></div>
                            <button class="menu-item danger" @click="deleteTable(cardMenu.id); cardMenu = null">
                                <span x-html="icon('Trash', { size: 15 })" style="display:flex"></span><span>Delete table</span>
                            </button>
                        </div>
                    </template>
                    <template x-if="!cardMenu.id">
                        <div>
                            <button class="menu-item" @click="addTable(cardMenu.world); cardMenu = null">
                                <span x-html="icon('Plus', { size: 15 })" style="display:flex"></span><span>New table here</span>
                            </button>
                            <button class="menu-item" @click="autoArrange(); cardMenu = null">
                                <span x-html="icon('Layout', { size: 15 })" style="display:flex"></span><span>Auto-arrange</span>
                            </button>
                            <button class="menu-item" @click="fitToScreen(); cardMenu = null">
                                <span x-html="icon('Fit', { size: 15 })" style="display:flex"></span><span>Fit to screen</span>
                            </button>
                            <div class="menu-sep"></div>
                            <button class="menu-item" @click="selectedIds = tables.map((t) => t.id); cardMenu = null">
                                <span x-html="icon('Check', { size: 15 })" style="display:flex"></span><span>Select all</span>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- relationship line popover --}}
            <template x-if="relMenu">
                <div class="menu" :style="menuStyle(relMenu)" @pointerdown.outside="relMenu = null" @keydown.escape.window="relMenu = null">
                    <button class="menu-item danger" @click="deleteRel(relMenu.relId)">
                        <span x-html="icon('Trash', { size: 15 })" style="display:flex"></span><span>Delete relationship</span>
                    </button>
                </div>
            </template>
        </div>

        {{-- Editor panel --}}
        <template x-if="editorTable">
            <div class="editor" :key="editorId">
                <div class="ed-head">
                    <span class="ed-dot" :style="'background:' + tableColor(editorTable).bar"></span>
                    <div style="flex: 1; min-width: 0;">
                        <div class="ed-title" style="font-family: var(--mono);" x-text="editorTable.name"></div>
                        <div class="ed-sub" x-text="editorTable.columns.length + ' columns · ' + editorTable.indexes.length + (editorTable.indexes.length === 1 ? ' index' : ' indexes')"></div>
                    </div>
                    <button class="ed-close" @click="closeEditor()" x-html="icon('X', { size: 18 })"></button>
                </div>
                <div class="ed-body">
                    <div class="ed-section-label">Table</div>
                    <div class="field">
                        <span class="field-label">Name</span>
                        <input class="input mono" x-model="editorTable.name" />
                    </div>

                    <div class="ed-section-label">
                        <span style="display: inline-flex; align-items: center; gap: 6px;">
                            <span x-html="icon('Palette', { size: 13 })" style="display:flex"></span> Color
                        </span>
                    </div>
                    <div class="swatches">
                        <template x-for="k in colorKeys" :key="k">
                            <button class="swatch" :class="{ sel: editorTable.color === k }"
                                    :style="'background:' + (palette[k] || palette.blue).bar" @click="colorTable(editorId, k)"></button>
                        </template>
                    </div>

                    <div class="ed-section-label">
                        <span>Columns</span>
                        <span style="color: var(--faint); font-weight: 500; text-transform: none; letter-spacing: 0;" x-text="editorTable.columns.length"></span>
                    </div>

                    <template x-for="col in editorTable.columns" :key="col.id">
                        <div class="col-card" :class="{ open: editorOpenCols.includes(col.id) }">
                            <div class="col-card-head" @click="toggleEditorCol(col.id)">
                                <span style="color: var(--muted); display: flex;"
                                      x-html="editorOpenCols.includes(col.id) ? icon('ChevronDown', { size: 15 }) : icon('ChevronRight', { size: 15 })"></span>
                                <span style="display:flex" x-html="colIconEditor(col)"></span>
                                <span style="font-family: var(--mono); font-size: 12.5px; font-weight: 540; flex: 1;" x-text="col.name"></span>
                                <span class="col-badge" x-text="typeLabel(col)"></span>
                            </div>
                            <template x-if="editorOpenCols.includes(col.id)">
                                <div class="col-card-body">
                                    <div class="field-row">
                                        <div class="field"><span class="field-label">Name</span><input class="input mono" x-model="col.name" /></div>
                                        <div class="field">
                                            <span class="field-label">Type</span>
                                            <select class="select" x-model="col.type">
                                                <template x-for="ty in types" :key="ty"><option :value="ty" x-text="ty"></option></template>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="field"><span class="field-label">Default value</span><input class="input mono" placeholder="—" x-model="col.default" /></div>
                                    <div style="border-top: 1px solid var(--border); padding-top: 2px;">
                                        <div class="toggle-row">
                                            <div class="toggle-meta"><div><div class="toggle-name">Nullable</div><div class="toggle-desc">Allow NULL values</div></div></div>
                                            <button class="switch" :class="{ on: col.nullable }" @click="col.nullable = !col.nullable"></button>
                                        </div>
                                        <div class="toggle-row">
                                            <div class="toggle-meta"><div><div class="toggle-name">Unique</div><div class="toggle-desc">Add a unique constraint</div></div></div>
                                            <button class="switch" :class="{ on: col.unique }" @click="col.unique = !col.unique"></button>
                                        </div>
                                        <div class="toggle-row">
                                            <div class="toggle-meta"><div><div class="toggle-name">Index</div><div class="toggle-desc">Create a database index</div></div></div>
                                            <button class="switch" :class="{ on: col.index }" @click="col.index = !col.index"></button>
                                        </div>
                                        <div class="toggle-row">
                                            <div class="toggle-meta"><div><div class="toggle-name">Primary key</div></div></div>
                                            <button class="switch" :class="{ on: col.pk }" @click="col.pk = !col.pk"></button>
                                        </div>
                                    </div>
                                    <div style="border-top: 1px solid var(--border); padding-top: 10px;">
                                        <div class="field">
                                            <span class="field-label">Relationship</span>
                                            <select class="select" :value="col.fk ? col.fk.table : ''" @change="setFkTable(col, $event.target.value)">
                                                <option value="">No relationship</option>
                                                <template x-for="ft in fkTargets(editorTable)" :key="ft.id"><option :value="ft.id" x-text="ft.name"></option></template>
                                            </select>
                                        </div>
                                        <template x-if="col.fk">
                                            <div>
                                                <div class="field">
                                                    <span class="field-label">References column</span>
                                                    <select class="select" :value="col.fk.column" @change="setFkColumn(col, $event.target.value)">
                                                        <template x-for="rc in fkColumnsFor(col.fk.table)" :key="rc.id"><option :value="rc.name" x-text="rc.name + (rc.pk ? ' (pk)' : '')"></option></template>
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <span class="field-label">Type</span>
                                                    <div class="seg">
                                                        <button type="button" class="seg-btn" :class="{ on: col.fk.type === '1:N' }" @click="setFkType(col, '1:N')">1 : N</button>
                                                        <button type="button" class="seg-btn" :class="{ on: col.fk.type === '1:1' }" @click="setFkType(col, '1:1')">1 : 1</button>
                                                    </div>
                                                </div>
                                                <div class="field-row">
                                                    <div class="field">
                                                        <span class="field-label">On delete</span>
                                                        <select class="select" :value="col.fk.onDelete" @change="setFkAction(col, 'onDelete', $event.target.value)">
                                                            <template x-for="a in fkActions" :key="a"><option :value="a" x-text="a"></option></template>
                                                        </select>
                                                    </div>
                                                    <div class="field">
                                                        <span class="field-label">On update</span>
                                                        <select class="select" :value="col.fk.onUpdate" @change="setFkAction(col, 'onUpdate', $event.target.value)">
                                                            <template x-for="a in fkActions" :key="a"><option :value="a" x-text="a"></option></template>
                                                        </select>
                                                    </div>
                                                </div>
                                                <button class="del-table" style="height: 28px;" @click="clearFk(col)">
                                                    <span x-html="icon('X', { size: 12 })" style="display:flex"></span> Remove relationship
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button class="del-table" style="height: 30px;" @click="deleteColumn(editorTable, col)">
                                        <span x-html="icon('Trash', { size: 13 })" style="display:flex"></span> Remove column
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <button class="ed-add-col" @click="addColumn(editorId)">
                        <span x-html="icon('Plus', { size: 15 })" style="display:flex"></span> Add column
                    </button>

                    <div style="margin-top: 22px; border-top: 1px solid var(--border); padding-top: 14px;">
                        <button class="del-table" @click="deleteTable(editorId)">
                            <span x-html="icon('Trash', { size: 14 })" style="display:flex"></span> Delete table
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ───────── Sidebar kebab menu ───────── --}}
    <template x-if="sbMenu">
        <div class="menu" :style="menuStyle(sbMenu)" @click.outside="sbMenu = null" @keydown.escape.window="sbMenu = null">
            <button class="menu-item" @click="renamingId = sbMenu.id; sbMenu = null">
                <span x-html="icon('Edit', { size: 15 })" style="display:flex"></span><span>Rename</span>
            </button>
            <button class="menu-item" @click="duplicateTable(sbMenu.id); sbMenu = null">
                <span x-html="icon('Copy', { size: 15 })" style="display:flex"></span><span>Duplicate</span>
            </button>
            <div class="menu-sep"></div>
            <div style="font-size: 11px; font-weight: 600; color: var(--muted); padding: 2px 9px 0;">Color</div>
            <div class="menu-swatches">
                <template x-for="k in colorKeys" :key="k">
                    <button class="swatch" :class="{ sel: sbMenuTable() && sbMenuTable().color === k }"
                            :style="'background:' + (palette[k] || palette.blue).bar" @click="colorTable(sbMenu.id, k); sbMenu = null"></button>
                </template>
            </div>
            <div class="menu-sep"></div>
            <button class="menu-item danger" @click="deleteTable(sbMenu.id); sbMenu = null">
                <span x-html="icon('Trash', { size: 15 })" style="display:flex"></span><span>Delete</span>
            </button>
        </div>
    </template>

    {{-- ───────── Tweaks panel ───────── --}}
    <div style="position: fixed; left: 18px; bottom: 18px; z-index: 60;" @keydown.escape.window="tweaksOpen = false">
        <button class="btn btn-icon" style="box-shadow: var(--shadow-card); height: 38px; width: 38px;"
                @click="tweaksOpen = !tweaksOpen" title="Appearance" x-html="icon('Palette', { size: 16 })"></button>
        <template x-if="tweaksOpen">
            <div class="menu" style="position: absolute; left: 0; bottom: 46px; width: 246px; padding: 12px;"
                 @click.outside="tweaksOpen = false">
                <div class="sb-title" style="margin-bottom: 8px;">Table colors</div>
                <div class="field" style="margin-bottom: 12px;">
                    <span class="field-label">Palette</span>
                    <select class="select" x-model="tweaks.tablePalette">
                        <template x-for="p in ['Vivid', 'Pastel', 'Muted', 'Ocean']" :key="p"><option :value="p" x-text="p"></option></template>
                    </select>
                </div>
                <div class="sb-title" style="margin: 14px 0 8px;">Interface</div>
                <div class="field" style="margin-bottom: 12px;">
                    <span class="field-label">Accent</span>
                    <div class="swatches">
                        <template x-for="name in accentNames()" :key="name">
                            <button class="swatch" :class="{ sel: tweaks.accent === name }"
                                    :style="'background:' + accentColor(name)" @click="tweaks.accent = name"></button>
                        </template>
                    </div>
                </div>
                <div class="field" style="margin-bottom: 6px;">
                    <span class="field-label">Corner radius <span style="color: var(--muted);" x-text="tweaks.radius + 'px'"></span></span>
                    <input type="range" min="2" max="18" step="1" x-model.number="tweaks.radius" style="width: 100%; accent-color: var(--accent);" />
                </div>
                <div class="toggle-row" style="border-top: 1px solid var(--border);">
                    <div class="toggle-name">Canvas grid</div>
                    <button class="switch" :class="{ on: tweaks.showGrid }" @click="tweaks.showGrid = !tweaks.showGrid"></button>
                </div>
            </div>
        </template>
    </div>

    {{-- ───────── Toast ───────── --}}
    <template x-if="toastMsg">
        <div class="toast">
            <span class="tk" x-html="icon('Check', { size: 15 })" style="display:flex"></span>
            <span x-text="toastMsg"></span>
        </div>
    </template>
</div>
