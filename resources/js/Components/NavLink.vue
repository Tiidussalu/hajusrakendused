<template>
    <Link
        :href="href"
        :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150',
            active
                ? 'bg-primary-50 text-primary-700'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
        ]"
    >
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    active: Boolean,
});
</script>
