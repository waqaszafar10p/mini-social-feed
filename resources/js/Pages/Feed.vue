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
                                    @click="editPost(post)"
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
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// Simulated logged-in user
const authUser = ref({ id: 1, name: 'Waqas Zafar' });

// New post input
const newPost = ref('');

// Sample hardcoded post list
const posts = ref([
    {
        id: 101,
        user: { id: 1, name: 'Waqas Zafar' },
        content: 'This is my first post!',
        likes_count: 2,
        liked: true,
        newComment: '',
        comments: [
            { id: 201, user: { name: 'Ali Khan' }, content: 'Great post!' },
            { id: 202, user: { name: 'Sara Malik' }, content: 'Love it 👏' },
        ],
    },
    {
        id: 102,
        user: { id: 2, name: 'Aisha Ahmed' },
        content: 'Enjoying Vue and Laravel ❤️',
        likes_count: 5,
        liked: false,
        newComment: '',
        comments: [
            { id: 203, user: { name: 'Waqas Zafar' }, content: 'Keep it up!' },
        ],
    },
]);

// Dummy functions
function submitPost() {
    alert('Post submitted: ' + newPost.value);
    newPost.value = '';
}

function toggleLike(post) {
    post.liked = !post.liked;
    post.likes_count += post.liked ? 1 : -1;
}

function addComment(post) {
    if (post.newComment.trim()) {
        post.comments.push({
            id: Date.now(),
            user: { name: authUser.value.name },
            content: post.newComment.trim(),
        });
        post.newComment = '';
    }
}

function editPost(post) {
    alert('Edit post: ' + post.id);
}

function deletePost(postId) {
    posts.value = posts.value.filter((p) => p.id !== postId);
}
</script>
