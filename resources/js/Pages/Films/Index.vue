<template>
    <AppLayout>
        <Head title="Filmid & API" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🎬 Filmid & API</h1>
                    <p class="text-gray-500 mt-1">
                        {{ films.total }} filmi andmebaasis ·
                        <code class="bg-gray-100 px-1 rounded text-sm">GET /api/v1/films</code>
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        v-if="$page.props.auth.user"
                        @click="showAddModal = true"
                        class="btn-primary"
                    >
                        ➕ Lisa film
                    </button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 mb-6 bg-gray-100 rounded-xl p-1 w-fit">
                <button
                    @click="activeTab = 'films'"
                    :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'films' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']"
                >
                    🎬 Filmid
                </button>
                <button
                    @click="activeTab = 'api'"
                    :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'api' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']"
                >
                    📡 API docs
                </button>
                <button
                    @click="activeTab = 'external'"
                    :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'external' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']"
                >
                    🌐 Väline API
                </button>
                <button
                    v-if="$page.props.auth.user"
                    @click="activeTab = 'token'"
                    :class="['px-5 py-2 rounded-lg text-sm font-medium transition', activeTab === 'token' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700']"
                >
                    🔑 API Võti
                </button>
            </div>

            <!-- ── TAB: FILMID ── -->
            <div v-show="activeTab === 'films'">
                <!-- Filters -->
                <div class="card p-5 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label text-xs">Otsing</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                class="form-input"
                                placeholder="Pealkiri, režissöör..."
                                @input="debouncedSearch"
                            />
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

                <!-- Films Grid -->
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

                <!-- Empty State -->
                <div v-else class="text-center py-16 text-gray-400">
                    <p class="text-5xl mb-4">🎬</p>
                    <p>
                        Filme ei leitud.
                        <button v-if="$page.props.auth.user" @click="showAddModal = true" class="text-primary-600 underline">Lisa esimene film!</button>
                        <span v-else class="text-gray-400">Logi sisse filmide lisamiseks.</span>
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="films.last_page > 1" class="flex justify-center gap-2 mt-4">
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

            <!-- ── TAB: API DOCS ── -->
            <div v-show="activeTab === 'api'">
                <div class="space-y-6">
                    <!-- Overview -->
                    <div class="card p-6 bg-gray-900 text-green-400 font-mono text-sm overflow-x-auto">
                        <p class="text-gray-400 text-xs mb-3"># Saadaval API otspunktid</p>
                        <p>GET {{ baseUrl }}/api/v1/films</p>
                        <p>GET {{ baseUrl }}/api/v1/films/{id}</p>
                        <p>GET {{ baseUrl }}/api/v1/markers</p>
                        <p>GET {{ baseUrl }}/api/v1/weather?city=Tallinn</p>
                    </div>

                    <!-- Endpoints table -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Otspunktid</h2>
                        <div class="space-y-2 text-sm font-mono">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold shrink-0">GET</span>
                                <span class="text-gray-700">/api/v1/films</span>
                                <span class="text-gray-400 text-xs ml-auto">?search= &amp;genre= &amp;sort= &amp;limit=</span>
                            </div>
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold shrink-0">GET</span>
                                <span class="text-gray-700">/api/v1/films/{id}</span>
                            </div>
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold shrink-0">GET</span>
                                <span class="text-gray-700">/api/v1/markers</span>
                            </div>
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold shrink-0">GET</span>
                                <span class="text-gray-700">/api/v1/weather</span>
                                <span class="text-gray-400 text-xs ml-auto">?city=Tallinn</span>
                            </div>
                        </div>
                    </div>

                    <!-- Auth -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">🔐 Autentimine</h2>
                        <p class="text-sm text-gray-600 mb-4">Lisa API võti igale päringule päisena või query parameetrina:</p>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Authorization päis (soovitatav)</p>
                                <div class="bg-gray-900 text-green-400 font-mono text-sm rounded-xl p-4 overflow-x-auto">
                                    <p class="text-gray-400"># Filmide nimekiri</p>
                                    <p>curl {{ baseUrl }}/api/v1/films \</p>
                                    <p>&nbsp;&nbsp;-H "Authorization: Bearer <span class="text-yellow-300">SINU_API_VÕTI</span>"</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Query parameeter</p>
                                <div class="bg-gray-900 text-green-400 font-mono text-sm rounded-xl p-4 overflow-x-auto">
                                    <p>{{ baseUrl }}/api/v1/films?api_token=<span class="text-yellow-300">SINU_API_VÕTI</span></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">JavaScript (fetch)</p>
                                <div class="bg-gray-900 text-blue-300 font-mono text-sm rounded-xl p-4 overflow-x-auto">
                                    <p><span class="text-purple-400">const</span> res = <span class="text-purple-400">await</span> fetch(<span class="text-green-400">'{{ baseUrl }}/api/v1/films'</span>, {</p>
                                    <p>&nbsp;&nbsp;headers: { <span class="text-green-400">'Authorization'</span>: <span class="text-green-400">`Bearer ${'$'}{apiToken}`</span> }</p>
                                    <p>});</p>
                                    <p><span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> res.json();</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB: VÄLISTE API ── -->
            <div v-show="activeTab === 'external'">
                <!-- Examples -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📚 Näited</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Example 1: Sharks -->
                        <div class="card p-4 border-2 border-blue-200 hover:border-blue-400 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900">🦈 Shark'ide API</h3>
                                    <p class="text-xs text-gray-500">Sinu sõbra API</p>
                                </div>
                                <button @click="loadExample('https://hajusrakendused-main-pm8ido.laravel.cloud/api/sharks', 'xb0RGUxrOUV5JOcE0CeQNU27V0U1WfU2FGgv8U1z', 'header')" class="btn-primary text-xs px-3 py-1">
                                    Kasuta
                                </button>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p><span class="font-semibold">URL:</span> hajusrakendused-main-pm8ido.laravel.cloud/api/sharks</p>
                                <p><span class="font-semibold">Auth:</span> X-API-Key</p>
                            </div>
                        </div>

                        <!-- Example 2: Rick & Morty -->
                        <div class="card p-4 border-2 border-green-200 hover:border-green-400 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900">🎬 Rick & Morty</h3>
                                    <p class="text-xs text-gray-500">Avalik API</p>
                                </div>
                                <button @click="loadExample('https://rickandmortyapi.com/api/character', '', 'none')" class="btn-primary text-xs px-3 py-1">
                                    Kasuta
                                </button>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p><span class="font-semibold">URL:</span> rickandmortyapi.com/api/character</p>
                                <p><span class="font-semibold">Auth:</span> Pole nõutav</p>
                            </div>
                        </div>

                        <!-- Example 3: PokéAPI -->
                        <div class="card p-4 border-2 border-purple-200 hover:border-purple-400 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900">🎮 PokéAPI</h3>
                                    <p class="text-xs text-gray-500">Avalik API</p>
                                </div>
                                <button @click="loadExample('https://pokeapi.co/api/v2/pokemon?limit=20', '', 'none')" class="btn-primary text-xs px-3 py-1">
                                    Kasuta
                                </button>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p><span class="font-semibold">URL:</span> pokeapi.co/api/v2/pokemon</p>
                                <p><span class="font-semibold">Auth:</span> Pole nõutav</p>
                            </div>
                        </div>

                        <!-- Example 4: Random Users -->
                        <div class="card p-4 border-2 border-orange-200 hover:border-orange-400 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900">👥 Random Users</h3>
                                    <p class="text-xs text-gray-500">Avalik API</p>
                                </div>
                                <button @click="loadExample('https://randomuser.me/api/?results=10', '', 'none')" class="btn-primary text-xs px-3 py-1">
                                    Kasuta
                                </button>
                            </div>
                            <div class="space-y-1 text-xs text-gray-600">
                                <p><span class="font-semibold">URL:</span> randomuser.me/api</p>
                                <p><span class="font-semibold">Auth:</span> Pole nõutav</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">🦈 Välise API Andmete Tester</h2>
                    <p class="text-sm text-gray-600 mb-4">Sisesta suvaline API otspunkt ja võti - hankige andmeid ükskõik millest:</p>

                    <!-- API URL Input -->
                    <div class="mb-4">
                        <label class="form-label text-xs">API Otspunkt (URL) *</label>
                        <input
                            v-model="externalApiUrl"
                            type="url"
                            class="form-input"
                            placeholder="https://api.näide.ee/andmed"
                            @keyup.enter="fetchExternalData"
                        />
                    </div>

                    <!-- API Key Input -->
                    <div class="mb-4">
                        <label class="form-label text-xs">API Võti</label>
                        <input
                            v-model="externalApiKey"
                            type="password"
                            class="form-input"
                            placeholder="Sisesta API võti..."
                            @keyup.enter="fetchExternalData"
                        />
                    </div>

                    <!-- Auth Method -->
                    <div class="mb-4">
                        <label class="form-label text-xs">Autentimise Meetod</label>
                        <select v-model="externalAuthMethod" class="form-input">
                            <option value="none">Pole nõutav (avalik API)</option>
                            <option value="header">Päises (X-API-Key)</option>
                            <option value="bearer">Päises (Authorization: Bearer)</option>
                            <option value="custom_header">Kohandatud päis</option>
                            <option value="query">Query parameeter (api_key=...)</option>
                        </select>
                    </div>

                    <!-- Custom Header Name (if needed) -->
                    <div v-if="externalAuthMethod === 'custom_header'" class="mb-4">
                        <label class="form-label text-xs">Päise Nimi</label>
                        <input
                            v-model="externalAuthHeaderName"
                            type="text"
                            class="form-input"
                            placeholder="Nt: Authorization"
                        />
                    </div>

                    <!-- Fetch Button -->
                    <div class="flex gap-3 mb-6">
                        <button
                            @click="fetchExternalData"
                            :disabled="!externalApiUrl || externalLoading"
                            class="btn-primary"
                        >
                            {{ externalLoading ? '⏳ Laadib...' : '🚀 Hangi andmed' }}
                        </button>
                        <button
                            v-if="externalData.length"
                            @click="clearExternalData"
                            class="btn-secondary"
                        >
                            🗑️ Tühista
                        </button>
                    </div>

                    <!-- Error Message -->
                    <div v-if="externalError" class="p-4 bg-red-50 border border-red-200 rounded-lg mb-4">
                        <p class="text-sm text-red-700">
                            <strong>❌ Viga:</strong> {{ externalError }}
                        </p>
                    </div>

                    <!-- Success Message -->
                    <div v-if="externalData.length" class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                        <p class="text-sm text-green-700">
                            <strong>✅</strong> Laaditud {{ externalData.length }} kirjet
                        </p>
                    </div>

                    <!-- Results Grid -->
                    <div v-if="externalData.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="(item, index) in externalData"
                            :key="index"
                            class="card hover:shadow-lg transition-shadow p-4"
                        >
                            <!-- Image (if exists) -->
                            <div v-if="item.image || item.image_url || item.picture" class="mb-3 h-40 overflow-hidden rounded-lg bg-gray-800">
                                <img
                                    :src="getImageUrl(item)"
                                    :alt="getItemTitle(item)"
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <!-- Title -->
                            <h3 class="font-bold text-gray-900 mb-2">{{ getItemTitle(item) }}</h3>

                            <!-- Description -->
                            <p v-if="getItemDescription(item)" class="text-sm text-gray-600 line-clamp-2 mb-3">
                                {{ getItemDescription(item) }}
                            </p>

                            <!-- All Fields as Key-Value -->
                            <div class="space-y-1 text-xs text-gray-500">
                                <div v-for="[key, value] in getFilteredFields(item)" :key="key" class="truncate">
                                    <span class="font-semibold">{{ formatKey(key) }}:</span>
                                    <span class="text-gray-600">{{ formatValue(value) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!externalLoading && !externalError && !externalData.length && externalApiUrl" class="text-center py-8 text-gray-400">
                        <p class="text-lg">🔍 Andmeid ei leitud</p>
                    </div>
                </div>
            </div>

            <!-- ── TAB: API TOKEN ── -->
            <div v-if="$page.props.auth.user" v-show="activeTab === 'token'">

                <!-- Newly generated token — shown ONCE -->
                <div v-if="newToken" class="card p-6 mb-6 border-2 border-green-400 bg-green-50">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-green-600 text-xl">✅</span>
                        <h2 class="font-semibold text-green-800">Uus API võti genereeritud!</h2>
                    </div>
                    <p class="text-sm text-green-700 mb-3">
                        Kopeeri see võti kohe — seda näidatakse ainult <strong>üks kord</strong>.
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-white border border-green-300 rounded-lg px-4 py-3 font-mono text-sm break-all select-all">{{ newToken }}</code>
                        <button @click="copyToken(newToken)" class="btn-primary shrink-0 text-sm px-4 py-3">
                            {{ copied ? '✅ Kopeeritud!' : '📋 Kopeeri' }}
                        </button>
                    </div>
                </div>

                <!-- Token status -->
                <div class="card p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Võtme olek</h2>

                    <div v-if="hasToken" class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl mb-4">
                        <span class="text-2xl">🟢</span>
                        <div>
                            <p class="font-medium text-blue-900">API võti on aktiivne</p>
                            <p class="text-sm text-blue-700">Sul on kehtiv API võti. Regenereerimisel luuakse uus võti ja vana lakkab töötamast.</p>
                        </div>
                    </div>
                    <div v-else class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-xl mb-4">
                        <span class="text-2xl">⚪</span>
                        <div>
                            <p class="font-medium text-gray-700">API võtit pole</p>
                            <p class="text-sm text-gray-500">Genereeri võti allpool, et hakata API-t kasutama.</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="generate" class="btn-primary">
                            {{ hasToken ? '🔄 Regenereeri võti' : '🔑 Genereeri võti' }}
                        </button>
                        <button
                            v-if="hasToken"
                            @click="revoke"
                            class="btn-secondary text-red-600 hover:bg-red-50 border-red-200"
                        >
                            🗑️ Tühista võti
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>

    <!-- ── Add Film Modal ── -->
    <Teleport to="body">
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">🎬 Lisa uus film</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="submitFilm" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="form-label">Pealkiri *</label>
                            <input v-model="form.title" type="text" class="form-input" placeholder="Filmi pealkiri" required />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="form-label">Režissöör *</label>
                            <input v-model="form.director" type="text" class="form-input" placeholder="Nt Christopher Nolan" required />
                            <p v-if="form.errors.director" class="text-red-500 text-xs mt-1">{{ form.errors.director }}</p>
                        </div>
                        <div>
                            <label class="form-label">Aasta *</label>
                            <input v-model="form.year" type="number" min="1888" :max="new Date().getFullYear() + 2" class="form-input" placeholder="2024" required />
                            <p v-if="form.errors.year" class="text-red-500 text-xs mt-1">{{ form.errors.year }}</p>
                        </div>
                        <div>
                            <label class="form-label">Žanr</label>
                            <input v-model="form.genre" type="text" class="form-input" placeholder="Nt Põnevik, Draama" list="genre-suggestions-modal" />
                            <datalist id="genre-suggestions-modal">
                                <option v-for="g in genreSuggestions" :key="g" :value="g" />
                            </datalist>
                        </div>
                        <div>
                            <label class="form-label">Hinne (0–10)</label>
                            <input v-model="form.rating" type="number" step="0.1" min="0" max="10" class="form-input" placeholder="Nt 8.5" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Pildi URL</label>
                            <input v-model="form.image" type="url" class="form-input" placeholder="https://..." />
                            <div v-if="form.image" class="mt-2 h-32 rounded-lg overflow-hidden">
                                <img :src="form.image" alt="Eelvaade" class="w-full h-full object-cover" @error="form.image = ''" />
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Kirjeldus *</label>
                            <textarea v-model="form.description" rows="4" class="form-input" placeholder="Filmi lühikirjeldus..." required></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal" class="btn-secondary flex-1">Tühista</button>
                        <button type="submit" class="btn-primary flex-1" :disabled="form.processing">
                            {{ form.processing ? 'Salvestab...' : '🎬 Lisa film' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    films:    Object,
    filters:  Object,
    genres:   Array,
    hasToken: Boolean,
    newToken: String,
});

const page       = usePage();
const activeTab  = ref('films');
const showAddModal = ref(false);
const copied     = ref(false);
const baseUrl    = computed(() => window.location.origin);

// ── External API ──
const externalApiUrl = ref('');
const externalApiKey = ref('');
const externalAuthMethod = ref('header');
const externalAuthHeaderName = ref('X-API-Key');
const externalData = ref([]);
const externalLoading = ref(false);
const externalError = ref('');

// ── Filters ──
const filters = ref({ ...props.filters });
let searchTimer = null;

function applyFilters() {
    const params = {};
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.genre)  params.genre  = filters.value.genre;
    if (filters.value.sort)   params.sort   = filters.value.sort;
    if (filters.value.limit)  params.limit  = filters.value.limit;
    router.get(route('films.index'), params, { preserveState: true });
}

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
}

function clearFilters() {
    filters.value = { search: '', genre: '', sort: '', limit: '' };
    router.get(route('films.index'));
}

function goToPage(p) {
    router.get(route('films.index'), { ...filters.value, page: p });
}

// ── Film actions ──
function canDelete(film) {
    const user = page.props.auth?.user;
    return user && (user.id === film.user_id || user.is_admin);
}

function deleteFilm(film) {
    if (!confirm(`Kustuta film "${film.title}"?`)) return;
    router.delete(route('films.destroy', film.id), { preserveScroll: true });
}

// ── Add film modal ──
const form = useForm({
    title: '', director: '', year: new Date().getFullYear(),
    genre: '', rating: '', image: '', description: '',
});

const genreSuggestions = [
    'Action', 'Draama', 'Komöödia', 'Põnevik', 'Õudus',
    'Romantika', 'Sci-Fi', 'Animatsioon', 'Dokumentaal', 'Seiklus',
];

function submitFilm() {
    form.post(route('films.store'), {
        onSuccess: () => { closeModal(); },
    });
}

function closeModal() {
    showAddModal.value = false;
    form.reset();
}

// ── API Token ──
async function copyToken(token) {
    try {
        await navigator.clipboard.writeText(token);
    } catch {
        const el = document.createElement('textarea');
        el.value = token;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
}

function generate() {
    router.post(route('api.token.generate'), {}, {
        onSuccess: () => { activeTab.value = 'token'; },
    });
}

function revoke() {
    if (!confirm('Kas oled kindel? Vana võti lakkab kohe töötamast.')) return;
    router.post(route('api.token.revoke'));
}

// ── External API Fetcher ──
async function fetchExternalData() {
    externalLoading.value = true;
    externalError.value = '';
    externalData.value = [];

    try {
        // Validate URL
        new URL(externalApiUrl.value);

        // Build request options
        const headers = {
            'Accept': 'application/json',
        };

        let url = externalApiUrl.value;

        // Add API key based on auth method
        if (externalApiKey.value) {
            if (externalAuthMethod.value === 'header') {
                headers['X-API-Key'] = externalApiKey.value;
            } else if (externalAuthMethod.value === 'bearer') {
                headers['Authorization'] = `Bearer ${externalApiKey.value}`;
            } else if (externalAuthMethod.value === 'custom_header') {
                headers[externalAuthHeaderName.value] = externalApiKey.value;
            } else if (externalAuthMethod.value === 'query') {
                url += (url.includes('?') ? '&' : '?') + `api_key=${encodeURIComponent(externalApiKey.value)}`;
            }
        }

        const response = await fetch(url, {
            method: 'GET',
            headers,
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                externalError.value = 'Vale API võti või puudub juurdepääs.';
            } else if (response.status === 404) {
                externalError.value = 'API otspunkt ei leitud.';
            } else {
                externalError.value = `API viga: ${response.status} ${response.statusText}`;
            }
            return;
        }

        const data = await response.json();

        // Handle different response formats
        let items = [];
        if (Array.isArray(data)) {
            items = data;
        } else if (data.data && Array.isArray(data.data)) {
            items = data.data;
        } else if (data.results && Array.isArray(data.results)) {
            items = data.results;
        } else if (data.items && Array.isArray(data.items)) {
            items = data.items;
        } else if (typeof data === 'object' && data !== null) {
            // If it's a single object, wrap it
            items = [data];
        }

        if (items.length === 0) {
            externalError.value = 'Andmeid ei leitud.';
            return;
        }

        externalData.value = items;
    } catch (error) {
        if (error instanceof TypeError) {
            externalError.value = 'Vale URL formaat.';
        } else {
            externalError.value = `Viga: ${error.message}`;
        }
    } finally {
        externalLoading.value = false;
    }
}

function clearExternalData() {
    externalApiUrl.value = '';
    externalApiKey.value = '';
    externalData.value = [];
    externalError.value = '';
}

function loadExample(url, key, authMethod) {
    externalApiUrl.value = url;
    externalApiKey.value = key;
    externalAuthMethod.value = authMethod;
}

// Helper functions
function getItemTitle(item) {
    return item.title || item.name || item.label || item.id || 'Item';
}

function getItemDescription(item) {
    return item.description || item.desc || item.bio || item.content || '';
}

function getImageUrl(item) {
    let imageField = item.image || item.image_url || item.picture || item.photo || '';
    if (imageField && !imageField.startsWith('http')) {
        return 'https://hajusrakendused-main-pm8ido.laravel.cloud' + imageField;
    }
    return imageField;
}

function getFilteredFields(item) {
    const ignore = ['id', 'title', 'name', 'description', 'desc', 'image', 'image_url', 'picture', 'photo', 'bio', 'content', 'user', 'created_at', 'updated_at'];
    const result = {};

    for (const [key, value] of Object.entries(item)) {
        if (!ignore.includes(key) && value !== null && value !== undefined) {
            // Include objects too, just stringify them
            if (typeof value === 'object') {
                if (Array.isArray(value)) {
                    result[key] = `[Array with ${value.length} items]`;
                } else {
                    result[key] = JSON.stringify(value).substring(0, 50);
                }
            } else {
                result[key] = value;
            }
        }
    }

    // Return first 5 fields as array of [key, value] pairs
    return Object.entries(result).slice(0, 5);
}

function formatKey(key) {
    if (typeof key !== 'string') return String(key);
    return key.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1').trim();
}

function formatValue(value) {
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (typeof value === 'number') return value.toString();
    return String(value).substring(0, 50);
}
</script>