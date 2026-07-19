<template>
    <LayoutSwitcher>
        <div class="landing">
            <Head>
                <title>{{ meta?.title || title }}</title>
                <meta name="description" :content="meta?.description || description || siteDescription" />
                <meta name="keywords" :content="meta?.keywords || keywords || siteKeywords" />

                <meta property="og:title" :content="meta?.og_title || meta?.title || title" />
                <meta property="og:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
                <meta property="og:url" :content="meta?.canonical || window.location.href" />
                <meta property="og:type" :content="meta?.og_type || 'website'" />
                <meta property="og:image" :content="meta?.og_image || ''" />
                <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

                <meta name="twitter:card" :content="meta?.twitter_card || 'summary_large_image'" />
                <meta name="twitter:title" :content="meta?.og_title || meta?.title || title" />
                <meta name="twitter:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
                <meta name="twitter:image" :content="meta?.og_image || ''" />

                <link rel="canonical" :href="meta?.canonical || window.location.href" />
            </Head>

            <nav class="nav" ref="navRef">
                <div class="container nav-inner">
                    <a href="/" class="nav-logo">MIRRASIM</a>
                    <div class="nav-links">
                        <a v-for="item in menuItems" :key="item.id" :href="item.href" class="nav-link" :class="{ active: activeSection === item.id }" @click.prevent="scrollTo(item.href, item.id)">
                            {{ item.label }}
                        </a>
                    </div>
                    <a href="#contact" class="nav-cta">Связаться</a>
                    <button class="burger" @click="mobileOpen = !mobileOpen">
                        <span></span><span></span><span></span>
                    </button>
                </div>
                <div class="mobile-menu" :class="{ open: mobileOpen }">
                    <a v-for="item in menuItems" :key="item.id" :href="item.href" @click.prevent="scrollTo(item.href, item.id); mobileOpen = false">
                        {{ item.label }}
                    </a>
                    <a href="#contact" class="mobile-cta" @click.prevent="scrollTo('#contact', 'contact'); mobileOpen = false">Связаться</a>
                </div>
            </nav>

            <section id="hero" class="hero">
                <div class="container hero-inner">
                    <div class="hero-left">
                        <div class="hero-line"></div>
                        <div class="hero-label">Высотные работы</div>
                        <h1 class="hero-title">Утепление фасадов<br /><span class="hero-accent">под ключ</span></h1>
                        <p class="hero-text">Работаем с объектами любой сложности по всей России.<br />Гарантия качества и безопасность на каждом этапе.</p>
                        <div class="hero-buttons">
                            <a href="#contact" class="btn-primary">Оставить заявку</a>
                            <a href="#work" class="btn-secondary">Наши работы</a>
                        </div>
                    </div>
                    <div class="hero-right">
                        <div class="hero-block"><div class="hero-block-number">10+</div><div class="hero-block-label">лет опыта</div></div>
                        <div class="hero-block"><div class="hero-block-number">150+</div><div class="hero-block-label">объектов</div></div>
                        <div class="hero-block"><div class="hero-block-number">98%</div><div class="hero-block-label">довольных клиентов</div></div>
                    </div>
                </div>
            </section>

            <section id="services" class="services">
                <div class="container">
                    <div class="section-label">Услуги</div>
                    <h2 class="section-title">Что мы делаем</h2>
                    <div class="services-grid">
                        <div class="service">
                            <div class="service-marker">01</div>
                            <h3 class="service-title">Утепление фасадов</h3>
                            <p class="service-desc">Монтаж утеплителя и финишной отделки. Минеральная вата, пенополистирол, штукатурка.</p>
                        </div>
                        <div class="service">
                            <div class="service-marker">02</div>
                            <h3 class="service-title">Высотные работы</h3>
                            <p class="service-desc">Ремонт, обслуживание, мойка окон и герметизация швов на высоте до 150 метров.</p>
                        </div>
                        <div class="service">
                            <div class="service-marker">03</div>
                            <h3 class="service-title">Монтаж конструкций</h3>
                            <p class="service-desc">Вентфасады, козырьки, водосточные системы, наружное освещение.</p>
                        </div>
                        <div class="service">
                            <div class="service-marker">04</div>
                            <h3 class="service-title">Антикоррозийная защита</h3>
                            <p class="service-desc">Грунтовка, покраска, огнезащита металлоконструкций любой сложности.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="about" class="about">
                <div class="container about-inner">
                    <div class="about-content">
                        <div class="section-label">О компании</div>
                        <h2 class="section-title">Надёжный партнёр в высотных работах</h2>
                        <p class="about-text">Компания «Миррасим» работает на рынке промышленного альпинизма более 10 лет. Все специалисты имеют допуски к работам на высоте и профильные сертификаты. Мы гарантируем качество, соблюдение сроков и безопасность.</p>
                        <ul class="about-list">
                            <li>Лицензии и допуски СРО</li>
                            <li>Работаем с объектами любой сложности</li>
                            <li>Гарантия до 5 лет</li>
                        </ul>
                    </div>
                    <div class="about-block">
                        <div class="about-block-placeholder">Фото компании</div>
                    </div>
                </div>
            </section>

            <section id="work" class="work">
                <div class="container">
                    <div class="section-label">Портфолио</div>
                    <h2 class="section-title">Реализованные проекты</h2>
                    <div class="work-grid">
                        <div class="work-item">
                            <div class="work-placeholder">🏢</div>
                            <div class="work-info">
                                <h4>ЖК «Высотный»</h4>
                                <p>24 этажа, утепление фасада</p>
                            </div>
                        </div>
                        <div class="work-item">
                            <div class="work-placeholder">🏢</div>
                            <div class="work-info">
                                <h4>БЦ «Столица»</h4>
                                <p>16 этажей, мойка и герметизация</p>
                            </div>
                        </div>
                        <div class="work-item">
                            <div class="work-placeholder">🏢</div>
                            <div class="work-info">
                                <h4>ТЦ «Гранд»</h4>
                                <p>12 этажей, монтаж вентфасада</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="contact">
                <div class="container">
                    <div class="contact-inner">
                        <div class="contact-left">
                            <div class="section-label">Свяжитесь с нами</div>
                            <h2 class="section-title">Обсудим ваш проект</h2>
                            <p class="contact-text">Оставьте заявку — мы свяжемся с вами в течение 15 минут.</p>
                            <div class="contact-info">
                                <a href="tel:+79370063063" class="contact-phone">+7 (937) 006-30-63</a>
                                <a href="mailto:info@mirrasim.ru" class="contact-email">info@mirrasim.ru</a>
                                <span class="contact-address">📍 Москва, ул. Строителей, 15</span>
                            </div>
                        </div>
                        <div class="contact-right">
                            <form class="contact-form">
                                <input type="text" class="form-input" placeholder="Ваше имя" />
                                <input type="tel" class="form-input" placeholder="Номер телефона *" />
                                <textarea class="form-input form-textarea" rows="4" placeholder="Опишите задачу..."></textarea>
                                <button type="submit" class="btn-primary btn-full">Отправить заявку</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <div class="container footer-inner">
                    <span class="footer-logo">MIRRASIM</span>
                    <span class="footer-copy">© 2026 Все права защищены</span>
                </div>
            </footer>
        </div>
    </LayoutSwitcher>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';

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

const siteDescription = props.appSettings?.site_description || '';
const siteKeywords = props.appSettings?.seo_keywords || '';

const mobileOpen = ref(false);
const activeSection = ref('hero');
const navRef = ref(null);

const menuItems = [
    { id: 'services', label: 'Услуги', href: '#services' },
    { id: 'about', label: 'О компании', href: '#about' },
    { id: 'work', label: 'Проекты', href: '#work' },
    { id: 'contact', label: 'Связаться', href: '#contact' },
];

const scrollTo = (href, id) => {
    const target = document.querySelector(href);
    if (!target) return;
    const offset = navRef.value?.offsetHeight || 80;
    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top, behavior: 'smooth' });
    activeSection.value = id;
};

let observer = null;
let ticking = false;

const updateActiveSection = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
        const sections = menuItems.map((item) => ({
            id: item.id,
            el: document.getElementById(item.id),
        }));

        const scrollY = window.scrollY + 120;

        let found = 'hero';
        for (const section of sections) {
            if (!section.el) continue;
            const top = section.el.offsetTop;
            const height = section.el.offsetHeight;
            if (scrollY >= top && scrollY < top + height) {
                found = section.id;
                break;
            }
        }

        activeSection.value = found;
        ticking = false;
    });
};

onMounted(() => {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (e) => {
            const href = anchor.getAttribute('href');
            if (href === '#') return;
            e.preventDefault();
            const target = document.querySelector(href);
            if (!target) return;
            const offset = navRef.value?.offsetHeight || 80;
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            });
        },
        { threshold: 0.25 }
    );

    menuItems.forEach((item) => {
        const el = document.getElementById(item.id);
        if (el) observer.observe(el);
    });

    window.addEventListener('scroll', updateActiveSection);
});

onUnmounted(() => {
    if (observer) {
        menuItems.forEach((item) => {
            const el = document.getElementById(item.id);
            if (el) observer.unobserve(el);
        });
        observer = null;
    }
    window.removeEventListener('scroll', updateActiveSection);
});
</script>
