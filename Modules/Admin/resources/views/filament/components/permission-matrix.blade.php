@php
    $catalog = \Modules\Admin\Support\PermissionCatalog::groups();
    $selected = collect($state ?? [])
        ->map(fn ($value) => (int) $value)
        ->filter(fn ($value) => $value > 0)
        ->unique()
        ->values()
        ->all();
    $total = \Modules\Admin\Support\PermissionCatalog::count();
@endphp

<div
    x-data="permissionMatrix({
        groups: {{ Illuminate\Support\Js::from($catalog) }},
        selected: {{ Illuminate\Support\Js::from($selected) }},
    })"
    x-init="collapsed = Object.fromEntries(groups.map((group) => [group.key, false])); persist()"
    data-name="permission-matrix"
    class="grid gap-4"
>
    <div class="sticky top-2 z-10 rounded-xl border border-gray-200 bg-gray-50/95 p-3 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-[200px] flex-1">
                <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
                <input
                    x-model="search"
                    type="search"
                    placeholder="Search modules, resources or actions ..."
                    class="w-full rounded-lg border-0 bg-white py-1.5 pr-4 pl-9 text-sm text-gray-900 shadow-sm ring-1 ring-gray-200 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700"
                >
            </div>
            <div class="rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                <span x-text="selected.length"></span> of {{ $total }} permissions selected
            </div>
            <button type="button" @click="toggleAllCollapse(false)" class="rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-200 hover:text-gray-900 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:text-gray-100">
                Expand all
            </button>
            <button type="button" @click="toggleAllCollapse(true)" class="rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-gray-600 ring-1 ring-gray-200 hover:text-gray-900 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:text-gray-100">
                Collapse all
            </button>
        </div>
    </div>

    <div class="gap-4 xl:columns-2 2xl:columns-3">
        <template x-for="group in groups" :key="group.key">
            <div
                x-show="visibleGroup(group)"
                x-transition
                class="mb-4 break-inside-avoid overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
            >
                <div class="flex items-center justify-between gap-2 border-b border-gray-200 bg-gray-100/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/80">
                    <button
                        type="button"
                        @click="toggleCollapse(group.key)"
                        class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-left"
                    >
                        <svg
                            class="h-4 w-4 shrink-0 text-gray-400 transition-transform"
                            :class="isCollapsed(group.key) ? '-rotate-90' : ''"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                        <span x-text="group.label" class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100"></span>
                        <span class="shrink-0 rounded-full bg-gray-200 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <span x-text="selectedInGroup(group).length"></span>/<span x-text="group.ids.length"></span>
                        </span>
                    </button>
                    <label class="flex shrink-0 cursor-pointer items-center gap-1.5 text-[11px] font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">
                        <input
                            type="checkbox"
                            :checked="groupAllChecked(group)"
                            :indeterminate.prop="groupIndeterminate(group)"
                            @change="toggleGroup(group)"
                            class="h-3.5 w-3.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                        >
                        <span>All</span>
                    </label>
                </div>

                <div
                    x-show="!isCollapsed(group.key)"
                    class="grid gap-3 bg-white p-3 dark:bg-gray-950"
                >
                    <template x-for="resource in group.resources" :key="resource.name">
                        <div x-show="visibleResource(resource)">
                            <div class="mb-1.5 text-[10px] font-semibold tracking-wide text-gray-400 uppercase dark:text-gray-500" x-text="resource.name"></div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="permission in resource.permissions" :key="permission.id">
                                    <label
                                        class="flex cursor-pointer items-center gap-1 rounded-full px-2 py-0.5 text-[11px] leading-4 ring-1 hover:opacity-85"
                                        :class="chipClass(permission.kind, selected.includes(normalized(permission.id)))"
                                        x-bind:title="permission.label + ' (' + permission.name + ')'"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="permission.id"
                                            x-model="selected"
                                            @change="persist()"
                                            class="h-3 w-3 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                        >
                                        <span x-text="permission.action"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <div x-show="visibleGroupsCount() === 0" class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
        No permissions match your search.
    </div>

    <script>
        function permissionMatrix({ groups, selected }) {
            return {
                groups,
                selected,
                search: '',
                collapsed: {},

                normalized: (value) => parseInt(value, 10),

                visibleGroup(group) {
                    const query = this.search.trim().toLowerCase();
                    if (!query) {
                        return true;
                    }

                    return group.label.toLowerCase().includes(query)
                        || group.resources.some((resource) => this.resourceVisible(resource, query));
                },

                resourceVisible(resource, query = this.search.trim().toLowerCase()) {
                    if (!query) {
                        return true;
                    }

                    if (resource.name.toLowerCase().includes(query)) {
                        return true;
                    }

                    return resource.permissions.some((permission) =>
                        permission.name.toLowerCase().includes(query)
                        || permission.action.toLowerCase().includes(query)
                    );
                },

                visibleResource(resource) {
                    return this.resourceVisible(resource);
                },

                visibleGroupsCount() {
                    return this.groups.filter((group) => this.visibleGroup(group)).length;
                },

                isCollapsed(key) {
                    return Boolean(this.collapsed[key]);
                },

                toggleCollapse(key) {
                    this.collapsed[key] = !this.collapsed[key];
                },

                toggleAllCollapse(collapsed) {
                    Object.keys(this.collapsed).forEach((key) => {
                        this.collapsed[key] = collapsed;
                    });
                },

                selectedInGroup(group) {
                    return group.ids.filter((id) => this.selected.includes(this.normalized(id)));
                },

                groupAllChecked(group) {
                    return group.ids.length > 0
                        && group.ids.every((id) => this.selected.includes(this.normalized(id)));
                },

                groupIndeterminate(group) {
                    const selectedCount = this.selectedInGroup(group).length;
                    return selectedCount > 0 && selectedCount < group.ids.length;
                },

                toggleGroup(group) {
                    const ids = group.ids.map((id) => this.normalized(id));
                    const allSelected = ids.every((id) => this.selected.includes(id));

                    if (allSelected) {
                        this.selected = this.selected.filter((id) => !ids.includes(this.normalized(id)));
                    } else {
                        this.selected = Array.from(new Set([...this.selected, ...ids]));
                    }

                    this.persist();
                },

                chipClass(kind, checked) {
                    const palettes = {
                        crud: {
                            off: 'bg-gray-100 text-gray-600 ring-gray-200 hover:ring-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
                            on: 'bg-primary-50 text-primary-700 ring-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/30',
                        },
                        manage: {
                            off: 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:ring-purple-500/30',
                            on: 'bg-purple-100 text-purple-800 ring-purple-300 dark:bg-purple-500/20 dark:text-purple-300 dark:ring-purple-500/40',
                        },
                        access: {
                            off: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
                            on: 'bg-amber-100 text-amber-800 ring-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:ring-amber-500/40',
                        },
                        action: {
                            off: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30',
                            on: 'bg-blue-100 text-blue-800 ring-blue-300 dark:bg-blue-500/20 dark:text-blue-300 dark:ring-blue-500/40',
                        },
                        legacy: {
                            off: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
                            on: 'bg-emerald-100 text-emerald-800 ring-emerald-300 dark:bg-emerald-500/20 dark:text-emerald-300 dark:ring-emerald-500/40',
                        },
                    };

                    const palette = palettes[kind] ?? palettes.legacy;
                    return palette[checked ? 'on' : 'off'];
                },

                persist() {
                    const cleaned = Array.from(
                        new Set(this.selected.map((id) => this.normalized(id)))
                    ).filter((id) => Number.isInteger(id) && id > 0).sort((a, b) => a - b);

                    this.selected = cleaned;
                    this.$wire.set('data.permissions', cleaned, false);
                },
            };
        }
    </script>
</div>