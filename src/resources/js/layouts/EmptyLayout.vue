<template>
    <div class="min-h-screen bg-white">
        <TopNav :user="user" @logout="handleLogout" />
        <main class="p-0">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import TopNav from '../components/Admin/TopNav.vue';

defineProps({
    user: Object
});

const handleLogout = async () => {
    const token = localStorage.getItem('token');
    if (token) {
        await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${token}` }
        });
    }
    localStorage.removeItem('token');
    router.visit('/admin/login');
};
</script>
