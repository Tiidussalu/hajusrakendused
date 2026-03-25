<template>
    <AppLayout>
        <Head title="API Võti" />

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-6">
                <Link :href="route('api-explorer.index')" class="text-sm text-gray-500 hover:text-gray-700">← Tagasi filmide lehele</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">🔑 Minu API Võti</h1>
                <p class="text-gray-500 mt-1">Kasuta seda võtit filmide API päringutes.</p>
            </div>

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

            <!-- Token status card -->
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
                    <form @submit.prevent="generate">
                        <button type="submit" class="btn-primary">
                            {{ hasToken ? '🔄 Regenereeri võti' : '🔑 Genereeri võti' }}
                        </button>
                    </form>
                    <form v-if="hasToken" @submit.prevent="revoke">
                        <button
                            type="submit"
                            class="btn-secondary text-red-600 hover:bg-red-50 border-red-200"
                            onclick="return confirm('Kas oled kindel? Vana võti lakkab kohe töötamast.')"
                        >
                            🗑️ Tühista võti
                        </button>
                    </form>
                </div>
            </div>

            <!-- How to use -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📖 Kuidas kasutada</h2>

                <p class="text-sm text-gray-600 mb-4">Lisa oma API võti igale päringule kas päisena või query parameetrina:</p>

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
                            <p>&nbsp;&nbsp;headers: { <span class="text-green-400">'Authorization'</span>: <span class="text-green-400">`Bearer <span class="text-yellow-300">${'$'}{apiToken}</span>`</span> }</p>
                            <p>});</p>
                            <p><span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> res.json();</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Saadaval otspunktid</p>
                        <div class="space-y-2 text-sm font-mono">
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">GET</span>
                                <span class="text-gray-700">/api/v1/films</span>
                                <span class="text-gray-400 text-xs ml-auto">?search= &amp;genre= &amp;sort= &amp;limit=</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">GET</span>
                                <span class="text-gray-700">/api/v1/films/{id}</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">GET</span>
                                <span class="text-gray-700">/api/v1/markers</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">GET</span>
                                <span class="text-gray-700">/api/v1/weather</span>
                                <span class="text-gray-400 text-xs ml-auto">?city=Tallinn</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    hasToken: Boolean,
    newToken: String,
});

const copied = ref(false);
const baseUrl = computed(() => window.location.origin);

async function copyToken(token) {
    try {
        await navigator.clipboard.writeText(token);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    } catch {
        // Fallback for older browsers
        const el = document.createElement('textarea');
        el.value = token;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    }
}

function generate() {
    router.post(route('api.token.generate'));
}

function revoke() {
    router.post(route('api.token.revoke'));
}
</script>