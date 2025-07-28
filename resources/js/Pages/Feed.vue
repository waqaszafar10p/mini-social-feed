<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="container mx-auto p-4">
            <!-- Create Post -->
            <Card class="mb-4">
                <template #title>What's on your mind?</template>
                <template #content>
                    <Textarea
                        v-model="newPost"
                        autoResize
                        rows="3"
                        class="mb-2 w-full"
                    />
                    <div
                        v-if="errors?.createPost?.content"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ errors.createPost.content }}
                    </div>
                    <Button
                        label="Post"
                        icon="pi pi-send"
                        class="w-full"
                        @click="submitPost"
                    />
                </template>
            </Card>
            <!-- Posts List -->
            <div v-for="post in posts" :key="post.id" class="mb-4">
                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span>{{ post.user.name }}</span>
                            <div v-if="post.user.id === authUser.id">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    @click="startEdit(post)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    @click="deletePost(post.id)"
                                />
                            </div>
                        </div>
                    </template>

                    <template #content>
                        <p>{{ post.content }}</p>
                        <div
                            class="mt-1 text-xs text-gray-500"
                            :title="
                                dayjs(post.created_at).format(
                                    'YYYY-MM-DD hh:mm A',
                                )
                            "
                        >
                            {{ dayjs(post.created_at).fromNow() }}
                        </div>
                        <!-- Likes -->
                        <div class="mt-2 flex items-center gap-2">
                            <Button
                                :icon="
                                    post.liked
                                        ? 'pi pi-heart-fill'
                                        : 'pi pi-heart'
                                "
                                text
                                rounded
                                severity="danger"
                                @click="toggleLike(post)"
                            />
                            <span>{{ post.likes_count }} like(s)</span>
                        </div>

                        <!-- Comments -->
                        <div
                            class="bg-surface-100 border-surface-200 rounded-lg border p-3 text-sm"
                            style="max-height: 200px; overflow-y: auto"
                        >
                            <div class="mt-2 flex gap-2">
                                <InputText
                                    v-model="commentInputs[post.id]"
                                    class="w-full"
                                    placeholder="Write a comment..."
                                />

                                <Button
                                    icon="pi pi-send"
                                    @click="addComment(post)"
                                />
                            </div>
                            <div
                                v-if="useCommentError(post.id).value"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ useCommentError(post.id).value }}
                            </div>
                            <div
                                v-for="comment in post.comments"
                                :key="comment.id"
                                class="mt-1 text-sm"
                            >
                                <strong>{{ comment.user.name }}</strong
                                >: {{ comment.content }}
                                <span
                                    class="ml-2 text-xs text-gray-400"
                                    :title="
                                        dayjs(comment.created_at).format(
                                            'YYYY-MM-DD hh:mm A',
                                        )
                                    "
                                >
                                    {{
                                        comment.created_at
                                            ? dayjs(
                                                  comment.created_at,
                                              ).fromNow()
                                            : ''
                                    }}
                                </span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
            <!-- Scroll trigger -->
            <!-- <div ref="loadMoreTrigger" class="h-10"></div> -->
            <!-- Load More button -->
            <!-- <div class="mt-4 text-center" v-if="nextPageUrl">
                <Button
                    label="Load More"
                    icon="pi pi-arrow-down"
                    :loading="isLoadingMore"
                    @click="loadMorePosts"
                />
            </div>
            <div v-if="isLoadingMore" class="mt-2 text-center text-sm">
                Loading more posts...
            </div> -->
        </div>
        <Dialog
            v-model:visible="isEditDialogVisible"
            modal
            header="Edit Post"
            class="w-full max-w-[900px]"
            :style="{ maxWidth: '700px' }"
        >
            <div class="p-2">
                <Textarea
                    v-model="editedContent"
                    autoResize
                    rows="4"
                    class="w-full"
                    placeholder="Edit your post..."
                />
            </div>
            <div
                v-if="errors?.editPost?.content"
                class="mt-1 text-sm text-red-500"
            >
                {{ errors.editPost.content }}
            </div>
            <template #footer>
                <Button
                    label="Cancel"
                    text
                    @click="isEditDialogVisible = false"
                />
                <Button label="Save" icon="pi pi-check" @click="submitEdit" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watchEffect } from 'vue';
// import { onMounted, onUnmounted } from 'vue';

import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import { useConfirm } from 'primevue/useconfirm';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { usePage } from '@inertiajs/vue3';
import { useThrottleFn } from '@vueuse/core';
import { useEcho } from '@laravel/echo-vue';
const props = defineProps({
    posts: Array, // 👈 it's an object, not Array anymore
    errors: Object,
});

const page = usePage();
const toast = useToast();
const newPost = ref('');
const confirm = useConfirm();
const editingPostId = ref(null);
const editedContent = ref('');
const isEditDialogVisible = ref(false);
const commentInputs = ref({});
const posts = ref(props.posts || []);
// const nextPageUrl = ref(props.posts.next_page_url || null);
// const isLoadingMore = ref(false);
// const loadMoreTrigger = ref(null);
watchEffect(() => {
    if (props?.posts) {
        posts.value = props.posts;
    }
});
// const loadMorePosts = async () => {
//     if (!nextPageUrl.value || isLoadingMore.value) return;
//     isLoadingMore.value = true;
//     try {
//         const response = await fetch(nextPageUrl.value, {
//             headers: {
//                 Accept: 'application/json', // 👈 crucial
//             },
//         });
//         const data = await response.json();
//         posts.value.push(...data.data);
//         nextPageUrl.value = data.next_page_url;
//     } finally {
//         isLoadingMore.value = false;
//     }
// };

// let observer;
// onMounted(() => {
//     observer = new IntersectionObserver(([entry]) => {
//         if (entry.isIntersecting) loadMorePosts();
//     });

//     if (loadMoreTrigger.value) {
//         observer.observe(loadMoreTrigger.value);
//     }
// });

// onUnmounted(() => {
//     if (observer && loadMoreTrigger.value) {
//         observer.unobserve(loadMoreTrigger.value);
//     }
// });
useEcho(`posts`, '.post.liked', (e) => {
    console.log('here is the event data::', e);
    const idx = posts.value.findIndex((p) => p.id === e.postId);
    if (idx !== -1) {
        posts.value[idx].likes_count = e.likesCount;
    }
});

dayjs.extend(relativeTime);

const authUser = computed(() => page.props.auth.user);
function useCommentError(postId) {
    return computed(() => {
        return props.errors?.[`createComment_${postId}`]?.content ?? '';
    });
}
function submitPost() {
    if (!newPost.value.trim()) return;

    router.post(
        '/posts',
        {
            content: newPost.value,
        },
        {
            errorBag: 'createPost',
            onSuccess: () => {
                newPost.value = '';
                toast.add({
                    severity: 'success',
                    summary: 'Post added!',
                    life: 3000,
                });
                router.reload({ only: ['posts'] }); // Refresh just the post list
            },
        },
    );
}

// function toggleLike(post) {
//     router.post(
//         `/posts/${post.id}/like`,
//         {},
//         {
//             preserveScroll: true,
//             onFinish: () => {
//                 router.reload({ only: ['posts'] });
//             },
//         },
//     );
// }
const _toggle = (post) => {
    router.post(
        `/posts/${post.id}/like`,
        {},
        {
            preserveScroll: true,
            onFinish: () => router.reload({ only: ['posts'] }),
        },
    );
};
const toggleLike = useThrottleFn(_toggle, 2000, true, false); // leading only

function addComment(post) {
    const commentContent = commentInputs.value[post.id];
    if (!commentContent || !commentContent.trim()) return;

    router.post(
        `/posts/${post.id}/comments`,
        {
            content: commentContent,
        },
        {
            errorBag: `createComment_${post.id}`,
            preserveScroll: true,
            onSuccess: () => {
                commentInputs.value[post.id] = '';
                toast.add({
                    severity: 'success',
                    summary: 'Comment added!',
                    life: 3000,
                });
            },
        },
    );
}

function startEdit(post) {
    editingPostId.value = post.id;
    editedContent.value = post.content;
    isEditDialogVisible.value = true;
}

function submitEdit() {
    router.put(
        `/posts/${editingPostId.value}`,
        {
            content: editedContent.value,
        },
        {
            errorBag: 'editPost',
            preserveScroll: true,
            onSuccess: () => {
                editingPostId.value = null;
                editedContent.value = '';
                isEditDialogVisible.value = false;
                toast.add({
                    severity: 'success',
                    summary: 'Post Updated!',
                    life: 3000,
                });
                router.reload({ only: ['posts'] });
            },
        },
    );
}

function deletePost(postId) {
    confirm.require({
        message: 'Are you sure you want to delete post?',
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancel',
        acceptLabel: 'Delete',
        acceptClass: 'p-button-danger',
        accept: async () => {
            router.delete(`/posts/${postId}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['posts'] });
                },
            });
        },
        reject: () => {},
    });
}
</script>
