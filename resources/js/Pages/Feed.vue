<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="container mx-auto p-4">
            <!-- 🌟 Create Post -->
            <Card class="mb-4">
                <template #title>What's on your mind?</template>
                <template #content>
                    <Textarea
                        v-model="newPost"
                        autoResize
                        rows="3"
                        class="mb-2 w-full"
                    />
                    <Button
                        label="Post"
                        icon="pi pi-send"
                        class="w-full"
                        @click="submitPost"
                    />
                </template>
            </Card>

            <!-- 📰 Posts List -->
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

                        <!-- ❤️ Likes -->
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

                        <!-- 💬 Comments -->
                        <div class="mt-4 border-t pt-2">
                            <div
                                v-for="comment in post.comments"
                                :key="comment.id"
                                class="mb-2 text-sm"
                            >
                                <strong>{{ comment.user.name }}:</strong>
                                {{ comment.content }}
                            </div>

                            <div class="mt-2 flex gap-2">
                                <InputText
                                    v-model="post.newComment"
                                    class="w-full"
                                    placeholder="Write a comment..."
                                />
                                <Button
                                    icon="pi pi-send"
                                    @click="addComment(post)"
                                />
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

const toast = useToast();
const newPost = ref('');
const confirm = useConfirm();

const editingPostId = ref(null);
const editedContent = ref('');
const isEditDialogVisible = ref(false);

defineProps({
    posts: Array,
    authUser: Object,
});

function submitPost() {
    if (!newPost.value.trim()) return;

    router.post(
        '/posts',
        {
            content: newPost.value,
        },
        {
            onSuccess: () => {
                newPost.value = '';
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
    if (!post.newComment || !post.newComment.trim()) return;

    router.post(
        `/posts/${post.id}/comments`,
        {
            content: post.newComment,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                post.newComment = '';
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
