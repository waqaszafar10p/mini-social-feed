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
                                    v-model="newComment"
                                    class="w-full"
                                    placeholder="Write a comment..."
                                />

                                <Button
                                    icon="pi pi-send"
                                    @click="addComment(post)"
                                />
                            </div>
                            <div
                                v-if="errors?.createComment?.content"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.createComment.content }}
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
import { ref } from 'vue';
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

const toast = useToast();
const newPost = ref('');
const newComment = ref('');
const confirm = useConfirm();
const editingPostId = ref(null);
const editedContent = ref('');
const isEditDialogVisible = ref(false);

defineProps({
    posts: Array,
    authUser: Object,
    errors: Object,
});
dayjs.extend(relativeTime);

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

function toggleLike(post) {
    router.post(
        `/posts/${post.id}/like`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                router.reload({ only: ['posts'] });
            },
        },
    );
}

function addComment(post) {
    if (!newComment.value || !newComment.value.trim()) return;

    router.post(
        `/posts/${post.id}/comments`,
        {
            content: newComment.value,
        },
        {
            errorBag: 'createComment',
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
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
            preserveScroll: true,
            onSuccess: () => {
                editingPostId.value = null;
                editedContent.value = '';
                isEditDialogVisible.value = false;
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
