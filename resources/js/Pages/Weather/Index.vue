<template>
    <AppLayout>
        <Head title="Ilmateenistus" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">🌤️ Ilmateenistus</h1>
                <p class="text-gray-500">Reaalajas ilmaandmed OpenWeatherMap API kaudu · Vahemälu 30 min</p>
            </div>

            <!-- Search Bar -->
            <div class="card p-6 mb-8">
                <form @submit.prevent="searchWeather" class="flex gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="searchCity"
                            type="text"
                            placeholder="Sisesta linna nimi (nt Tallinn, London, Tokyo)..."
                            class="form-input pl-10"
                            list="popular-cities"
                        />
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                        <datalist id="popular-cities">
                            <option v-for="city in popularCities" :key="city" :value="city" />
                        </datalist>
                    </div>
                    <button type="submit" class="btn-primary">
                        Otsi ilma
                    </button>
                </form>

                <!-- Popular cities quick select -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="text-xs text-gray-500 mr-1 self-center">Populaarsed:</span>
                    <button
                        v-for="city in popularCities"
                        :key="city"
                        @click="goToCity(city)"
                        :class="[
                            'px-3 py-1 rounded-full text-xs font-medium transition',
                            currentCity === city
                                ? 'bg-primary-100 text-primary-700 border border-primary-300'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        ]"
                    >
                        {{ city }}
                    </button>
                </div>
            </div>

            <!-- Error -->
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-8 flex items-center gap-3">
                <span class="text-2xl">❌</span>
                <div>
                    <p class="font-medium">Viga ilmaandmete hankimisel</p>
                    <p class="text-sm">{{ error }}</p>
                </div>
            </div>

            <!-- Current Weather -->
            <div v-if="weather" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                <!-- Main weather card -->
                <div class="lg:col-span-2 card overflow-hidden">
                    <div :class="['p-8 bg-gradient-to-br', weatherGradient, 'text-white']">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-4xl font-bold mb-1">{{ weather.city }}, {{ weather.country }}</h2>
                                <p class="text-white/80 text-lg capitalize mb-4">{{ weather.description }}</p>
                                <div class="flex items-end gap-4">
                                    <span class="text-7xl font-light">{{ weather.temp }}°</span>
                                    <div class="text-white/80 mb-2">
                                        <p class="text-sm">Tundub nagu {{ weather.feels_like }}°</p>
                                        <p class="text-sm">↑ {{ weather.temp_max }}° ↓ {{ weather.temp_min }}°</p>
                                    </div>
                                </div>
                            </div>
                            <img
                                :src="weather.icon_url"
                                :alt="weather.description"
                                class="w-24 h-24 filter drop-shadow-lg"
                            />
                        </div>
                    </div>

                    <!-- Details grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y divide-gray-100">
                        <div class="p-4 text-center">
                            <p class="text-2xl mb-1">💧</p>
                            <p class="text-xl font-semibold text-gray-900">{{ weather.humidity }}%</p>
                            <p class="text-xs text-gray-500">Niiskus</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-2xl mb-1">💨</p>
                            <p class="text-xl font-semibold text-gray-900">{{ weather.wind_speed }} m/s</p>
                            <p class="text-xs text-gray-500">Tuul</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-2xl mb-1">🌡️</p>
                            <p class="text-xl font-semibold text-gray-900">{{ weather.pressure }} hPa</p>
                            <p class="text-xs text-gray-500">Õhurõhk</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-2xl mb-1">👁️</p>
                            <p class="text-xl font-semibold text-gray-900">{{ weather.visibility }} km</p>
                            <p class="text-xs text-gray-500">Nähtavus</p>
                        </div>
                    </div>
                </div>

                <!-- Sun times & extra info -->
                <div class="space-y-4">
                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-700 mb-4">☀️ Päevavalgus</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">🌅 Päikesetõus</span>
                                <span class="font-semibold">{{ weather.sunrise }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">🌇 Päikeseloojang</span>
                                <span class="font-semibold">{{ weather.sunset }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card p-6">
                        <h3 class="font-semibold text-gray-700 mb-4">📍 Koordinaadid</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-sm">Laiuskraad</span>
                                <span class="font-mono text-sm">{{ weather.lat }}°</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-sm">Pikkuskraad</span>
                                <span class="font-mono text-sm">{{ weather.lon }}°</span>
                            </div>
                        </div>
                    </div>

                    <div class="card p-4 bg-blue-50 border-blue-200">
                        <p class="text-xs text-blue-600 flex items-center gap-1">
                            <span>🔄</span>
                            <span>Andmed uuendatakse iga 30 minuti tagant (vahemälu)</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 5-day Forecast -->
            <div v-if="forecast" class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">📅 5-päeva prognoos</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div
                        v-for="day in forecast"
                        :key="day.date"
                        class="bg-gradient-to-b from-blue-50 to-indigo-50 rounded-xl p-4 text-center border border-blue-100"
                    >
                        <p class="text-xs font-medium text-gray-600 mb-2 leading-tight">{{ day.date_label }}</p>
                        <img :src="day.icon_url" :alt="day.description" class="w-12 h-12 mx-auto my-2" />
                        <p class="text-xs text-gray-500 capitalize mb-2">{{ day.description }}</p>
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold text-red-500">{{ day.temp_max }}°</span>
                            <span class="text-blue-400">{{ day.temp_min }}°</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-400 space-y-0.5">
                            <p>💧 {{ day.humidity }}%</p>
                            <p>💨 {{ day.wind_speed }} m/s</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    weather: Object,
    forecast: Array,
    currentCity: String,
    popularCities: Array,
    error: String,
});

const searchCity = ref(props.currentCity);

function searchWeather() {
    if (searchCity.value.trim()) {
        router.get(route('weather.index'), { city: searchCity.value.trim() });
    }
}

function goToCity(city) {
    searchCity.value = city;
    router.get(route('weather.index'), { city });
}

const weatherGradient = computed(() => {
    if (!props.weather) return 'from-blue-500 to-blue-700';
    const icon = props.weather.icon;
    if (icon.includes('01')) return 'from-yellow-400 to-orange-500'; // selge
    if (icon.includes('02') || icon.includes('03')) return 'from-blue-400 to-blue-600'; // osapilves
    if (icon.includes('04')) return 'from-gray-400 to-gray-600'; // pilves
    if (icon.includes('09') || icon.includes('10')) return 'from-blue-600 to-indigo-800'; // vihm
    if (icon.includes('11')) return 'from-gray-700 to-gray-900'; // äike
    if (icon.includes('13')) return 'from-blue-200 to-blue-400'; // lumi
    if (icon.includes('50')) return 'from-gray-300 to-gray-500'; // udu
    return 'from-blue-500 to-blue-700';
});
</script>
