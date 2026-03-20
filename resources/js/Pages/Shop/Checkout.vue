<template>
    <AppLayout>
        <Head title="Kassa" />

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <Link :href="route('shop.index')" class="text-sm text-gray-500 hover:text-gray-700">← Tagasi poodi</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">💳 Kassa</h1>
            </div>

            <!-- Empty cart -->
            <div v-if="cart.length === 0" class="text-center py-20">
                <p class="text-5xl mb-4">🛒</p>
                <p class="text-xl text-gray-600 mb-4">Ostukorv on tühi</p>
                <Link :href="route('shop.index')" class="btn-primary">Mine poodi</Link>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                <!-- Left: forms -->
                <div class="lg:col-span-3 space-y-6">

                    <!-- Contact info -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 Kontaktandmed</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Eesnimi *</label>
                                <input v-model="form.first_name" type="text" class="form-input" required />
                            </div>
                            <div>
                                <label class="form-label">Perenimi *</label>
                                <input v-model="form.last_name" type="text" class="form-input" required />
                            </div>
                            <div>
                                <label class="form-label">E-post *</label>
                                <input v-model="form.email" type="email" class="form-input" required />
                            </div>
                            <div>
                                <label class="form-label">Telefon</label>
                                <input v-model="form.phone" type="tel" class="form-input" placeholder="+372 ..." />
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="card p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">💳 Makseteave</h2>
                        <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                            🔒 Turvaline makse Stripe kaudu · Testkaart: 4242 4242 4242 4242
                        </p>
                        <div id="stripe-card-element" class="p-3 border border-gray-300 rounded-lg bg-white min-h-[44px]"></div>
                        <p v-if="stripeError" class="text-red-500 text-sm mt-2">{{ stripeError }}</p>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="pay"
                        :disabled="paying || !stripeReady"
                        class="btn-primary w-full py-3 text-base"
                    >
                        <span v-if="paying" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Töötleb makset...
                        </span>
                        <span v-else>Maksa {{ cartTotal.toFixed(2) }} €</span>
                    </button>
                </div>

                <!-- Right: order summary -->
                <div class="lg:col-span-2">
                    <div class="card p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Tellimus</h2>
                        <div class="space-y-3 mb-4">
                            <div v-for="item in cart" :key="item.id" class="flex gap-3">
                                <img :src="item.image" :alt="item.name" class="w-12 h-12 object-cover rounded-lg" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">{{ item.qty }} × {{ item.price.toFixed(2) }} €</p>
                                </div>
                                <p class="text-sm font-semibold">{{ (item.price * item.qty).toFixed(2) }} €</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Vahesumma</span>
                                <span>{{ cartTotal.toFixed(2) }} €</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tarne</span>
                                <span class="text-green-600">Tasuta</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-100 mt-2">
                                <span>Kokku</span>
                                <span>{{ cartTotal.toFixed(2) }} €</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ stripeKey: String });

const cart = ref(JSON.parse(sessionStorage.getItem('cart') || '[]'));
const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.price * i.qty, 0));

const form = ref({ first_name: '', last_name: '', email: '', phone: '' });
const paying = ref(false);
const stripeError = ref('');
const stripeReady = ref(false);

let stripe = null;
let cardElement = null;

onMounted(async () => {
    if (cart.value.length === 0) return;

    // Load Stripe.js
    const script = document.createElement('script');
    script.src = 'https://js.stripe.com/v3/';
    script.onload = () => {
        stripe = Stripe(props.stripeKey || 'pk_test_placeholder');
        const elements = stripe.elements();
        cardElement = elements.create('card', {
            style: {
                base: { fontSize: '16px', color: '#374151', fontFamily: 'Inter, sans-serif' },
            },
        });
        cardElement.mount('#stripe-card-element');
        cardElement.on('ready', () => { stripeReady.value = true; });
        cardElement.on('change', (e) => { stripeError.value = e.error?.message || ''; });
    };
    document.head.appendChild(script);
});

async function pay() {
    if (!form.value.first_name || !form.value.last_name || !form.value.email) {
        stripeError.value = 'Palun täida kõik kohustuslikud väljad.';
        return;
    }

    paying.value = true;
    stripeError.value = '';

    try {
        // 1. Create PaymentIntent on server
        const res = await fetch(route('shop.payment-intent'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                ...form.value,
                amount: Math.round(cartTotal.value * 100),
                items: cart.value.map(i => ({
                    id: i.id, name: i.name, price: i.price, qty: i.qty,
                })),
            }),
        });

        const { clientSecret, orderId } = await res.json();

        // 2. Confirm with Stripe
        const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: `${form.value.first_name} ${form.value.last_name}`,
                    email: form.value.email,
                },
            },
        });

        if (error) {
            stripeError.value = error.message;
            paying.value = false;
            return;
        }

        if (paymentIntent.status === 'succeeded') {
            sessionStorage.removeItem('cart');
            router.get(route('shop.success'), { order_id: orderId });
        }
    } catch (e) {
        stripeError.value = 'Tehniline viga. Palun proovi uuesti.';
        paying.value = false;
    }
}
</script>
