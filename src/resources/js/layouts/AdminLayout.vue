<template>
    <div class="min-h-screen bg-gray-100">
        <TopNav :user="user" @toggleSidebar="sidebarOpen = !sidebarOpen" @logout="handleLogout" />

        <div class="flex pt-12">
            <div v-if="sidebarOpen" class="fixed inset-0 z-20 lg:hidden" @click="sidebarOpen = false"></div>

            <Sidebar :isOpen="sidebarOpen" :user="user" />

            <main class="flex-1 p-4 lg:p-6">
                <div class="w-full">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import TopNav from '../components/Admin/TopNav.vue';
import Sidebar from '../components/Admin/Sidebar.vue';

defineProps({
    user: Object
});

const sidebarOpen = ref(false);

const handleLogout = async () => {
    const token = localStorage.getItem('token');
    if (token) {
        await axios.post('/api/logout', {}, {
            headers: {Authorization: `Bearer ${token}`}
        });
    }
    localStorage.removeItem('token');
    router.visit('/admin/login');
};
</script>
