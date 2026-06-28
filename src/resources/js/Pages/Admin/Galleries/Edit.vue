<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="sticky top-0 z-20 bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">{{ title }}</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <Link href="/admin/galleries" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Закрыть
                </Link>
            </div>
        </div>

        <div class="p-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- НАЗВАНИЕ -->
                <div class="p-4 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">НАЗВАНИЕ</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full max-w-2xl border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Введите название..."
                    />
                </div>

                <!-- Вкладки -->
                <div class="border-b border-gray-200">
                    <div class="flex px-4">
                        <button
                            @click="activeTab = 'media'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'media' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            МЕДИА
                        </button>
                        <button
                            @click="activeTab = 'settings'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'settings' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            НАСТРОЙКИ
                        </button>
                        <button
                            @click="activeTab = 'content'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition"
                            :class="activeTab === 'content' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        >
                            КОНТЕНТ
                        </button>
                    </div>
                </div>

                <!-- Содержимое вкладок -->
                <div class="p-4">
                    <!-- МЕДИА -->
                    <div v-if="activeTab === 'media'">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-1 border-r border-gray-200 pr-4">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm font-medium text-gray-700">Изображения</span>
                                    <button
                                        @click="triggerUpload"
                                        :disabled="uploading"
                                        class="text-sm text-blue-600 hover:text-blue-800 disabled:opacity-50"
                                    >
                                        {{ uploading ? 'Загрузка...' : '+ Добавить' }}
                                    </button>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        class="hidden"
                                        @change="uploadImages"
                                    />
                                </div>

                                <div class="space-y-1 max-h-[500px] overflow-y-auto">
                                    <div
                                        v-for="image in images"
                                        :key="image.id"
                                        @click="selectImage(image)"
                                        class="flex items-center gap-3 p-2 rounded cursor-pointer hover:bg-gray-50 transition"
                                        :class="selectedImage?.id === image.id ? 'bg-blue-50 border border-blue-200' : ''"
                                    >
                                        <img
                                            :src="image.image_path"
                                            alt=""
                                            class="w-12 h-12 object-cover rounded border border-gray-200"
                                        />
                                        <span class="text-sm text-gray-700 truncate flex-1">
                                            {{ image.title || 'Без названия' }}
                                        </span>
                                        <button
                                            @click.stop="deleteImage(image.id)"
                                            class="text-gray-400 hover:text-red-600"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                    <div v-if="images.length === 0 && !uploading" class="text-center py-8 text-gray-400 text-sm">
                                        Нет изображений
                                    </div>
                                    <div v-if="uploading" class="text-center py-4 text-sm text-gray-500">
                                        Загрузка...
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2">
                                <div v-if="selectedImage" class="space-y-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">НАЗВАНИЕ</label>
                                            <input
                                                v-model="selectedImage.title"
                                                type="text"
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                placeholder="Введите название..."
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">ПУТЬ</label>
                                            <input
                                                :value="selectedImage.image_path"
                                                type="text"
                                                readonly
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm bg-gray-50 text-gray-500"
                                            />
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 p-2">
                                        <img
                                            :src="selectedImage.image_path"
                                            :alt="selectedImage.title || 'Изображение'"
                                            class="w-full max-w-[600px] h-64 object-contain"
                                        />
                                    </div>

                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="flex border-b border-gray-200">
                                            <button
                                                @click="editorMode = 'editor'"
                                                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                                                :class="editorMode === 'editor' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                            >
                                                Редактор
                                            </button>
                                            <button
                                                @click="editorMode = 'code'"
                                                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                                                :class="editorMode === 'code' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                            >
                                                Код
                                            </button>
                                            <button
                                                @click="editorMode = 'preview'"
                                                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                                                :class="editorMode === 'preview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
                                            >
                                                Предпросмотр
                                            </button>
                                        </div>
                                        <div class="p-4 min-h-[150px]">
                                            <textarea
                                                v-if="editorMode === 'code'"
                                                v-model="selectedImage.description"
                                                class="w-full min-h-[150px] border-0 focus:outline-none font-mono text-sm"
                                                placeholder="Введите описание..."
                                            />
                                            <div v-else-if="editorMode === 'preview'" class="prose max-w-none">
                                                {{ selectedImage.description || 'Нет описания' }}
                                            </div>
                                            <textarea
                                                v-else
                                                v-model="selectedImage.description"
                                                class="w-full min-h-[150px] border-0 focus:outline-none text-sm"
                                                placeholder="Введите описание..."
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-12 text-gray-400">
                                    Выберите изображение для редактирования
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- НАСТРОЙКИ -->
                    <div v-if="activeTab === 'settings'">
                        <div class="max-w-3xl space-y-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Макет</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Отступ (px)</label>
                                        <input
                                            v-model.number="settings.gutter"
                                            type="number"
                                            min="0"
                                            max="50"
                                            class="w-32 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.match_height = !settings.match_height"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.match_height ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.match_height ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Выравнивание высоты</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Выравнивание</label>
                                        <select v-model="settings.alignment" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="left">По левому краю</option>
                                            <option value="center">По центру</option>
                                            <option value="right">По правому краю</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Медиа</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-3 max-w-md">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Ширина (px)</label>
                                            <input
                                                v-model="settings.media.width"
                                                type="text"
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                placeholder="авто"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Высота (px)</label>
                                            <input
                                                v-model="settings.media.height"
                                                type="text"
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                placeholder="авто"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Граница</label>
                                        <select v-model="settings.media.border" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="none">Нет</option>
                                            <option value="rounded">Скругленная</option>
                                            <option value="circle">Круг</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Контент</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.content.show_title = !settings.content.show_title"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.content.show_title ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.content.show_title ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать заголовок</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.content.show_content = !settings.content.show_content"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.content.show_content ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.content.show_content ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать описание</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Размер заголовка</label>
                                        <select v-model="settings.content.title_size" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="default">По умолчанию</option>
                                            <option value="small">Маленький</option>
                                            <option value="large">Большой</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- КОНТЕНТ -->
                    <div v-if="activeTab === 'content'">
                        <div class="max-w-3xl space-y-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Текст</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.content.show_title = !settings.content.show_title"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.content.show_title ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.content.show_title ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать заголовок</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.content.show_content = !settings.content.show_content"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.content.show_content ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.content.show_content ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать описание</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Размер заголовка</label>
                                        <select v-model="settings.content.title_size" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="default">По умолчанию</option>
                                            <option value="small">Маленький</option>
                                            <option value="large">Большой</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Ссылка</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.link.show = !settings.link.show"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.link.show ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.link.show ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать ссылку</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Стиль</label>
                                        <select v-model="settings.link.style" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="button">Кнопка</option>
                                            <option value="text">Текст</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Текст</label>
                                        <input
                                            v-model="settings.link.text"
                                            type="text"
                                            class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Подробнее"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Лайтбокс</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Лайтбокс</label>
                                        <select v-model="settings.lightbox.mode" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="default">По умолчанию</option>
                                            <option value="enabled">Включен</option>
                                            <option value="disabled">Отключен</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.lightbox.use_title = !settings.lightbox.use_title"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.lightbox.use_title ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.lightbox.use_title ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Использовать заголовок</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Подпись</label>
                                        <input
                                            v-model="settings.lightbox.caption"
                                            type="text"
                                            class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Подпись к изображению"
                                        />
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 max-w-md">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Ширина (px)</label>
                                            <input
                                                v-model="settings.lightbox.image_width"
                                                type="text"
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                placeholder="авто"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Высота (px)</label>
                                            <input
                                                v-model="settings.lightbox.image_height"
                                                type="text"
                                                class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                placeholder="авто"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.lightbox.show_second_media = !settings.lightbox.show_second_media"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.lightbox.show_second_media ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.lightbox.show_second_media ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Показывать второй медиа-элемент в лайтбоксе</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-800 border-b border-gray-200 pb-2 mb-4">Кнопка</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="settings.lightbox.button.enabled = !settings.lightbox.button.enabled"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="settings.lightbox.button.enabled ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                                :class="settings.lightbox.button.enabled ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">Включить ссылку лайтбокса</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Стиль</label>
                                        <select v-model="settings.lightbox.button.style" class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="button">Кнопка</option>
                                            <option value="text">Текст</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Текст</label>
                                        <input
                                            v-model="settings.lightbox.button.text"
                                            type="text"
                                            class="w-full max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Подробнее"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />

        <ConfirmModal
            :is-open="confirmModal.isOpen"
            :title="confirmModal.title"
            :message="confirmModal.message"
            :confirm-text="confirmModal.confirmText"
            :type="confirmModal.type"
            :loading="confirmModal.loading"
            @close="confirmModal.isOpen = false"
            @confirm="confirmModal.onConfirm"
        />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '../../../layouts/EmptyLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import ConfirmModal from '../../../components/shared/ConfirmModal.vue';

const props = defineProps<{
    user: any;
    gallery: any;
    title: string;
}>();

const loading = ref(false);
const uploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const activeTab = ref('media');
const editorMode = ref('editor');
const notification = ref({ show: false, message: '', type: 'success' | 'error' });

const confirmModal = ref({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Удалить',
    type: 'danger' as 'danger' | 'warning',
    loading: false,
    onConfirm: () => {},
});

const form = ref({
    title: props.gallery.title,
    type: props.gallery.type,
    status: props.gallery.status,
});

const defaultSettings = {
    gutter: 20,
    match_height: true,
    alignment: 'left',
    media: { width: 'auto', height: 'auto', border: 'none' },
    content: { show_title: true, show_content: false, title_size: 'default' },
    link: { show: true, style: 'button', text: 'Подробнее' },
    lightbox: {
        mode: 'default',
        use_title: true,
        caption: '',
        image_width: 'auto',
        image_height: 'auto',
        show_second_media: false,
        button: { enabled: true, style: 'button', text: 'Подробнее' }
    }
};

function mergeDeep(target: any, source: any): any {
    const result = JSON.parse(JSON.stringify(target));
    for (const key in source) {
        if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
            if (!result[key]) result[key] = {};
            result[key] = mergeDeep(result[key], source[key]);
        } else if (source[key] !== undefined && source[key] !== null) {
            result[key] = source[key];
        }
    }
    return result;
}

const initializeSettings = () => {
    const saved = props.gallery.settings;
    if (saved && typeof saved === 'object') {
        return mergeDeep(defaultSettings, saved);
    }
    return JSON.parse(JSON.stringify(defaultSettings));
};

const settings = reactive(initializeSettings());

const images = ref<any[]>(props.gallery.images || []);
const selectedImage = ref<any>(null);

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const showConfirmModal = (options: {
    title: string;
    message: string;
    confirmText?: string;
    type?: 'danger' | 'warning';
    onConfirm: () => void;
}) => {
    confirmModal.value = {
        isOpen: true,
        title: options.title,
        message: options.message,
        confirmText: options.confirmText || 'Удалить',
        type: options.type || 'danger',
        loading: false,
        onConfirm: options.onConfirm,
    };
};

const triggerUpload = () => {
    fileInput.value?.click();
};

const uploadImages = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (!files || files.length === 0) return;

    uploading.value = true;

    try {
        for (const file of files) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('title', file.name);

            const response = await axios.post(`/admin/galleries/${props.gallery.id}/images`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (response.data.image) {
                images.value.push(response.data.image);
            }
        }

        showNotification('Изображения загружены', 'success');

        if (images.value.length > 0 && !selectedImage.value) {
            selectedImage.value = images.value[0];
        }
    } catch (error) {
        showNotification('Ошибка при загрузке', 'error');
        console.error('Upload error:', error);
    } finally {
        uploading.value = false;
        target.value = '';
    }
};

const selectImage = (image: any) => {
    selectedImage.value = image;
};

const deleteImage = async (imageId: number) => {
    showConfirmModal({
        title: 'Удалить изображение?',
        message: 'Вы уверены, что хотите удалить это изображение? Это действие нельзя отменить.',
        confirmText: 'Удалить',
        type: 'danger',
        onConfirm: async () => {
            confirmModal.value.loading = true;
            try {
                await axios.delete(`/admin/galleries/${props.gallery.id}/images/${imageId}`);
                images.value = images.value.filter(img => img.id !== imageId);
                showNotification('Изображение удалено', 'success');
                if (selectedImage.value?.id === imageId) {
                    selectedImage.value = images.value.length > 0 ? images.value[0] : null;
                }
            } catch (error) {
                showNotification('Ошибка при удалении', 'error');
            } finally {
                confirmModal.value.loading = false;
                confirmModal.value.isOpen = false;
            }
        }
    });
};

const save = async () => {
    if (!form.value.title.trim()) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;

    try {
        await axios.put(`/admin/galleries/${props.gallery.id}`, {
            title: form.value.title,
            type: form.value.type,
            status: form.value.status,
            settings: settings
        });
        showNotification('Сохранено', 'success');
    } catch (error) {
        showNotification('Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.title.trim()) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;

    try {
        await axios.put(`/admin/galleries/${props.gallery.id}`, {
            title: form.value.title,
            type: form.value.type,
            status: form.value.status,
            settings: settings
        });
        showNotification('Сохранено', 'success');
        setTimeout(() => {
            router.visit('/admin/galleries');
        }, 500);
    } catch (error) {
        showNotification('Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (images.value.length > 0) {
        selectedImage.value = images.value[0];
    }
});
</script>
