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
    if (item.link_type === 'external') return item.link_value;
    return item.url || '#';
});

const hasChildren = computed(() =>
    Array.isArray(props.item.children) && props.item.children.length > 0
);
</script>

<!-- Все стили перенесены в resources/css/themes/default/header.css -->
