<template>
    <AppLayout>
        <Head title="Lisa film" />

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <Link :href="route('api-explorer.index')" class="text-sm text-gray-500 hover:text-gray-700">← Tagasi filmide lehele</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">🎬 Lisa uus film</h1>
            </div>

            <div class="card p-8">
                <form @submit.prevent="submit" class="space-y-5">
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
                            <input v-model="form.genre" type="text" class="form-input" placeholder="Nt Põnevik, Draama, Komöödia" list="genre-suggestions" />
                            <datalist id="genre-suggestions">
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
                            <div v-if="form.image" class="mt-2 h-40 rounded-lg overflow-hidden">
                                <img :src="form.image" alt="Eelvaade" class="w-full h-full object-cover" @error="form.image = ''" />
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="form-label">Kirjeldus *</label>
                            <textarea v-model="form.description" rows="5" class="form-input" placeholder="Filmi lühikirjeldus..." required></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <Link :href="route('api-explorer.index')" class="btn-secondary flex-1 text-center">Tühista</Link>
                        <button type="submit" class="btn-primary flex-1" :disabled="form.processing">
                            {{ form.processing ? 'Salvestab...' : '🎬 Lisa film' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    title: '', director: '', year: new Date().getFullYear(),
    genre: '', rating: '', image: '', description: '',
});

const genreSuggestions = [
    'Action', 'Draama', 'Komöödia', 'Põnevik', 'Õudus',
    'Romantika', 'Sci-Fi', 'Animatsioon', 'Dokumentaal', 'Seiklus',
];

function submit() {
    form.post(route('api-explorer.store'));
}
</script>
