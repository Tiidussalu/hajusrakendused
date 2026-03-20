<template>
    <AppLayout>
        <Head title="Tellimus kinnitatud" />

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <div class="card p-12">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">✅</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Makse õnnestus!</h1>
                <p class="text-gray-500 mb-8">Täname ostu eest, {{ order.first_name }}! Tellimus on vastu võetud.</p>

                <div class="bg-gray-50 rounded-xl p-6 text-left mb-8">
                    <h2 class="font-semibold text-gray-700 mb-4">📋 Tellimuse kokkuvõte</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tellimuse nr:</span>
                            <span class="font-mono font-medium">#{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nimi:</span>
                            <span>{{ order.first_name }} {{ order.last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">E-post:</span>
                            <span>{{ order.email }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-base mt-3 pt-3 border-t border-gray-200">
                            <span>Kokku makstud:</span>
                            <span>{{ Number(order.total).toFixed(2) }} €</span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="text-left mb-8">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm">Ostetud tooted:</h3>
                    <div class="space-y-2">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm text-gray-600">
                            <span>{{ item.name }} × {{ item.qty }}</span>
                            <span>{{ (item.price * item.qty).toFixed(2) }} €</span>
                        </div>
                    </div>
                </div>

                <Link :href="route('shop.index')" class="btn-primary">
                    ← Tagasi poodi
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({ order: Object });
</script>
