<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="user-modal">
            <div class="user-modal-header">
                <h3>{{ isEdit ? 'Редактировать пользователя' : 'Создать пользователя' }}</h3>
                <button @click="close" class="user-modal-close">×</button>
            </div>

            <div class="nav-tabs-wrapper">
                <div class="nav-tabs">
                    <button
                        @click="activeTab = 'account'"
                        :class="['nav-item', { active: activeTab === 'account' }]"
                    >
                        Учётная запись
                    </button>
                    <button
                        @click="activeTab = 'groups'"
                        :class="['nav-item', { active: activeTab === 'groups' }]"
                    >
                        Группы
                    </button>
                    <button
                        @click="activeTab = 'options'"
                        :class="['nav-item', { active: activeTab === 'options' }]"
                    >
                        Параметры
                    </button>
                </div>
            </div>

            <div class="tab-content-fixed">
                <!-- Вкладка: Учётная запись -->
                <div v-show="activeTab === 'account'" class="tab-pane">
                    <div class="form-row">
                        <label class="form-label">Имя *</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            class="form-input"
                            placeholder="Введите имя"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Логин *</label>
                        <input
                            v-model="formData.username"
                            type="text"
                            class="form-input"
                            placeholder="Введите логин"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">E-mail *</label>
                        <input
                            v-model="formData.email"
                            type="email"
                            class="form-input"
                            placeholder="user@example.com"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">Пароль</label>
                        <input
                            v-model="formData.password"
                            type="password"
                            class="form-input"
                            :placeholder="isEdit ? 'Оставьте пустым чтобы не менять' : 'Введите пароль'"
                        />
                    </div>

                    <div class="form-row">
                        <label class="form-label">E-mail активация</label>
                        <select v-model="formData.activated" class="form-select">
                            <option :value="true">Активирован</option>
                            <option :value="false">Не активирован</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Блокировка</label>
                        <select v-model="formData.blocked" class="form-select">
                            <option :value="false">Активен</option>
                            <option :value="true">Заблокирован</option>
                        </select>
                    </div>
                </div>

                <!-- Вкладка: Группы -->
                <div v-show="activeTab === 'groups'" class="tab-pane">
                    <div class="form-group">
                        <label class="form-label">Назначенные группы</label>
                        <div class="groups-list">
                            <label v-for="group in allGroups" :key="group.id" class="group-checkbox">
                                <input
                                    type="checkbox"
                                    :value="group.id"
                                    v-model="formData.groups"
                                    class="rounded border-gray-300"
                                />
                                <span>{{ group.name }}</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            Пользователь наследует права всех выбранных групп
                        </p>
                    </div>
                </div>

                <!-- Вкладка: Параметры -->
                <div v-show="activeTab === 'options'" class="tab-pane tab-placeholder">
                    <div class="placeholder-content">
                        <p>Дополнительные настройки будут добавлены позже</p>
                    </div>
                </div>
            </div>

            <div class="user-modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="save" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Сохранение...' : (isEdit ? 'Обновить' : 'Создать') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface Group {
    id: number;
    name: string;
}

interface UserForm {
    name: string;
    username: string;
    email: string;
    password: string;
    blocked: boolean;
    activated: boolean;
    groups: number[];
}

const props = defineProps<{
    show: boolean;
    isEdit: boolean;
    userData?: any;
    groups: Group[];
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', data: UserForm): void;
}>();

const activeTab = ref('account');
const formData = ref<UserForm>({
    name: '',
    username: '',
    email: '',
    password: '',
    blocked: false,
    activated: true,
    groups: [],
});

const allGroups = ref<Group[]>([]);

watch(() => props.show, (val) => {
    if (val) {
        allGroups.value = props.groups;
        activeTab.value = 'account';

        if (props.isEdit && props.userData) {
            formData.value = {
                name: props.userData.name || '',
                username: props.userData.username || '',
                email: props.userData.email || '',
                password: '',
                blocked: props.userData.blocked || false,
                activated: props.userData.activated !== false,
                groups: props.userData.groups?.map((g: any) => g.id) || [],
            };
        } else {
            formData.value = {
                name: '',
                username: '',
                email: '',
                password: '',
                blocked: false,
                activated: true,
                groups: [],
            };
        }
    }
});

const close = () => {
    emit('close');
};

const save = () => {
    if (!formData.value.name) {
        alert('Введите имя');
        return;
    }
    if (!formData.value.username) {
        alert('Введите логин');
        return;
    }
    if (!formData.value.email) {
        alert('Введите email');
        return;
    }
    if (!props.isEdit && !formData.value.password) {
        alert('Введите пароль');
        return;
    }

    emit('save', { ...formData.value });
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-modal {
    background: white;
    border-radius: 0.5rem;
    width: 650px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: 550px;
    max-height: 85vh;
}

.user-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.user-modal-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.user-modal-close {
    color: #4b5563;
    font-size: 1.75rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.user-modal-close:hover {
    opacity: 1;
    color: #1f2937;
}

.nav-tabs-wrapper {
    padding: 0.75rem 1.5rem 0 1.5rem;
    background: white;
    flex-shrink: 0;
}

.nav-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #e5e7eb;
}

.nav-item {
    padding: 0.625rem 1rem;
    background: none;
    border: none;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: -1px;
}

.nav-item:hover {
    color: #4f46e5;
}

.nav-item.active {
    color: #4f46e5;
    border-bottom: 2px solid #4f46e5;
}

.tab-content-fixed {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.tab-pane {
    width: 100%;
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.form-row .form-label {
    width: 130px;
    flex-shrink: 0;
    margin-bottom: 0;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
}

.form-row .form-input,
.form-row .form-select {
    flex: 1;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group .form-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.form-select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    background: white;
}

.groups-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.75rem;
    max-height: 200px;
    overflow-y: auto;
}

.group-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.user-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #f3f4f6;
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #337ab7;
    color: white;
    border-radius: 0.375rem;
    border: none;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-primary:hover {
    background: #286090;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.tab-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

.placeholder-content {
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}
</style>
