<template>
    <AppLayout>
        <Head title="Logi sisse" />

        <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl font-bold">H</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Logi sisse</h1>
                    <p class="text-gray-500 mt-1">Hajusrakenduse kasutajakonto</p>
                </div>

                <div class="card p-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="form-label">E-post</label>
                            <input v-model="form.email" type="email" class="form-input" required autocomplete="email" />
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="form-label">Parool</label>
                            <input v-model="form.password" type="password" class="form-input" required autocomplete="current-password" />
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input v-model="form.remember" type="checkbox" id="remember" class="rounded border-gray-300 text-primary-600" />
                            <label for="remember" class="text-sm text-gray-600">Jäta mind meelde</label>
                        </div>

                        <button type="submit" class="btn-primary w-full py-2.5" :disabled="form.processing">
                            {{ form.processing ? 'Sisselogimine...' : 'Logi sisse' }}
                        </button>
                    </form>

                    <p class="text-center text-sm text-gray-500 mt-6">
                        Pole kontot?
                        <Link :href="route('register')" class="text-primary-600 font-medium hover:underline">Registreeru siin</Link>
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({ email: '', password: '', remember: false });
function submit() { form.post(route('login')); }
</script>
