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
    x-init="persist()"
    data-name="permission-matrix"
    class="grid gap-5"
>
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[220px] flex-1">
                <svg class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
                <input
                    x-model="search"
                    type="search"
                    placeholder="Search modules, resources or actions ..."
                    class="w-full rounded-lg border-0 bg-white py-2 pr-4 pl-9 text-sm text-gray-900 shadow-sm ring-1 ring-gray-200 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700"
                >
            </div>
            <div class="rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                <span x-text="selected.length"></span> of {{ $total }} permissions selected
            </div>
        </div>
    </div>

    <template x-for="group in groups" :key="group.key">
        <div x-show="visibleGroup(group)" class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-gray-100/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/80">
                <div class="flex items-center gap-2">
                    <span x-text="group.label" class="text-sm font-semibold text-gray-900 dark:text-gray-100"></span>
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <span x-text="selectedInGroup(group).length"></span>/<span x-text="group.ids.length"></span>
                    </span>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">
                    <input
                        type="checkbox"
                        :checked="groupAllChecked(group)"
                        :indeterminate.prop="groupIndeterminate(group)"
                        @change="toggleGroup(group)"
                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                    >
                    <span>Select all</span>
                </label>
            </div>

            <div class="grid gap-5 bg-white p-4 dark:bg-gray-950">
                <template x-for="resource in group.resources" :key="resource.name">
                    <div x-show="visibleResource(resource)">
                        <div class="mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase dark:text-gray-500" x-text="resource.name"></div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                            <template x-for="permission in resource.permissions" :key="permission.id">
                                <label
                                    class="group flex cursor-pointer items-center gap-1.5 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
                                    x-bind:title="permission.label + ' (' + permission.name + ')'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.id"
                                        x-model="selected"
                                        @change="persist()"
                                        class="h-3.5 w-3.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                    >
                                    <span x-text="permission.action" x-bind:class="actionClass(permission.kind)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <div x-show="visibleGroupsCount() === 0" class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
        No permissions match your search.
    </div>

    <script>
        function permissionMatrix({ groups, selected }) {
            return {
                groups,
                selected,
                search: '',

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

                actionClass(kind) {
                    const classes = {
                        crud: 'text-gray-800 dark:text-gray-100',
                        manage: 'font-semibold text-purple-700 dark:text-purple-400',
                        access: 'font-medium text-amber-700 dark:text-amber-400',
                        action: 'text-blue-700 dark:text-blue-400',
                        legacy: 'text-emerald-700 dark:text-emerald-400',
                    };

                    return classes[kind] ?? classes.legacy;
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