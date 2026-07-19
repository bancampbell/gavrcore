<template>
    <div v-if="showBanner" class="cookie-consent">
        <div class="cookie-container">
            <div class="cookie-content">
                <div class="cookie-text">
                    <div>
                        <p class="cookie-title">Использование файлов cookie</p>
                        <p class="cookie-description">
                            Мы используем файлы cookie для улучшения работы сайта.
                            <button
                                @click="showSettings = !showSettings"
                                class="cookie-link"
                            >
                                Настройки
                            </button>
                        </p>
                    </div>
                </div>

                <div class="cookie-actions">
                    <button
                        @click="declineAll"
                        class="cookie-btn cookie-btn-secondary"
                    >
                        Отклонить
                    </button>
                    <button
                        @click="acceptAll"
                        class="cookie-btn cookie-btn-primary"
                    >
                        Принять
                    </button>
                </div>
            </div>

            <!-- Настройки -->
            <div v-if="showSettings" class="cookie-settings">
                <div class="cookie-settings-item">
                    <div class="cookie-settings-info">
                        <span class="cookie-settings-label">Необходимые</span>
                        <span class="cookie-settings-desc">Технические куки для работы сайта</span>
                    </div>
                    <span class="cookie-badge">Всегда включены</span>
                </div>

                <div class="cookie-settings-item">
                    <div class="cookie-settings-info">
                        <span class="cookie-settings-label">Аналитические</span>
                        <span class="cookie-settings-desc">Сбор статистики для улучшения сайта</span>
                    </div>
                    <button
                        @click="toggleCategory('analytics')"
                        class="cookie-toggle"
                        :class="{ 'cookie-toggle-on': settings.analytics }"
                    >
                        <span class="cookie-toggle-slider" />
                    </button>
                </div>

                <div class="cookie-settings-item">
                    <div class="cookie-settings-info">
                        <span class="cookie-settings-label">Маркетинговые</span>
                        <span class="cookie-settings-desc">Персонализированные предложения</span>
                    </div>
                    <button
                        @click="toggleCategory('marketing')"
                        class="cookie-toggle"
                        :class="{ 'cookie-toggle-on': settings.marketing }"
                    >
                        <span class="cookie-toggle-slider" />
                    </button>
                </div>

                <div class="cookie-settings-footer">
                    <button
                        @click="declineAll"
                        class="cookie-btn cookie-btn-secondary"
                    >
                        Отклонить все
                    </button>
                    <button
                        @click="saveSettings"
                        class="cookie-btn cookie-btn-primary"
                    >
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const showBanner = ref(false);
const showSettings = ref(false);

const settings = ref({
    analytics: false,
    marketing: false,
});

onMounted(() => {
    const consent = page.props.cookieConsent as { given: boolean; categories: any } | undefined;

    if (consent?.given) {
        showBanner.value = false;
        settings.value = {
            analytics: consent.categories?.analytics || false,
            marketing: consent.categories?.marketing || false,
        };

        if (consent.categories?.analytics) loadAnalytics();
        if (consent.categories?.marketing) loadMarketing();
    } else {
        const hasDeclined = localStorage.getItem('cookie_consent_declined');
        if (!hasDeclined) {
            showBanner.value = true;
        }
    }
});

const acceptAll = async () => {
    settings.value = { analytics: true, marketing: true };
    await saveConsent(settings.value);
    showBanner.value = false;
    loadAllScripts();
};

const declineAll = async () => {
    await axios.post('/cookie-consent/decline');
    localStorage.setItem('cookie_consent_declined', 'true');
    showBanner.value = false;
    showSettings.value = false;
};

const saveSettings = async () => {
    await saveConsent(settings.value);
    showBanner.value = false;
    showSettings.value = false;
    if (settings.value.analytics) loadAnalytics();
    if (settings.value.marketing) loadMarketing();
};

const toggleCategory = (category: 'analytics' | 'marketing') => {
    settings.value[category] = !settings.value[category];
};

const saveConsent = async (categories: { analytics: boolean; marketing: boolean }) => {
    await axios.post('/cookie-consent/accept', { categories });
    localStorage.removeItem('cookie_consent_declined');
};

const loadAnalytics = () => {
    console.log('🔍 Analytics loaded');
};

const loadMarketing = () => {
    console.log('📢 Marketing scripts loaded');
};

const loadAllScripts = () => {
    loadAnalytics();
    loadMarketing();
};
</script>

