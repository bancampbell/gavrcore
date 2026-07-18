<template>
    <div class="landing-page">
        <Head>
            <title>{{ meta?.title || title }}</title>
            <meta name="description" :content="meta?.description || description || siteDescription" />
            <meta name="keywords" :content="meta?.keywords || keywords || siteKeywords" />

            <!-- Open Graph -->
            <meta property="og:title" :content="meta?.og_title || meta?.title || title" />
            <meta property="og:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
            <meta property="og:url" :content="meta?.canonical || window.location.href" />
            <meta property="og:type" :content="meta?.og_type || 'website'" />
            <meta property="og:image" :content="meta?.og_image || ''" />
            <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

            <!-- Twitter Cards -->
            <meta name="twitter:card" :content="meta?.twitter_card || 'summary_large_image'" />
            <meta name="twitter:title" :content="meta?.og_title || meta?.title || title" />
            <meta name="twitter:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
            <meta name="twitter:image" :content="meta?.og_image || ''" />

            <!-- Canonical -->
            <link rel="canonical" :href="meta?.canonical || window.location.href" />
        </Head>

        <!-- НАВИГАЦИЯ -->
        <header class="header">
            <div class="container header-inner">
                <div class="logo">
                    <span class="logo-text">МИРРАСИМ</span>
                </div>
                <nav class="nav">
                    <a href="#about" class="nav-link" @click.prevent="scrollTo('about')">О компании</a>
                    <a href="#services" class="nav-link" @click.prevent="scrollTo('services')">Услуги</a>
                    <a href="#contacts" class="nav-link" @click.prevent="scrollTo('contacts')">Контакты</a>
                </nav>
                <a href="#contacts" class="header-phone" @click.prevent="scrollTo('contacts')">+7 (937) 006-30-63</a>
            </div>
        </header>

        <!-- ГЕРОЙ -->
        <section id="about" class="hero-section">
            <div class="container hero-content">
                <h1 class="hero-title">«МИРРАСИМ» — ваш надёжный партнёр!</h1>
                <p class="hero-description">
                    Предлагаем решение любых задач, связанных с производством и эксплуатацией электроустановок
                </p>
                <div class="hero-actions">
                    <a href="#contacts" class="hero-button" @click.prevent="scrollTo('contacts')">Получить персональное предложение</a>
                    <a href="tel:+79370063063" class="hero-phone-link">
                        <span class="phone-icon">📞</span>
                        +7 (937) 006-30-63
                    </a>
                </div>
            </div>
        </section>

        <!-- УСЛУГИ -->
        <section id="services" class="services-section">
            <div class="container">
                <h2 class="section-title">Наши услуги</h2>
                <div class="services-grid">
                    <div v-for="service in services" :key="service.title" class="service-card">
                        <div class="service-icon">{{ service.icon }}</div>
                        <h3 class="service-title">{{ service.title }}</h3>
                        <p class="service-description">{{ service.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ШОРТКОДЫ (ФОРМЫ, ГАЛЕРЕИ) -->
        <section class="shortcode-section">
            <div class="container">
                <ShortcodeRenderer
                    :content="landingContent"
                    :forms="forms || {}"
                />
            </div>
        </section>

        <!-- ПРЕИМУЩЕСТВА -->
        <section class="advantages-section">
            <div class="container">
                <h2 class="section-title">Почему выбирают нас</h2>
                <div class="advantages-grid">
                    <div v-for="item in advantages" :key="item" class="advantage-item">
                        <span class="advantage-check">✓</span>
                        <span class="advantage-text">{{ item }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ФОРМА -->
        <section id="contacts" class="form-section">
            <div class="container">
                <div class="form-wrapper">
                    <h2 class="form-title">Получить персональное предложение</h2>
                    <form @submit.prevent="submitForm" class="contact-form">
                        <div class="form-group">
                            <label for="name" class="form-label">Ваше имя</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="form-input"
                                placeholder="Иван Иванов"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Ваш номер телефона *</label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="form-input"
                                placeholder="+7 (___) ___-__-__"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="message" class="form-label">Сообщение</label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                class="form-textarea"
                                rows="4"
                                placeholder="Опишите вашу задачу..."
                            ></textarea>
                        </div>
                        <div class="form-checkbox">
                            <input
                                id="agreement"
                                v-model="form.agreement"
                                type="checkbox"
                                class="checkbox-input"
                                required
                            />
                            <label for="agreement" class="checkbox-label">
                                Согласие с политикой конфиденциальности
                            </label>
                        </div>
                        <button type="submit" class="submit-button" :disabled="loading">
                            {{ loading ? 'Отправка...' : 'Отправить' }}
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- ФУТЕР -->
        <footer class="footer">
            <div class="container footer-inner">
                <div class="footer-logo">МИРРАСИМ</div>
                <div class="footer-contacts">
                    <a href="tel:+79370063063" class="footer-phone">+7 (937) 006-30-63</a>
                    <span class="footer-email">info@mirrasim.ru</span>
                </div>
                <div class="footer-copy">
                    © {{ new Date().getFullYear() }} Все права защищены
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import ShortcodeRenderer from '@/components/shared/ShortcodeRenderer.vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    appSettings: Object,
    currentTheme: String,
    mainMenu: Array,
    title: String,
    description: String,
    keywords: String,
    forms: {
        type: Object,
        default: () => ({})
    },
    meta: {
        type: Object,
        default: () => ({}),
    }
});

const siteName = props.appSettings?.site_name || 'GavrCore CMS';
const siteDescription = props.appSettings?.site_description || '';
const siteKeywords = props.appSettings?.seo_keywords || '';

const loading = ref(false);
const form = reactive({
    name: '',
    phone: '',
    message: '',
    agreement: false
});

const services = [
    {
        icon: '⚡',
        title: 'Испытания кабелей',
        description: 'Испытания кабелей с изоляцией из сшитого полиэтилена с применением сверхнизкой частоты 0,1 Гц'
    },
    {
        icon: '🔧',
        title: 'Пусконаладочные работы',
        description: 'Комплексный подход к пусконаладке и вводу в эксплуатацию электроустановок'
    },
    {
        icon: '📊',
        title: 'Энергоаудит',
        description: 'Анализ эффективности использования энергоресурсов и оптимизация затрат'
    },
    {
        icon: '🛡️',
        title: 'Защита от перенапряжений',
        description: 'Монтаж и обслуживание систем молниезащиты и защиты от перенапряжений'
    }
];

const advantages = [
    'Опыт работы более 10 лет',
    'Лицензии и допуски СРО',
    'Современное оборудование',
    'Гарантия качества работ',
    'Индивидуальный подход к каждому клиенту',
    'Оперативное выполнение заказов'
];

const scrollTo = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const headerHeight = document.querySelector('.header')?.offsetHeight || 80;
        const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - headerHeight;

        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
};

const landingContent = `
    <h2 class="text-2xl font-bold text-center mb-6">Свяжитесь с нами</h2>
    <p class="text-center text-gray-600 mb-8">Оставьте заявку и мы свяжемся с вами</p>
   [gallery id="1"]
`;
</script>

<style scoped>
.landing-page {
    font-family: system-ui, -apple-system, sans-serif;
    background: #f8fafc;
    min-height: 100vh;
    color: #1a2a3a;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== HEADER ===== */
.header {
    background: white;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 16px 0;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.logo-text {
    font-size: 24px;
    font-weight: 700;
    color: #1a3a6b;
    letter-spacing: 1px;
}

.nav {
    display: flex;
    gap: 32px;
}

.nav-link {
    color: #2a3a5a;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: color 0.2s;
    cursor: pointer;
}

.nav-link:hover {
    color: #2a5a9b;
}

.header-phone {
    color: #1a3a6b;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
}

/* ===== HERO ===== */
.hero-section {
    background: linear-gradient(135deg, #1a3a6b 0%, #2a5a9b 50%, #3a7acb 100%);
    padding: 80px 20px 60px;
    color: white;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.hero-title {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
}

.hero-description {
    font-size: 18px;
    line-height: 1.6;
    opacity: 0.9;
    margin-bottom: 32px;
}

.hero-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.hero-button {
    display: inline-block;
    background: white;
    color: #1a3a6b;
    padding: 14px 40px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
}

.hero-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.hero-phone-link {
    color: white;
    text-decoration: none;
    font-size: 20px;
    font-weight: 600;
}

.phone-icon {
    margin-right: 8px;
}

/* ===== SERVICES ===== */
.services-section {
    padding: 60px 20px;
    background: white;
}

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a2a4a;
    text-align: center;
    margin-bottom: 40px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
}

.service-card {
    background: #f8faff;
    padding: 28px 24px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s;
    border: 1px solid transparent;
}

.service-card:hover {
    border-color: #2a5a9b;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(42,90,155,0.10);
}

.service-icon {
    font-size: 36px;
    margin-bottom: 12px;
}

.service-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a2a4a;
    margin-bottom: 8px;
}

.service-description {
    font-size: 14px;
    color: #4a5a7a;
    line-height: 1.5;
}

/* ===== SHORTCODE ===== */
.shortcode-section {
    padding: 60px 20px;
    background: white;
    border-top: 1px solid #eef2f6;
    border-bottom: 1px solid #eef2f6;
}

.shortcode-section .container {
    max-width: 900px;
    margin: 0 auto;
}

/* ===== ADVANTAGES ===== */
.advantages-section {
    background: #f0f4ff;
    padding: 60px 20px;
}

.advantages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    max-width: 900px;
    margin: 0 auto;
}

.advantage-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.advantage-check {
    color: #2a5a9b;
    font-weight: 700;
    font-size: 20px;
}

.advantage-text {
    color: #2a3a5a;
    font-size: 15px;
}

/* ===== FORM ===== */
.form-section {
    padding: 60px 20px;
    background: white;
}

.form-wrapper {
    max-width: 600px;
    margin: 0 auto;
    background: #f8faff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.form-title {
    font-size: 26px;
    font-weight: 700;
    color: #1a2a4a;
    text-align: center;
    margin-bottom: 28px;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 14px;
    font-weight: 500;
    color: #2a3a5a;
}

.form-input,
.form-textarea {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.2s;
    background: white;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #2a5a9b;
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label {
    font-size: 14px;
    color: #4a5a7a;
    cursor: pointer;
}

.submit-button {
    padding: 14px 32px;
    background: #1a3a6b;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-button:hover {
    background: #2a5a9b;
}

.submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ===== FOOTER ===== */
.footer {
    background: #0f1a2e;
    color: #aab8d4;
    padding: 32px 20px;
}

.footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-logo {
    font-size: 20px;
    font-weight: 700;
    color: white;
}

.footer-contacts {
    display: flex;
    gap: 24px;
}

.footer-phone {
    color: white;
    text-decoration: none;
    font-weight: 500;
}

.footer-email {
    color: #8a9abb;
}

.footer-copy {
    font-size: 14px;
    color: #6a7a9a;
}

/* ===== АДАПТИВ ===== */
@media (max-width: 768px) {
    .header-inner {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .nav {
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .hero-title {
        font-size: 28px;
    }

    .hero-description {
        font-size: 16px;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .advantages-grid {
        grid-template-columns: 1fr;
    }

    .form-wrapper {
        padding: 24px 20px;
    }

    .footer-inner {
        flex-direction: column;
        text-align: center;
    }

    .footer-contacts {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
