<template>
    <AppLayout>
        <Head title="Avaleht" />

        <!-- Hero -->
        <div class="bg-gradient-to-br from-primary-700 via-primary-600 to-indigo-700 text-white py-20 px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl font-bold mb-4 leading-tight">Hajusrakendus</h1>
                <p class="text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                    Laravel 13 · Vue 3 · Inertia.js · Tailwind CSS
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <Link :href="route('weather.index')" class="bg-white text-primary-700 font-semibold px-6 py-3 rounded-xl hover:bg-gray-50 transition">
                        🌤️ Ilmateenistus
                    </Link>
                    <Link :href="route('map.index')" class="bg-white/20 text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/30 transition border border-white/30">
                        🗺️ Kaardirakendus
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
            <div class="grid grid-cols-3 gap-4">
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-primary-600">{{ stats.posts }}</p>
                    <p class="text-sm text-gray-500 mt-1">Blogipostitust</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-primary-600">{{ stats.markers }}</p>
                    <p class="text-sm text-gray-500 mt-1">Kaardimarkerit</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-primary-600">{{ stats.films }}</p>
                    <p class="text-sm text-gray-500 mt-1">Filmi API-s</p>
                </div>
            </div>
        </div>

        <!-- Module cards -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Kõik moodulid</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <Link :href="route('weather.index')" class="card p-6 hover:shadow-lg transition-shadow group">
                    <div class="text-4xl mb-3">🌤️</div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">Ilmateenistus</h3>
                    <p class="text-sm text-gray-500">Reaalajas ilmaandmed OpenWeatherMap API kaudu. 5-päeva prognoos, vahemälu, linna otsing.</p>
                    <span class="mt-3 inline-block text-primary-600 text-sm font-medium">Ava →</span>
                </Link>

                <Link :href="route('map.index')" class="card p-6 hover:shadow-lg transition-shadow group">
                    <div class="text-4xl mb-3">🗺️</div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">Kaardirakendus</h3>
                    <p class="text-sm text-gray-500">Radar Maps kaart markerite haldamiseks. Lisa, muuda, kustuta. Klõpsa kaardil markeri lisamiseks.</p>
                    <span class="mt-3 inline-block text-primary-600 text-sm font-medium">Ava →</span>
                </Link>

                <Link :href="route('blog.index')" class="card p-6 hover:shadow-lg transition-shadow group">
                    <div class="text-4xl mb-3">📝</div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">Blogi</h3>
                    <p class="text-sm text-gray-500">Blogipostituste CRUD, autentimine, kommentaarid ja admini haldus.</p>
                    <span class="mt-3 inline-block text-primary-600 text-sm font-medium">Ava →</span>
                </Link>

                <Link :href="route('shop.index')" class="card p-6 hover:shadow-lg transition-shadow group">
                    <div class="text-4xl mb-3">🛒</div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">E-pood</h3>
                    <p class="text-sm text-gray-500">12 toodet, ostukorv, Stripe maksed, tellimuste haldus andmebaasis.</p>
                    <span class="mt-3 inline-block text-primary-600 text-sm font-medium">Ava →</span>
                </Link>

                <Link :href="route('api-explorer.index')" class="card p-6 hover:shadow-lg transition-shadow group">
                    <div class="text-4xl mb-3">🎬</div>
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">Filmide API</h3>
                    <p class="text-sm text-gray-500">JSON REST API filmidele. Filtreerimine, sorteerimine, otsing, vahemälu, limit parameeter.</p>
                    <span class="mt-3 inline-block text-primary-600 text-sm font-medium">Ava →</span>
                </Link>

                <div class="card p-6 bg-gray-50">
                    <div class="text-4xl mb-3">📡</div>
                    <h3 class="font-bold text-gray-700 mb-2">JSON API</h3>
                    <div class="space-y-1 text-xs font-mono text-gray-500">
                        <p>GET /api/v1/films</p>
                        <p>GET /api/v1/markers</p>
                        <p>GET /api/v1/weather?city=...</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({ stats: Object });
</script>
