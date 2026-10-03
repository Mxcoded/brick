@extends('restaurant::layouts.master')
@section('title', 'Menu')

@section('content')
    @auth
        @canany(['access_restaurant_dashboard', 'orders.create', 'menu.read', 'menu.create', 'menu.update', 'menu.delete'])
            <div class="admin-quick-bar">
                <div class="d-flex align-items-center gap-2 px-3 py-2">
                    <span class="text-white-50 small me-2"><i class="bi bi-person-badge me-1"></i>Staff</span>
                    @can('orders.create')
                        <a href="{{ route('restaurant.waiter.dashboard') }}" class="btn btn-sm btn-outline-light rounded-pill">
                            <i class="bi bi-calculator me-1"></i>POS
                        </a>
                    @endcan
                    @canany(['menu.create', 'menu.update', 'menu.delete', 'menu.read'])
                        <a href="{{ route('restaurant.admin.dashboard') }}" class="btn btn-sm btn-outline-light rounded-pill">
                            <i class="bi bi-gear me-1"></i>Manage Menu
                        </a>
                    @endcanany
                    <span class="ms-auto">
                        <a href="{{ route('restaurant.landing') }}" class="btn btn-sm btn-outline-light rounded-pill">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </span>
                </div>
            </div>
        @endcanany
    @endauth

    <div class="menu-simple" x-data="simpleMenu">
        <div class="menu-simple-head">
            <h1>
                @if ($type === 'table')
                    Menu &mdash; Table {{ $sourceModel->number ?? 'N/A' }}
                @elseif ($type === 'room')
                    Menu &mdash; Room {{ $sourceModel->name ?? 'N/A' }}
                @else
                    Our Menu
                @endif
            </h1>
            <p>Tap a dish to add it to your order. You can add notes for the kitchen if you want to.</p>
        </div>

        {{-- The whole list is rendered by Alpine from the same JSON the search
             reads, so searching and browsing cannot disagree about what exists. --}}
        <template x-if="groups.length > 0">
            <div>
                <template x-if="groups.length > 1">
                    <div class="menu-cats" role="tablist" aria-label="Menu sections">
                        <template x-for="group in groups" :key="group.id">
                            <button type="button" class="menu-cat" role="tab" :aria-selected="activeCategory === group.id"
                                @click="activeCategory = group.id" x-text="group.name"></button>
                        </template>
                    </div>
                </template>

                <div class="menu-search">
                    <input type="search" x-model.debounce.250ms="search"
                        placeholder="Search for a dish" aria-label="Search the menu" autocomplete="off">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </div>

                {{-- While a search is running every section that has a match
                     stays on screen. Hiding all but the selected section would
                     hide a hit the customer can see is right there. --}}
                <template x-for="group in groups" :key="group.id">
                    <section x-show="activeCategory === group.id || search.trim() !== ''" class="menu-section"
                        :hidden="activeCategory !== group.id && search.trim() === ''">
                        <h2 class="menu-group-title" x-text="group.name"></h2>

                        <div class="menu-grid">
                            <template x-for="item in visibleItems(group)" :key="item.id">
                                <article class="menu-card" :class="{ 'menu-card-sold': !item.is_available }">
                                    <img class="menu-card-img" :src="imageFor(item)" :alt="item.name" loading="lazy">

                                    <div class="menu-card-body">
                                        <h3 class="menu-card-name" x-text="item.name"></h3>
                                        <p class="menu-card-desc" x-show="item.description" x-text="item.description"></p>

                                        {{-- Only the waiter terminal is shown
                                             sold-out dishes, and it renders the
                                             item as unavailable rather than
                                             letting a sale be attempted. --}}
                                        <template x-if="!item.is_available">
                                            <span class="menu-sold-flag">Sold out today</span>
                                        </template>

                                        <template x-if="item.is_available">
                                            <div>
                                                <div class="menu-card-foot">
                                                    <span class="menu-card-price"
                                                        x-text="`₦${Number(item.price || 0).toLocaleString()}`"></span>
                                                    <button type="button" class="menu-card-add"
                                                        @click="add(item)" :disabled="busyItem === item.id">
                                                        <span x-show="busyItem !== item.id">Add</span>
                                                        <span x-show="busyItem === item.id">
                                                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                                                            <span class="visually-hidden">Adding</span>
                                                        </span>
                                                    </button>
                                                </div>

                                                {{-- Notes are opt-in. A text box on every card is
                                                     clutter, and it is the easiest way for a
                                                     customer to send the kitchen a mistake. --}}
                                                <button type="button" class="menu-note-toggle"
                                                    @click="toggleNote(item.id)"
                                                    :aria-expanded="noteOpen(item.id) ? 'true' : 'false'"
                                                    x-text="noteOpen(item.id) ? 'Hide note' : 'Add a note'"></button>

                                                <div class="menu-note" :class="{ open: noteOpen(item.id) }">
                                                    <div>
                                                        <textarea x-model="notes[item.id]" rows="2"
                                                            :aria-label="`Note for ${item.name}`"
                                                            placeholder="No onions, extra spicy&hellip;"
                                                            x-bind:disabled="busyItem === item.id"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </article>
                            </template>

                            <div class="menu-none" x-show="visibleItems(group).length === 0">
                                <i class="fas fa-utensils" aria-hidden="true"></i>
                                <strong x-text="search.trim() ? 'No dish matches that' : 'Nothing here right now'"></strong>
                                <span x-text="search.trim() ? 'Try a shorter word, or pick another section.' : 'Please check another section.'"></span>
                            </div>
                        </div>
                    </section>
                </template>
            </div>
        </template>

        <template x-if="groups.length === 0">
            <div class="order-empty">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                <h2>The menu is not ready yet</h2>
                <p>Please ask a member of staff, or check back in a moment.</p>
            </div>
        </template>
    </div>
@endsection

@push('scripts')
    <script>
        // The public menu is one Alpine component so that category selection,
        // search and the add button all read the same item list.
        document.addEventListener('alpine:init', () => {
            const data = @json($categories);

            const itemsOf = (node) => (node && Array.isArray(node.menu_items) ? node.menu_items : []);

            // Children carry their own sub-dishes, so a category becomes one
            // list of cards per level. The parent renders as its own group and
            // each child renders as a further group, which keeps the
            // relationship obvious without a nested accordion.
            const buildGroups = (nodes) => {
                const groups = [];
                const walk = (node) => {
                    groups.push({ id: node.id, name: node.name, items: itemsOf(node) });
                    (node.children_recursive || []).forEach(walk);
                };
                (Array.isArray(data) ? data : []).forEach(walk);
                return groups;
            };

            Alpine.data('simpleMenu', () => ({
                groups: buildGroups(data),
                activeCategory: null,
                search: '',
                notes: {},
                openNotes: {},
                busyItem: null,
                init() {
                    this.activeCategory = this.groups.length ? this.groups[0].id : null;
                },
                noteOpen(id) {
                    return this.openNotes[id] === true;
                },
                toggleNote(id) {
                    this.openNotes[id] = !this.openNotes[id];
                },
                imageFor(item) {
                    return item.image
                        ? `{{ asset('storage') }}/${item.image}`
                        : `{{ asset('storage/images/menudefaultimage.png') }}`;
                },
                visibleItems(group) {
                    const term = this.search.trim().toLowerCase();
                    if (!term) return group.items;
                    return group.items.filter((item) => {
                        const name = (item.name || '').toLowerCase();
                        const description = (item.description || '').toLowerCase();
                        return name.includes(term) || description.includes(term);
                    });
                },
                add(item) {
                    if (this.busyItem !== null) return;
                    this.busyItem = item.id;
                    // The store is reached through the component because the
                    // bare $store magic only exists inside expressions Alpine
                    // evaluates from the markup. In a method body it is a plain
                    // function scope with no such name in it.
                    //
                    // Price and name are passed only so the confirmation can
                    // read nicely. The server re-reads both from the database.
                    this.$store.cart
                        .addToCart(item.id, item.name, item.price, 1, this.notes[item.id] || '')
                        .finally(() => {
                            this.busyItem = null;
                        });
                },
            }));
        });
    </script>
@endpush
