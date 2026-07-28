<template>
    <Head>
        <title>{{ title }}</title>
    </Head>

    <AdminLayout v-if="mode !== 'picker'" :user="user">
        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Медиа менеджер</h1>
            </div>

            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <MediaManagerContent
                        ref="contentRef"
                        :mode="mode"
                        :accepted-files="acceptedFiles"
                        @select="handleSelect"
                        @file-selected="handleFileSelected"
                        @loaded="handleLoaded"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>

    <div v-if="mode === 'picker'" class="media-manager-picker-wrapper">
        <MediaManagerContent
            ref="contentRef"
            :mode="mode"
            :accepted-files="acceptedFiles"
            @select="handleSelect"
            @file-selected="handleFileSelected"
            @loaded="handleLoaded"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import MediaManagerContent from './components/MediaManagerContent.vue';
import type { User } from '../../../types';

const props = defineProps<{
    user: User;
    title?: string;
    mode?: 'full' | 'picker';
    selectedUrl?: string;
    acceptedFiles?: string[];
}>();

const emit = defineEmits<{
    (e: 'select', file: { url: string; name: string; path: string }): void;
    (e: 'fileSelected', file: { url: string; name: string; path: string } | null): void;
    (e: 'close'): void;
    (e: 'loaded'): void;
}>();

const contentRef = ref<InstanceType<typeof MediaManagerContent> | null>(null);

const handleSelect = (file: { url: string; name: string; path: string }) => {
    emit('select', file);
};

const handleFileSelected = (file: { url: string; name: string; path: string } | null) => {
    emit('fileSelected', file);
};

const handleLoaded = () => {
    emit('loaded');
};

const selectFileByUrl = async (url: string) => {
    if (contentRef.value?.selectFileByUrl) {
        await contentRef.value.selectFileByUrl(url);
    }
};

defineExpose({
    confirmSelection: () => contentRef.value?.confirmSelection?.(),
    getSelectedFile: () => contentRef.value?.getSelectedFile?.(),
    selectedFileForPicker: () => contentRef.value?.selectedFileForPicker,
    selectFileByUrl,
});

watch(() => props.selectedUrl, async (newUrl) => {
    if (newUrl && props.mode === 'picker') {
        await nextTick();
        await selectFileByUrl(newUrl);
    }
}, { immediate: true });

onMounted(async () => {
    if (props.selectedUrl && props.mode === 'picker') {
        await nextTick();
        await selectFileByUrl(props.selectedUrl);
    }
});
</script>
