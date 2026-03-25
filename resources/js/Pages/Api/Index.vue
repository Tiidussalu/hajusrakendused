<template>
    <AppLayout>
        <Head title="Filmide API" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🎬 Filmide API</h1>
                    <p class="text-gray-500 mt-1">{{ films.total }} filmi andmebaasis · JSON API: <code class="bg-gray-100 px-1 rounded text-sm">/api/v1/films</code></p>
                </div>
                <Link v-if="$page.props.auth.user" :href="route('api-explorer.create')" class="btn-primary">
                    ➕ Lisa film
                </Link>
            </div>

            <!-- API Docs snippet -->
            <div class="card p-5 mb-6 bg-gray-900 text-green-400 font-mono text-sm overflow-x-auto">
                <p class="text-gray-400 text-xs mb-2"># API päringute näited</p>
                <p>GET /api/v1/films</p>
                <p>GET /api/v1/films?search=nolan&amp;genre=Põnevik&amp;sort=-rating&amp;limit=5</p>
                <p>GET /api/v1/films/{id}</p>
            </div>

            <!-- Filters -->
            <div class="card p-5 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label text-xs">Otsing</label>
                        <input v-model="filters.search" type="text" class="form-input" placeholder="Pealkiri, režissöör..." @input="debouncedSearch" />
                    </div>
                    <div>
                        <label class="form-label text-xs">Žanr</label>
                        <select v-model="filters.genre" class="form-input" @change="applyFilters">
                            <option value="">Kõik žanrid</option>
                            <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Sorteeri</label>
                        <select v-model="filters.sort" class="form-input" @change="applyFilters">
                            <option value="-created_at">Uuemad ees</option>
                            <option value="created_at">Vanemad ees</option>
                            <option value="-rating">Kõrgem hinne ees</option>
                            <option value="rating">Madalam hinne ees</option>
                            <option value="title">Pealkiri A-Z</option>
                            <option value="-year">Uuem aasta ees</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Näita kirjeid</label>
                        <select v-model="filters.limit" class="form-input" @change="applyFilters">
                            <option value="">Vaikimisi (12)</option>
                            <option value="6">6</option>
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button @click="clearFilters" class="btn-secondary text-xs">Tühista filtrid</button>
                </div>
            </div>

            <!-- Films grid -->
            <div v-if="films.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <div
                    v-for="film in films.data"
                    :key="film.id"
                    class="card hover:shadow-lg transition-shadow group"
                >
                    <div class="h-56 overflow-hidden bg-gray-800 relative">
                        <img
                            v-if="film.image"
                            :src="film.image"
                            :alt="film.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-6xl">🎬</span>
                        </div>
                        <!-- Rating badge -->
                        <div v-if="film.rating" class="absolute top-2 right-2 bg-yellow-400 text-gray-900 text-xs font-bold px-2 py-1 rounded-full">
                            ⭐ {{ film.rating }}
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 class="font-bold text-gray-900 leading-tight">{{ film.title }}</h3>
                            <span class="text-gray-400 text-sm shrink-0">{{ film.year }}</span>
                        </div>
                        <p class="text-xs text-primary-600 font-medium mb-1">🎥 {{ film.director }}</p>
                        <p v-if="film.genre" class="text-xs text-gray-400 bg-gray-100 inline-block px-2 py-0.5 rounded-full mb-2">{{ film.genre }}</p>
                        <p class="text-sm text-gray-600 line-clamp-3 mb-3">{{ film.description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">Lisa: {{ film.user?.name ?? 'Anonüümne' }}</span>
                            <button
                                v-if="canDelete(film)"
                                @click="deleteFilm(film)"
                                class="text-xs text-red-400 hover:text-red-600"
                            >🗑️</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-16 text-gray-400">
                <p class="text-5xl mb-4">🎬</p>
                <p>Filme ei leitud.
                    <Link v-if="$page.props.auth.user" :href="route('api-explorer.create')" class="text-primary-600 underline">Lisa esimene film!</Link>
                    <span v-else class="text-gray-400">Logi sisse filmide lisamiseks.</span>
                </p>
            </div>

            <!-- Pagination -->
            <div v-if="films.last_page > 1" class="flex justify-center gap-2">
                <button
                    v-for="page in films.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition',
                        page === films.current_page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                    ]"
                >{{ page }}</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    films:   Object,
    filters: Object,
    genres:  Array,
});

// Use Inertia's usePage() — the correct way to access shared props
const page = usePage();

const filters = ref({ ...props.filters });
let searchTimer = null;

function applyFilters() {
    const params = {};
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.genre)  params.genre  = filters.value.genre;
    if (filters.value.sort)   params.sort   = filters.value.sort;
    if (filters.value.limit)  params.limit  = filters.value.limit;
    router.get(route('api-explorer.index'), params, { preserveState: true });
}

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
}

function clearFilters() {
    filters.value = { search: '', genre: '', sort: '', limit: '' };
    router.get(route('api-explorer.index'));
}

function goToPage(page) {
    router.get(route('api-explorer.index'), { ...filters.value, page });
}

// Fixed: use usePage() instead of broken window.__inertia hack
function canDelete(film) {
    const user = page.props.auth?.user;
    return user && (user.id === film.user_id || user.is_admin);
}

async function deleteFilm(film) {
    if (!confirm(`Kustuta film "${film.title}"?`)) return;
    router.delete(route('api-explorer.destroy', film.id), {
        preserveScroll: true,
    });
}
</script>