<template>
    <AppLayout>
        <Head title="Blogi" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📝 Blogi</h1>
                    <p class="text-gray-500 mt-1">{{ posts.total }} postitust</p>
                </div>
                <!-- Only logged-in users can add posts -->
                <Link v-if="$page.props.auth.user" :href="route('blog.create')" class="btn-primary">
                    ✏️ Uus postitus
                </Link>
            </div>

            <!-- Search -->
            <div class="card p-4 mb-8">
                <form @submit.prevent="doSearch" class="flex gap-3">
                    <input v-model="searchInput" type="text" placeholder="Otsi postitusi..." class="form-input flex-1" />
                    <button type="submit" class="btn-primary">Otsi</button>
                    <button v-if="search" type="button" @click="clearSearch" class="btn-secondary">Tühista</button>
                </form>
            </div>

            <!-- Posts grid -->
            <div v-if="posts.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <article
                    v-for="post in posts.data"
                    :key="post.id"
                    class="card hover:shadow-md transition-shadow group flex flex-col"
                >
                    <div v-if="post.image" class="h-48 overflow-hidden shrink-0">
                        <img :src="post.image" :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div v-else class="h-48 bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center shrink-0">
                        <span class="text-6xl">📄</span>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                            <span>👤 {{ post.user?.name }}</span>
                            <span>·</span>
                            <span>{{ formatDate(post.created_at) }}</span>
                            <span v-if="post.approved_comments_count">· 💬 {{ post.approved_comments_count }}</span>
                        </div>
                        <h2 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            {{ post.title }}
                        </h2>
                        <p class="text-gray-600 text-sm line-clamp-3 mb-4 flex-1">{{ post.description }}</p>

                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100">
                            <Link :href="route('blog.show', post.slug)" class="text-primary-600 text-sm font-medium hover:underline">
                                Loe edasi →
                            </Link>
                            <!-- Author sees edit button for own posts -->
                            <Link
                                v-if="isOwnPost(post)"
                                :href="route('blog.edit', post.slug)"
                                class="text-xs text-blue-500 hover:text-blue-700 px-2 py-1 bg-blue-50 rounded transition"
                                @click.stop
                            >✏️ Muuda</Link>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="text-center py-16 text-gray-400">
                <p class="text-5xl mb-4">📭</p>
                <p class="text-lg">{{ search ? 'Otsing ei andnud tulemusi.' : 'Postitusi pole veel.' }}</p>
                <Link v-if="$page.props.auth.user && !search" :href="route('blog.create')" class="btn-primary mt-4 inline-block">
                    Lisa esimene postitus
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="posts.last_page > 1" class="flex justify-center gap-2 flex-wrap">
                <button
                    v-for="p in posts.last_page" :key="p"
                    @click="goToPage(p)"
                    :class="['px-4 py-2 rounded-lg text-sm font-medium transition', p === posts.current_page ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50']"
                >{{ p }}</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ posts: Object, search: String });
const page = usePage();
const searchInput = ref(props.search || '');

function isOwnPost(post) {
    const user = page.props.auth.user;
    return user && user.id === post.user_id;
}

function doSearch() {
    router.get(route('blog.index'), { search: searchInput.value }, { preserveState: true });
}

function clearSearch() {
    searchInput.value = '';
    router.get(route('blog.index'));
}

function goToPage(p) {
    router.get(route('blog.index'), { page: p, search: props.search });
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('et-EE', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>