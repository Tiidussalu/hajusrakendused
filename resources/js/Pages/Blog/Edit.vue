<template>
    <AppLayout>
        <Head title="Muuda postitust" />
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <Link :href="route('blog.show', post.slug)" class="text-sm text-gray-500 hover:text-gray-700">← Tagasi</Link>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">✏️ Muuda postitust</h1>
            </div>
            <PostForm :action="route('blog.update', post.slug)" method="put" :initial="post" />
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PostForm from '@/Components/PostForm.vue';

defineProps({ post: Object });
</script>