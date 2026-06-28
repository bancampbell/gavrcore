<template>
    <!-- Корневой пункт (1 уровень) -->
    <div v-if="!isSubmenu" class="relative group">
        <Link
            :href="getLinkUrl(item)"
            :target="item.target || '_self'"
            class="relative block px-3 py-2 text-gray-700 transition-all duration-200 hover:text-indigo-600"
            :class="{ 'text-indigo-600': isActive }"
        >
            {{ item.title }}
        </Link>

        <!-- Подменю для 1 уровня -->
        <div
            v-if="hasChildren"
            class="absolute left-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
        >
            <div class="bg-white rounded-lg shadow-lg border border-gray-100 min-w-[200px] py-1">
                <MenuItem
                    v-for="child in item.children"
                    :key="child.id"
                    :item="child"
                    :is-submenu="true"
                />
            </div>
        </div>
    </div>

    <!-- Пункт подменю (2+ уровень) -->
    <div v-else class="relative group-submenu">
        <Link
            :href="getLinkUrl(item)"
            :target="item.target || '_self'"
            class="relative block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-all duration-200"
            :class="{ 'bg-indigo-50 text-indigo-600': isActive }"
        >
            <div class="flex items-center justify-between gap-2">
                <span>{{ item.title }}</span>
                <svg v-if="hasChildren" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </Link>

        <!-- Подменю для 2+ уровня -->
        <div
            v-if="hasChildren"
            class="absolute left-full top-0 ml-1 opacity-0 invisible group-submenu-hover:opacity-100 group-submenu-hover:visible transition-all duration-200 z-50"
        >
            <div class="bg-white rounded-lg shadow-lg border border-gray-100 min-w-[200px] py-1">
                <MenuItem
                    v-for="child in item.children"
                    :key="child.id"
                    :item="child"
                    :is-submenu="true"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isSubmenu: {
        type: Boolean,
        default: false
    }
});

const getLinkUrl = (item) => {
    if (!item) return '#';
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};

const hasChildren = computed(() => {
    return props.item?.children && Array.isArray(props.item.children) && props.item.children.length > 0;
});

const isActive = computed(() => {
    const currentUrl = page.url;
    const itemUrl = getLinkUrl(props.item);

    if (itemUrl === '/') {
        return currentUrl === '/';
    }
    return currentUrl !== '/' && currentUrl.startsWith(itemUrl);
});
</script>

<style scoped>
.group:hover .group-hover\:visible {
    visibility: visible !important;
    opacity: 1 !important;
}

.group-submenu:hover .group-submenu-hover\:visible {
    visibility: visible !important;
    opacity: 1 !important;
}
</style>
