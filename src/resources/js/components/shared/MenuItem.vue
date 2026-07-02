<template>
    <!-- Корневой пункт (1 уровень) -->
    <div v-if="!isSubmenu" class="menu-item">
        <Link
            :href="getLinkUrl(item)"
            :target="item.target || '_self'"
            class="menu-link"
            :class="{ active: isActive }"
        >
            {{ item.title }}
        </Link>

        <!-- Подменю для 1 уровня -->
        <div v-if="hasChildren" class="menu-dropdown">
            <div class="menu-dropdown-inner">
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
    <div v-else class="menu-subitem">
        <Link
            :href="getLinkUrl(item)"
            :target="item.target || '_self'"
            class="menu-link"
            :class="{ active: isActive }"
        >
            <span class="menu-link-text">{{ item.title }}</span>
            <svg v-if="hasChildren" class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </Link>

        <!-- Подменю для 2+ уровня -->
        <div v-if="hasChildren" class="menu-subdropdown">
            <div class="menu-dropdown-inner">
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
