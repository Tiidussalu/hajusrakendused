<template>
    <AppLayout>
        <Head title="Kaardirakendus" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🗺️ Kaardirakendus</h1>
                    <p class="text-gray-500 mt-1">Klõpsa kaardil markeri lisamiseks · {{ localMarkers.length }} markerit · OpenStreetMap</p>
                </div>
                <button @click="openAddModal()" class="btn-primary">
                    ➕ Lisa marker
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Map -->
                <div class="lg:col-span-2">
                    <div class="card overflow-hidden">
                        <div id="map" class="w-full h-[560px]"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        © OpenStreetMap contributors · Klõpsa kaardil uue markeri lisamiseks
                    </p>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <div class="card p-4">
                        <h3 class="font-semibold text-gray-700 mb-3">📍 Markerid ({{ localMarkers.length }})</h3>

                        <div v-if="localMarkers.length === 0" class="text-center text-gray-400 py-8">
                            <p class="text-4xl mb-2">📌</p>
                            <p class="text-sm">Pole veel markereid. Klõpsa kaardil!</p>
                        </div>

                        <div v-else class="space-y-2 max-h-[460px] overflow-y-auto pr-1">
                            <div
                                v-for="marker in localMarkers"
                                :key="marker.id"
                                @click="focusMarker(marker)"
                                class="p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-blue-50 cursor-pointer transition group"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm text-gray-900 truncate">{{ marker.name }}</p>
                                        <p v-if="marker.description" class="text-xs text-gray-500 mt-0.5 truncate">
                                            {{ marker.description }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1 font-mono">
                                            {{ Number(marker.latitude).toFixed(4) }}, {{ Number(marker.longitude).toFixed(4) }}
                                        </p>
                                    </div>
                                    <div class="flex gap-1 ml-2 opacity-0 group-hover:opacity-100 transition">
                                        <button @click.stop="editMarker(marker)" class="p-1 text-blue-500 hover:text-blue-700 rounded">✏️</button>
                                        <button @click.stop="deleteMarker(marker)" class="p-1 text-red-500 hover:text-red-700 rounded">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ editingMarker ? '✏️ Muuda markerit' : '📍 Lisa uus marker' }}
                        </h2>
                    </div>
                    <form @submit.prevent="saveMarker" class="p-6 space-y-4">
                        <div>
                            <label class="form-label">Nimi *</label>
                            <input v-model="form.name" type="text" class="form-input" placeholder="Markeri nimi" required />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Laiuskraad *</label>
                                <input v-model="form.latitude" type="number" step="any" class="form-input font-mono" placeholder="59.4370" required />
                            </div>
                            <div>
                                <label class="form-label">Pikkuskraad *</label>
                                <input v-model="form.longitude" type="number" step="any" class="form-input font-mono" placeholder="24.7536" required />
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Kirjeldus</label>
                            <textarea v-model="form.description" rows="3" class="form-input" placeholder="Valikuline kirjeldus..."></textarea>
                        </div>
                        <div v-if="formErrors.length" class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                            <p v-for="err in formErrors" :key="err">{{ err }}</p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeModal" class="btn-secondary flex-1">Tühista</button>
                            <button type="submit" class="btn-primary flex-1" :disabled="saving">
                                {{ saving ? 'Salvestab...' : (editingMarker ? 'Uuenda' : 'Lisa marker') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    markers: Array,
});

const showModal = ref(false);
const editingMarker = ref(null);
const saving = ref(false);
const formErrors = ref([]);

// Local reactive markers list — no page reload needed
const localMarkers = ref([...props.markers]);

let mapInstance = null;
let leafletInstance = null;
let leafletMarkers = {};

const form = reactive({
    name: '',
    latitude: '',
    longitude: '',
    description: '',
});

function resetForm() {
    form.name = '';
    form.latitude = '';
    form.longitude = '';
    form.description = '';
    formErrors.value = [];
}

function openAddModal(lat = '', lng = '') {
    editingMarker.value = null;
    resetForm();
    form.latitude = lat;
    form.longitude = lng;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingMarker.value = null;
    resetForm();
}

function editMarker(marker) {
    editingMarker.value = marker;
    form.name = marker.name;
    form.latitude = marker.latitude;
    form.longitude = marker.longitude;
    form.description = marker.description || '';
    showModal.value = true;
}

async function saveMarker() {
    saving.value = true;
    formErrors.value = [];

    const isEdit = !!editingMarker.value;
    const url = isEdit ? `/map/markers/${editingMarker.value.id}` : '/map/markers';

    try {
        const response = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: form.name,
                latitude: parseFloat(form.latitude),
                longitude: parseFloat(form.longitude),
                description: form.description,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const savedMarker = data.marker;

            if (isEdit) {
                // Update local list
                const idx = localMarkers.value.findIndex(m => m.id === savedMarker.id);
                if (idx !== -1) localMarkers.value[idx] = savedMarker;

                // Update map popup
                const lm = leafletMarkers[savedMarker.id];
                if (lm) {
                    lm.setLatLng([savedMarker.latitude, savedMarker.longitude]);
                    lm.setPopupContent(buildPopupHtml(savedMarker));
                }
            } else {
                // Add to local list
                localMarkers.value.unshift(savedMarker);

                // Add to map immediately
                if (mapInstance && leafletInstance) {
                    addLeafletMarker(mapInstance, leafletInstance, savedMarker);
                    mapInstance.setView([savedMarker.latitude, savedMarker.longitude], 14);
                    leafletMarkers[savedMarker.id]?.openPopup();
                }
            }

            closeModal();
        } else if (data.errors) {
            formErrors.value = Object.values(data.errors).flat();
        } else {
            formErrors.value = ['Salvestamine ebaõnnestus.'];
        }
    } catch (e) {
        formErrors.value = ['Võrgu viga: ' + e.message];
    } finally {
        saving.value = false;
    }
}

async function deleteMarker(marker) {
    if (!confirm(`Kustuta marker "${marker.name}"?`)) return;
    try {
        const response = await fetch(`/map/markers/${marker.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            // Remove from local list
            localMarkers.value = localMarkers.value.filter(m => m.id !== marker.id);

            // Remove from map immediately
            if (leafletMarkers[marker.id]) {
                leafletMarkers[marker.id].remove();
                delete leafletMarkers[marker.id];
            }
        }
    } catch (e) {
        alert('Kustutamine ebaõnnestus.');
    }
}

function focusMarker(marker) {
    if (mapInstance) {
        mapInstance.setView([marker.latitude, marker.longitude], 14);
        leafletMarkers[marker.id]?.openPopup();
    }
}

function buildPopupHtml(markerData) {
    return `
        <div style="min-width:160px">
            <strong style="font-size:14px">${markerData.name}</strong>
            ${markerData.description ? `<br><span style="color:#666;font-size:12px">${markerData.description}</span>` : ''}
            <br><small style="color:#999">${new Date(markerData.added).toLocaleDateString('et-EE')}</small>
        </div>
    `;
}

function addLeafletMarker(map, L, markerData) {
    const lm = L.marker([markerData.latitude, markerData.longitude])
        .addTo(map)
        .bindPopup(L.popup().setContent(buildPopupHtml(markerData)));

    leafletMarkers[markerData.id] = lm;
    return lm;
}

onMounted(() => {
    leafletInstance = L;

    const map = L.map('map').setView([58.8, 25.0], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    mapInstance = map;

    localMarkers.value.forEach(m => addLeafletMarker(map, L, m));

    map.on('click', (e) => {
        openAddModal(
            e.latlng.lat.toFixed(7),
            e.latlng.lng.toFixed(7)
        );
    });
});
</script>