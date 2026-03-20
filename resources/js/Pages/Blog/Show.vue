<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <Link :href="route('blog.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
                ← Tagasi blogilehele
            </Link>

            <!-- Post -->
            <article class="card overflow-hidden mb-8">
                <div v-if="post.image" class="h-72 overflow-hidden">
                    <img :src="post.image" :alt="post.title" class="w-full h-full object-cover" />
                </div>

                <div class="p-8">
                    <div class="flex items-center gap-3 text-sm text-gray-400 mb-4">
                        <span class="font-medium text-gray-600">👤 {{ post.user?.name }}</span>
                        <span>·</span>
                        <span>{{ formatDate(post.created_at) }}</span>
                        <span v-if="post.updated_at !== post.created_at" class="text-xs italic">
                            · Muudetud: {{ formatDate(post.updated_at) }}
                        </span>
                    </div>

                    <h1 class="text-4xl font-bold text-gray-900 mb-6 leading-tight">{{ post.title }}</h1>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap text-base">{{ post.description }}</div>

                    <!-- Author: edit own post -->
                    <div v-if="isAuthor" class="flex gap-3 mt-8 pt-6 border-t border-gray-100">
                        <Link :href="route('blog.edit', post.slug)" class="btn-secondary text-xs">
                            ✏️ Muuda postitust
                        </Link>
                    </div>
                </div>
            </article>

            <!-- Comments -->
            <div class="card p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    💬 Kommentaarid ({{ localComments.length }})
                </h2>

                <div v-if="localComments.length" class="space-y-4 mb-8">
                    <div
                        v-for="comment in localComments"
                        :key="comment.id"
                        class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-sm shrink-0">
                                    {{ comment.author_name[0].toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium text-sm text-gray-900">{{ comment.author_name }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</p>
                                </div>
                            </div>
                            <!-- Admin only: delete comment button -->
                            <button
                                v-if="isAdmin"
                                @click="deleteComment(comment.id)"
                                class="shrink-0 text-xs text-red-400 hover:text-red-600 px-2 py-1 bg-red-50 hover:bg-red-100 rounded transition"
                            >🗑️ Kustuta</button>
                        </div>
                        <p class="text-gray-700 text-sm pl-12 leading-relaxed">{{ comment.body }}</p>
                    </div>
                </div>

                <div v-else class="text-center text-gray-400 py-8 mb-4">
                    <p class="text-3xl mb-2">💬</p>
                    <p class="text-sm">Ole esimene kommenteerija!</p>
                </div>

                <!-- Comment form - available to everyone including guests -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Lisa kommentaar</h3>
                    <form @submit.prevent="submitComment" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Nimi *</label>
                                <input v-model="commentForm.author_name" type="text" class="form-input" required />
                                <p v-if="commentErrors.author_name" class="text-red-500 text-xs mt-1">{{ commentErrors.author_name[0] }}</p>
                            </div>
                            <div>
                                <label class="form-label">E-post *</label>
                                <input v-model="commentForm.author_email" type="email" class="form-input" required />
                                <p v-if="commentErrors.author_email" class="text-red-500 text-xs mt-1">{{ commentErrors.author_email[0] }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Kommentaar *</label>
                            <textarea v-model="commentForm.body" rows="4" class="form-input" required placeholder="Kirjuta oma kommentaar..."></textarea>
                            <p v-if="commentErrors.body" class="text-red-500 text-xs mt-1">{{ commentErrors.body[0] }}</p>
                        </div>
                        <p v-if="commentErrors.general" class="text-red-500 text-sm bg-red-50 p-3 rounded-lg">{{ commentErrors.general }}</p>
                        <button type="submit" class="btn-primary" :disabled="submitting">
                            {{ submitting ? 'Saadan...' : 'Lisa kommentaar' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ post: Object });
const page = usePage();

const localComments = ref([...(props.post.approved_comments ?? [])]);

// Author: can edit own post
const isAuthor = computed(() => {
    const user = page.props.auth.user;
    return user && user.id === props.post.user_id;
});

// Admin: can delete comments
const isAdmin = computed(() => page.props.auth.user?.is_admin === true);

const commentForm = ref({
    author_name:  page.props.auth.user?.name  ?? '',
    author_email: page.props.auth.user?.email ?? '',
    body: '',
});
const commentErrors = ref({});
const submitting = ref(false);

function formatDate(date) {
    return new Date(date).toLocaleDateString('et-EE', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function submitComment() {
    commentErrors.value = {};
    submitting.value = true;

    try {
        const res = await fetch(`/blogi/${props.post.slug}/kommentaar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(commentForm.value),
        });

        const data = await res.json();

        if (res.ok && data.success) {
            // Instantly add comment to list without page refresh
            localComments.value.push(data.comment);
            commentForm.value.body = '';
        } else if (data.errors) {
            commentErrors.value = data.errors;
        } else {
            commentErrors.value = { general: data.message ?? 'Kommentaari lisamine ebaõnnestus.' };
        }
    } catch (e) {
        commentErrors.value = { general: 'Võrgu viga. Proovi uuesti.' };
    } finally {
        submitting.value = false;
    }
}

async function deleteComment(id) {
    if (!confirm('Kustuta see kommentaar?')) return;

    const res = await fetch(`/kommentaar/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json',
        },
    });

    if (res.ok) {
        // Instantly remove from list
        localComments.value = localComments.value.filter(c => c.id !== id);
    }
}
</script>