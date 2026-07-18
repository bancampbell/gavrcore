<template>
    <footer class="site-footer-warm">
        <div class="container">
            <div class="footer-grid-warm">
                <div class="footer-col-warm">
                    <h4>{{ appSettings.site_name || 'GavrCore CMS' }}</h4>
                    <p>{{ appSettings.site_description || '' }}</p>
                </div>
                <div class="footer-col-warm">
                    <h4>Навигация</h4>
                    <ul>
                        <li v-for="item in mainMenu" :key="item.id">
                            <Link :href="getLinkUrl(item)">{{ item.title }}</Link>
                        </li>
                    </ul>
                </div>
                <div class="footer-col-warm">
                    <h4>Контакты</h4>
                    <p>{{ appSettings.contact_email || 'info@example.com' }}</p>
                </div>
            </div>
            <div class="footer-bottom-warm">
                <p>&copy; {{ new Date().getFullYear() }} {{ appSettings.site_name || 'GavrCore CMS' }}. Все права защищены.</p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    mainMenu: {
        type: Array,
        default: () => []
    },
    appSettings: {
        type: Object,
        default: () => ({})
    }
});

const getLinkUrl = (item) => {
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};
</script>
