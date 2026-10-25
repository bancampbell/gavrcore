<template>
    <div class="sidebar-section">
        <div
            class="sidebar-section-toggle"
            @click="toggle"
        >
            <span class="section-title-text">{{ title }}</span>
            <svg
                class="section-arrow"
                :class="{ 'section-arrow-open': isOpen }"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </div>

        <div v-show="isOpen" class="sidebar-section-content">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    sectionKey: string;
    title: string;
    isOpen: boolean;
    toggle: () => void;
}>();
</script>
