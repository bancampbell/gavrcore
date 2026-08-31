<template>
    <div
        class="menu-item"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <Link
            v-if="isLink"
            :href="linkUrl"
            :target="isExternal ? '_blank' : undefined"
            :rel="isExternal ? 'noopener noreferrer' : undefined"
            class="menu-link"
        >
            {{ item.title }}
        </Link>
        <span v-else class="menu-text">
            {{ item.title }}
        </span>

        <div
            v-if="hasChildren"
            class="menu-children"
            :class="{ 'menu-children-visible': isHovered }"
        >
            <MenuItem
                v-for="child in item.children"
                :key="child.id"
                :item="child"
            />
        </div>
    </div>
</template>

<script setup>
import {computed, ref} from 'vue';
import {Link} from '@inertiajs/vue3';
import MenuItem from './MenuItem.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const isHovered = ref(false);

const isExternal = computed(() => props.item.link_type === 'external');

const isLink = computed(() =>
    props.item.link_type !== 'separator' && props.item.link_type !== 'heading'
);

const linkUrl = computed(() => {
    const item = props.item;
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    if (item.link_type === 'external') return item.link_value;
    return '#';
});

const hasChildren = computed(() =>
    Array.isArray(props.item.children) && props.item.children.length > 0
);
</script>

<style scoped>
.menu-item {
    position: relative;
}

.menu-link,
.menu-text {
    display: block;
    padding: 0.5rem 1rem;
    color: inherit;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
}

.menu-children {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 200px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 50;
    padding: 0.25rem 0;
}

/* Для вложенных подменю (2+ уровень) — открываем сбоку */
.menu-children .menu-children {
    top: 0;
    left: 100%;
    margin-left: 2px;
}

.menu-children-visible {
    display: block;
}

/* Пункты внутри подменю */
.menu-children .menu-item {
    border-bottom: 1px solid #f3f4f6;
}

.menu-children .menu-item:last-child {
    border-bottom: none;
}

.menu-children .menu-link:hover,
.menu-children .menu-text:hover {
    background: #f8fafc;
}
</style>
