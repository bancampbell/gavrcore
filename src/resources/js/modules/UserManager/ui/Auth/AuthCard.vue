<template>
    <div class="max-w-lg mx-auto py-12 px-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-8 py-10">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        required: true
    }
});
</script>
