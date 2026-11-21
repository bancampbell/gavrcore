<template>
    <div class="max-w-lg mx-auto py-12 px-4">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div v-if="title || subtitle" class="bg-gradient-to-r from-[#294469] to-[#3688d1] px-8 py-8 text-center">
                <h1 v-if="title" class="text-2xl font-bold text-white">{{ title }}</h1>
                <p v-if="subtitle" class="text-blue-200 text-sm mt-1">{{ subtitle }}</p>
            </div>
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
        required: false
    },
    subtitle: {
        type: String,
        required: false
    }
});
</script>
