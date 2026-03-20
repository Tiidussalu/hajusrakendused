<template>
    <div class="card p-8">
        <form @submit.prevent="submit" class="space-y-6">

            <div>
                <label class="form-label">Pealkiri *</label>
                <input v-model="form.title" type="text" class="form-input" placeholder="Postituse pealkiri..." required />
                <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="form-label">Pildi URL <span class="text-gray-400 font-normal">(valikuline)</span></label>
                <input
                    v-model="form.image"
                    type="text"
                    class="form-input"
                    placeholder="https://images.unsplash.com/photo-..."
                    @input="imageError = false"
                />
                <p v-if="form.errors.image" class="text-red-500 text-xs mt-1">{{ form.errors.image }}</p>
                <div v-if="form.image && !imageError" class="mt-2 h-48 rounded-xl overflow-hidden bg-gray-100">
                    <img
                        :src="form.image"
                        alt="Eelvaade"
                        class="w-full h-full object-cover"
                        @load="imageError = false"
                        @error="imageError = true"
                    />
                </div>
                <p v-if="imageError && form.image" class="text-amber-600 text-xs mt-1">
                    ⚠️ Pilti ei õnnestunud laadida — URL võib olla vale
                </p>
            </div>

            <div>
                <label class="form-label">Sisu *</label>
                <textarea
                    v-model="form.description"
                    rows="12"
                    class="form-input"
                    placeholder="Kirjuta postituse sisu siia..."
                    required
                ></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
            </div>

            <div class="flex items-center gap-3">
                <input v-model="form.published" type="checkbox" id="published" class="rounded border-gray-300 text-primary-600" />
                <label for="published" class="text-sm text-gray-700">Avalda kohe</label>
            </div>

            <div v-if="hasErrors" class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-1">
                <p v-for="(err, key) in form.errors" :key="key" class="text-red-600 text-sm">{{ err }}</p>
            </div>

            <button type="submit" class="btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Salvestab...' : (initial ? 'Uuenda postitus' : 'Avalda postitus') }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    action:  { type: String, required: true },
    method:  { type: String, default: 'post' },
    initial: { type: Object, default: null },
});

const imageError = ref(false);

const form = useForm({
    title:       props.initial?.title       ?? '',
    description: props.initial?.description ?? '',
    image:       props.initial?.image       ?? '',
    published:   props.initial?.published   ?? true,
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

function submit() {
    if (imageError.value) form.image = '';
    props.method === 'put' ? form.put(props.action) : form.post(props.action);
}
</script>