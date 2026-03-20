<template>
    <AppLayout>
        <Head title="Registreeru" />

        <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl font-bold">H</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Loo konto</h1>
                    <p class="text-gray-500 mt-1">Liitu Hajusrakendusega</p>
                </div>

                <div class="card p-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="form-label">Nimi</label>
                            <input v-model="form.name" type="text" class="form-input" required autocomplete="name" />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="form-label">E-post</label>
                            <input v-model="form.email" type="email" class="form-input" required autocomplete="email" />
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="form-label">Parool</label>
                            <input v-model="form.password" type="password" class="form-input" required autocomplete="new-password" />
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="form-label">Korda parooli</label>
                            <input v-model="form.password_confirmation" type="password" class="form-input" required />
                        </div>

                        <button type="submit" class="btn-primary w-full py-2.5" :disabled="form.processing">
                            {{ form.processing ? 'Loob kontot...' : 'Registreeru' }}
                        </button>
                    </form>

                    <p class="text-center text-sm text-gray-500 mt-6">
                        On juba konto?
                        <Link :href="route('login')" class="text-primary-600 font-medium hover:underline">Logi sisse</Link>
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
function submit() { form.post(route('register')); }
</script>
