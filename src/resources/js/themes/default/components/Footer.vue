<template>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ new Date().getFullYear() }} {{ appSettings.site_name || 'GavrCore CMS' }}. Все права защищены.</p>
        </div>
    </footer>
</template>

<script setup>
defineProps({
    appSettings: {
        type: Object,
        default: () => ({})
    }
});
</script>
