<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <Link :href="route('home')" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">H</span>
                            </div>
                            <span class="font-semibold text-gray-900 hidden sm:block">Hajusrakendus</span>
                        </Link>

                        <!-- Nav Links -->
                        <div class="hidden md:flex ml-8 space-x-1">
                            <NavLink :href="route('weather.index')" :active="route().current('weather.*')">
                                🌤️ Ilm
                            </NavLink>
                            <NavLink :href="route('map.index')" :active="route().current('map.*')">
                                🗺️ Kaart
                            </NavLink>
                            <NavLink :href="route('blog.index')" :active="route().current('blog.*')">
                                📝 Blogi
                            </NavLink>
                            <NavLink :href="route('shop.index')" :active="route().current('shop.*')">
                                🛒 Pood
                            </NavLink>
                            <NavLink :href="route('api-explorer.index')" :active="route().current('api-explorer.*')">
                                🎬 Filmid API
                            </NavLink>
                        </div>
                    </div>

                    <!-- Auth -->
                    <div class="flex items-center space-x-3">
                        <template v-if="$page.props.auth.user">
                            <span class="text-sm text-gray-600 hidden sm:block">
                                {{ $page.props.auth.user.name }}
                            </span>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded-md hover:bg-gray-100 transition"
                            >
                                Logi välja
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1 rounded-md hover:bg-gray-100 transition"
                            >
                                Logi sisse
                            </Link>
                            <Link
                                :href="route('register')"
                                class="btn-primary text-xs"
                            >
                                Registreeru
                            </Link>
                        </template>

                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-show="mobileMenuOpen" class="md:hidden border-t border-gray-200 py-2">
                <div class="px-4 space-y-1">
                    <MobileNavLink :href="route('weather.index')">🌤️ Ilm</MobileNavLink>
                    <MobileNavLink :href="route('map.index')">🗺️ Kaart</MobileNavLink>
                    <MobileNavLink :href="route('blog.index')">📝 Blogi</MobileNavLink>
                    <MobileNavLink :href="route('shop.index')">🛒 Pood</MobileNavLink>
                    <MobileNavLink :href="route('api-explorer.index')">🎬 Filmid API</MobileNavLink>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                v-if="$page.props.flash?.success"
                class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between"
            >
                <span>✅ {{ $page.props.flash.success }}</span>
                <button @click="dismissFlash" class="text-green-600 hover:text-green-800">✕</button>
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between"
            >
                <span>❌ {{ $page.props.flash.error }}</span>
                <button @click="dismissFlash" class="text-red-600 hover:text-red-800">✕</button>
            </div>
        </div>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">🌤️ Ilm</h3>
                        <p class="text-xs text-gray-500">Reaalajas ilmaandmed OpenWeatherMap API kaudu.</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">🗺️ Kaart</h3>
                        <p class="text-xs text-gray-500">Markerite haldus Radar Maps kaardil.</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">📝 Blogi</h3>
                        <p class="text-xs text-gray-500">Blogipostitused koos kommentaaridega.</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">🛒 Pood</h3>
                        <p class="text-xs text-gray-500">E-pood Stripe makselahendusega.</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">🎬 Filmid API</h3>
                        <p class="text-xs text-gray-500">JSON API filmide haldamiseks.</p>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
                    © {{ new Date().getFullYear() }} Hajusrakendus · Laravel 13 + Vue 3 + Inertia.js
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import MobileNavLink from '@/Components/MobileNavLink.vue';

const mobileMenuOpen = ref(false);
const flashVisible = ref(true);

function dismissFlash() {
    flashVisible.value = false;
}
</script>
