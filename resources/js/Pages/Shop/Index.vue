<template>
    <AppLayout>
        <Head title="E-pood" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🛒 Eesti Käsitöö Pood</h1>
                    <p class="text-gray-500 mt-1">{{ products.length }} toodet</p>
                </div>
                <!-- Cart button -->
                <button
                    @click="cartOpen = true"
                    class="relative btn-primary"
                >
                    🛒 Ostukorv
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"
                    >{{ cartCount }}</span>
                </button>
            </div>

            <!-- Category filter -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    @click="activeCategory = null"
                    :class="['px-4 py-1.5 rounded-full text-sm font-medium transition', !activeCategory ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50']"
                >
                    Kõik
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat"
                    @click="activeCategory = cat"
                    :class="['px-4 py-1.5 rounded-full text-sm font-medium transition', activeCategory === cat ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50']"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Products grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="card hover:shadow-lg transition-shadow group"
                >
                    <div class="h-52 overflow-hidden bg-gray-100">
                        <img
                            :src="product.image"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <div class="p-4">
                        <span class="text-xs text-primary-600 font-medium bg-primary-50 px-2 py-0.5 rounded-full">{{ product.category }}</span>
                        <h3 class="font-semibold text-gray-900 mt-2 mb-1">{{ product.name }}</h3>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ product.description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-gray-900">{{ product.price.toFixed(2) }} €</span>
                            <!-- Qty + Add -->
                            <div class="flex items-center gap-2">
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                    <button @click="decQty(product.id)" class="px-2 py-1 text-gray-600 hover:bg-gray-100 text-sm">−</button>
                                    <span class="px-2 py-1 text-sm font-medium min-w-[28px] text-center">{{ getQty(product.id) }}</span>
                                    <button @click="incQty(product.id)" class="px-2 py-1 text-gray-600 hover:bg-gray-100 text-sm">+</button>
                                </div>
                                <button
                                    @click="addToCart(product)"
                                    class="btn-primary text-xs px-3 py-2"
                                >
                                    Lisa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Sidebar -->
        <Teleport to="body">
            <div v-if="cartOpen" class="fixed inset-0 z-50 flex">
                <div class="flex-1 bg-black/50" @click="cartOpen = false"></div>
                <div class="w-full max-w-md bg-white shadow-2xl flex flex-col">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">🛒 Ostukorv</h2>
                        <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl">✕</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6">
                        <div v-if="cart.length === 0" class="text-center text-gray-400 py-12">
                            <p class="text-5xl mb-3">🛒</p>
                            <p>Ostukorv on tühi</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="item in cart"
                                :key="item.id"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl"
                            >
                                <img :src="item.image" :alt="item.name" class="w-16 h-16 object-cover rounded-lg" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-900 truncate">{{ item.name }}</p>
                                    <p class="text-sm text-gray-500">{{ item.price.toFixed(2) }} € tk</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <button @click="changeCartQty(item.id, item.qty - 1)" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-sm hover:bg-gray-300">−</button>
                                        <span class="text-sm font-medium">{{ item.qty }}</span>
                                        <button @click="changeCartQty(item.id, item.qty + 1)" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-sm hover:bg-gray-300">+</button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-sm">{{ (item.price * item.qty).toFixed(2) }} €</p>
                                    <button @click="removeFromCart(item.id)" class="text-red-400 hover:text-red-600 text-xs mt-1">Eemalda</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="cart.length > 0" class="p-6 border-t border-gray-200">
                        <div class="flex justify-between text-lg font-bold mb-4">
                            <span>Kokku:</span>
                            <span>{{ cartTotal.toFixed(2) }} €</span>
                        </div>
                        <Link :href="route('shop.checkout')" @click="saveCartToStorage" class="btn-primary w-full text-center block">
                            Jätka kassasse →
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ products: Array, stripeKey: String });

const cartOpen = ref(false);
const activeCategory = ref(null);
const quantities = ref({});

// Initialize cart from sessionStorage — safe fallback if empty/corrupt
function loadCartFromStorage() {
    try {
        return JSON.parse(sessionStorage.getItem('cart') || '[]');
    } catch {
        return [];
    }
}

const cart = ref(loadCartFromStorage());

const categories = computed(() => [...new Set(props.products.map(p => p.category))].sort());
const filteredProducts = computed(() =>
    activeCategory.value ? props.products.filter(p => p.category === activeCategory.value) : props.products
);
const cartCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0));
const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.price * i.qty, 0));

function getQty(id) { return quantities.value[id] ?? 1; }
function incQty(id) { quantities.value[id] = getQty(id) + 1; }
function decQty(id) { if (getQty(id) > 1) quantities.value[id] = getQty(id) - 1; }

function addToCart(product) {
    const qty = getQty(product.id);
    const existing = cart.value.find(i => i.id === product.id);
    if (existing) {
        existing.qty += qty;
    } else {
        cart.value.push({ ...product, qty });
    }
    // Reset per-card quantity selector back to 1
    quantities.value[product.id] = 1;
    saveCartToStorage();
    cartOpen.value = true;
}

function changeCartQty(id, newQty) {
    if (newQty <= 0) { removeFromCart(id); return; }
    const item = cart.value.find(i => i.id === id);
    if (item) { item.qty = newQty; saveCartToStorage(); }
}

function removeFromCart(id) {
    cart.value = cart.value.filter(i => i.id !== id);
    saveCartToStorage();
}

function saveCartToStorage() {
    sessionStorage.setItem('cart', JSON.stringify(cart.value));
}
</script>