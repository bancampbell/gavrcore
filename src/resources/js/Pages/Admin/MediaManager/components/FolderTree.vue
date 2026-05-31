<template>
    <div>
        <div
            @click="handleClick"
            class="group text-sm py-1 px-2 rounded-md cursor-pointer flex items-center transition-colors"
            :class="isActive ? 'bg-[#e6f0fa] text-[#333] font-medium' : 'text-gray-700 hover:bg-[#e6f0fa] hover:text-[#333]'"
        >
            <span
                v-if="hasChildren"
                @click.stop="toggleExpand"
                class="inline-flex items-center justify-center w-3.5 h-3.5 mr-1.5 border rounded hover:bg-gray-200 cursor-pointer"
                :class="isExpanded ? 'bg-gray-100' : ''"
                style="border-color: #333;"
            >
                <svg v-if="!isExpanded" class="w-2.5 h-2.5" fill="none" stroke="#333" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <svg v-else class="w-2.5 h-2.5" fill="none" stroke="#333" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                </svg>
            </span>
            <span v-else class="w-3.5 h-3.5 mr-1.5"></span>

            <!-- Иконка папки SVG с фоном -->
            <span class="mr-2 inline-flex items-center justify-center">
                <svg v-if="!isExpanded" class="w-5 h-5" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </span>

            <span class="flex-1" @click="navigate">{{ folder.name }}</span>
        </div>
        <div v-if="hasChildren && isExpanded" class="ml-6">
            <FolderTree
                v-for="child in children"
                :key="child.path"
                :folder="child"
                :current-path="currentPath"
                :all-folders="allFolders"
                :expanded-folders="expandedFolders"
                @navigate="$emit('navigate', $event)"
                @toggle-expand="$emit('toggle-expand', $event)"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Folder {
    name: string;
    path: string;
}

const props = defineProps<{
    folder: Folder;
    currentPath: string;
    allFolders: Folder[];
    expandedFolders?: string[];
}>();

const emit = defineEmits<{
    (e: 'navigate', path: string): void;
    (e: 'toggle-expand', path: string): void;
}>();

// Проверяем, раскрыта ли папка (из пропса expandedFolders или по умолчанию)
const isExpanded = computed(() => {
    if (props.expandedFolders) {
        return props.expandedFolders.includes(props.folder.path);
    }
    return false;
});

// Проверяем, активна ли текущая папка
const isActive = computed(() => props.currentPath === props.folder.path);

// Получаем дочерние папки
const children = computed(() => {
    return props.allFolders.filter(folder => {
        const folderParts = folder.path.split('/');
        const currentParts = props.folder.path.split('/');

        if (folderParts.length !== currentParts.length + 1) return false;

        return folder.path.startsWith(props.folder.path + '/');
    });
});

const hasChildren = computed(() => children.value.length > 0);

// Переключение раскрытия папки
const toggleExpand = () => {
    if (hasChildren.value) {
        emit('toggle-expand', props.folder.path);
    }
};

// Навигация в папку
const navigate = () => {
    emit('navigate', props.folder.path);
};

// Обработчик клика по элементу
const handleClick = (event: MouseEvent) => {
    // Если кликнули не по стрелке (у стрелки есть stopPropagation)
    // то просто навигируем
    navigate();
};
</script>
