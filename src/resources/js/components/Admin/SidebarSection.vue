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

<style scoped>
.sidebar-section-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 8px 12px !important;
    margin: 2px 0 !important;
    border-radius: 6px 0 0 6px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    background: rgba(45, 58, 77, 0.12) !important;
    user-select: none !important;
}

.sidebar-section-toggle:hover {
    background: rgba(45, 58, 77, 0.17) !important;
}


.section-title-text {
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    letter-spacing: 0.02em !important;
}

.section-arrow {
    flex-shrink: 0 !important;
    color: #94a3b8 !important;
    transition: transform 0.25s ease !important;
}

.section-arrow-open {
    transform: rotate(180deg) !important;
    color: #3071a9 !important;
}

.sidebar-section-content {
    margin-top: 2px !important;
    padding-left: 4px !important;
}

.sidebar-section-content :deep(.sidebar-link) {
    padding-left: 24px !important;
    font-size: 14px !important;
}
</style>
