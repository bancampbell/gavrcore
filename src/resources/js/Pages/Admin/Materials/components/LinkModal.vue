<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="modal-content link-modal">
            <!-- Заголовок -->
            <div class="modal-header">
                <h3 class="modal-title">Вставка ссылки</h3>
                <button @click="close" class="modal-close">&times;</button>
            </div>

            <!-- Тело модалки -->
            <div class="modal-body">
                <!-- Адрес -->
                <div class="form-row">
                    <label class="admin-form-label">Адрес</label>
                    <div class="input-group">
                        <input
                            v-model="linkUrl"
                            type="text"
                            class="admin-form-input"
                            placeholder="https://..."
                        />
                        <button
                            @click="openFileManager"
                            type="button"
                            class="btn-icon"
                            title="Файловый менеджер"
                        >
                            📄
                        </button>
                    </div>
                </div>

                <!-- Текст ссылки -->
                <div class="form-row">
                    <label class="admin-form-label">Текст ссылки</label>
                    <input
                        v-model="linkText"
                        type="text"
                        class="admin-form-input"
                        placeholder="Текст ссылки"
                    />
                </div>

                <!-- Контент -->
                <div class="form-group">
                    <label class="admin-form-label">Контент</label>
                    <div class="category-tree-container">
                        <div class="search-box">
                            <input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Поиск..."
                                class="search-input"
                            />
                            <button @click="searchMaterials" class="search-button">
                                Поиск
                            </button>
                        </div>
                        <div class="search-results">
                            <div v-if="searchTerm">
                                <div
                                    v-for="material in searchResults"
                                    :key="material.id"
                                    @click="selectMaterial(material)"
                                    class="material-item"
                                    :class="{ selected: selectedMaterialId === material.id }"
                                >
                                    {{ material.title }}
                                </div>
                                <div v-if="searchResults.length === 0" class="empty-message">
                                    Материалы не найдены
                                </div>
                            </div>
                            <div v-else>
                                <ContentTree
                                    :categories="categories"
                                    :materials="materials"
                                    :selected-item-id="selectedMaterialId"
                                    :selected-type="selectedType"
                                    :expanded-categories="expandedCategories"
                                    @select-item="onSelectContentItem"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Цель -->
                <div class="form-row">
                    <label class="admin-form-label">Цель</label>
                    <select v-model="linkTarget" class="admin-form-select">
                        <option value="_self">_self (текущее окно)</option>
                        <option value="_blank">_blank (новое окно)</option>
                    </select>
                </div>

                <!-- Название -->
                <div class="form-row">
                    <label class="admin-form-label">Название</label>
                    <input
                        v-model="linkTitle"
                        type="text"
                        class="admin-form-input"
                        placeholder="Всплывающая подсказка"
                    />
                </div>
            </div>

            <!-- Футер -->
            <div class="modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="insertLink" class="btn-primary">Вставить ссылку</button>
            </div>
        </div>
    </div>

    <MediaManagerModal
        :show="showMediaManager"
        :user="user"
        :selected-url="linkUrl"
        mode="file"
        @close="showMediaManager = false"
        @select="onMediaSelect"
    />
</template>

<script setup lang="ts">
import { ref, watch, inject } from 'vue';
import ContentTree from './ContentTree.vue';
import MediaManagerModal from './MediaManagerModal.vue';

const props = defineProps<{
    show: boolean;
    categories?: any[];
    materials?: any[];
    editData?: {
        oldText: string;
        url: string;
        text: string;
        target: string;
        title: string;
    } | null;
    selectedText?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insert', data: { url: string; text: string; target: string; title: string }): void;
    (e: 'edit', data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }): void;
}>();

const user = inject('user') as any;

const linkUrl = ref('');
const linkText = ref('');
const linkTarget = ref('_self');
const linkTitle = ref('');
const searchTerm = ref('');
const searchResults = ref<any[]>([]);
const selectedMaterialId = ref<number | null>(null);
const selectedType = ref<string | null>(null);
const isEditMode = ref(false);
const originalLinkText = ref('');
const originalLinkUrl = ref('');
const expandedCategories = ref<number[]>([]);
const showMediaManager = ref(false);

const getMaterialUrl = (material: any): string => {
    if (material.slug) return `/${material.slug}`;
    if (material.alias) return `/${material.alias}`;
    return `/materials/${material.id}`;
};

const expandToMaterial = (url: string) => {
    if (!url || !props.materials) return;

    const cleanUrl = url.startsWith('/') ? url.substring(1) : url;

    const material = props.materials.find(m => {
        if (m.slug && (cleanUrl === m.slug || url === `/${m.slug}`)) return true;
        if (m.alias && (cleanUrl === m.alias || url === `/${m.alias}`)) return true;
        if (url === `/materials/${m.id}`) return true;
        return false;
    });

    if (material) {
        selectedMaterialId.value = material.id;
        selectedType.value = 'material';

        const expandCategory = (categoryId: number | null) => {
            if (!categoryId) return;
            const category = props.categories?.find(c => c.id === categoryId);
            if (category) {
                if (!expandedCategories.value.includes(category.id)) {
                    expandedCategories.value.push(category.id);
                }
                if (category.parent_id) {
                    expandCategory(category.parent_id);
                }
            }
        };

        expandCategory(material.category_id);
    }
};

watch(() => props.show, (val) => {
    if (val) {
        expandedCategories.value = [];

        if (props.editData) {
            isEditMode.value = true;
            linkUrl.value = props.editData.url;
            linkText.value = props.editData.text;
            linkTarget.value = props.editData.target === '_blank' ? '_blank' : '_self';
            linkTitle.value = props.editData.title;
            originalLinkText.value = props.editData.oldText;
            originalLinkUrl.value = props.editData.url;
            expandToMaterial(props.editData.url);
        } else {
            isEditMode.value = false;
            linkUrl.value = '';
            linkText.value = props.selectedText || '';
            linkTarget.value = '_self';
            linkTitle.value = '';
            originalLinkText.value = '';
            originalLinkUrl.value = '';
            selectedMaterialId.value = null;
            selectedType.value = null;
        }
        searchTerm.value = '';
    }
});

const close = () => {
    emit('close');
};

const openFileManager = () => {
    showMediaManager.value = true;
};

const onMediaSelect = (file: { url: string; name: string; path: string; options?: any }) => {
    linkUrl.value = file.url;
    if (!linkText.value) {
        linkText.value = file.name;
    }
    selectedMaterialId.value = null;
    selectedType.value = null;
    showMediaManager.value = false;
};

const searchMaterials = () => {
    // Логика поиска
};

const selectMaterial = (material: any) => {
    selectedMaterialId.value = material.id;
    selectedType.value = 'material';
    linkUrl.value = getMaterialUrl(material);

    const expandCategory = (categoryId: number | null) => {
        if (!categoryId) return;
        const category = props.categories?.find(c => c.id === categoryId);
        if (category) {
            if (!expandedCategories.value.includes(category.id)) {
                expandedCategories.value.push(category.id);
            }
            if (category.parent_id) {
                expandCategory(category.parent_id);
            }
        }
    };
    expandCategory(material.category_id);
};

const onSelectContentItem = (item: any, type: 'category' | 'material') => {
    if (type === 'material') {
        selectedMaterialId.value = item.id;
        selectedType.value = 'material';
        linkUrl.value = getMaterialUrl(item);
    }
};

const insertLink = () => {
    if (!linkUrl.value) {
        alert('Введите адрес ссылки');
        return;
    }

    const targetValue = linkTarget.value === '_blank' ? '_blank' : '_self';

    if (isEditMode.value) {
        emit('edit', {
            oldText: originalLinkText.value,
            newUrl: linkUrl.value,
            newText: linkText.value || linkUrl.value,
            newTarget: targetValue,
            newTitle: linkTitle.value
        });
        originalLinkText.value = linkText.value || linkUrl.value;
        originalLinkUrl.value = linkUrl.value;
    } else {
        emit('insert', {
            url: linkUrl.value,
            text: linkText.value || linkUrl.value,
            target: targetValue,
            title: linkTitle.value
        });
    }
    close();
};

const updateLink = insertLink;
</script>
