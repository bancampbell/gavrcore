<template>
    <div class="landing">
        <!-- ===== НАВИГАЦИЯ ===== -->
        <nav class="nav" ref="navRef">
            <div class="container nav-inner">
                <a href="/" class="nav-logo">MIRRASIM</a>

                <div class="nav-links">
                    <a
                        v-for="item in menuItems"
                        :key="item.id"
                        :href="item.href"
                        class="nav-link"
                        :class="{ active: activeSection === item.id }"
                        @click.prevent="scrollTo(item.href, item.id)"
                    >
                        {{ item.label }}
                    </a>
                </div>

                <a href="#contact" class="nav-cta">Связаться</a>

                <button class="burger" @click="mobileOpen = !mobileOpen">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <!-- Мобильное меню -->
            <div class="mobile-menu" :class="{ open: mobileOpen }">
                <a
                    v-for="item in menuItems"
                    :key="item.id"
                    :href="item.href"
                    @click.prevent="scrollTo(item.href, item.id); mobileOpen = false"
                >
                    {{ item.label }}
                </a>
                <a href="#contact" class="mobile-cta" @click.prevent="scrollTo('#contact', 'contact'); mobileOpen = false">Связаться</a>
            </div>
        </nav>

        <!-- ===== HERO ===== -->
        <section id="hero" class="hero">
            <div class="container hero-inner">
                <div class="hero-left">
                    <!-- ПОЛОСКА КАК НА МАКЕТЕ -->
                    <div class="hero-line"></div>

                    <div class="hero-label">Высотные работы</div>
                    <h1 class="hero-title">
                        Утепление фасадов<br />
                        <span class="hero-accent">под ключ</span>
                    </h1>
                    <p class="hero-text">
                        Работаем с объектами любой сложности по всей России.<br />
                        Гарантия качества и безопасность на каждом этапе.
                    </p>
                    <div class="hero-buttons">
                        <a href="#contact" class="btn-primary">Оставить заявку</a>
                        <a href="#work" class="btn-secondary">Наши работы</a>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="hero-block">
                        <div class="hero-block-number">10+</div>
                        <div class="hero-block-label">лет опыта</div>
                    </div>
                    <div class="hero-block">
                        <div class="hero-block-number">150+</div>
                        <div class="hero-block-label">объектов</div>
                    </div>
                    <div class="hero-block">
                        <div class="hero-block-number">98%</div>
                        <div class="hero-block-label">довольных клиентов</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== SERVICES ===== -->
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

        <!-- ===== ABOUT ===== -->
        <section id="about" class="about">
            <div class="container about-inner">
                <div class="about-content">
                    <div class="section-label">О компании</div>
                    <h2 class="section-title">Надёжный партнёр в высотных работах</h2>
                    <p class="about-text">
                        Компания «Миррасим» работает на рынке промышленного альпинизма более 10 лет.
                        Все специалисты имеют допуски к работам на высоте и профильные сертификаты.
                        Мы гарантируем качество, соблюдение сроков и безопасность.
                    </p>
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

        <!-- ===== WORK ===== -->
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

        <!-- ===== CONTACT ===== -->
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

        <!-- ===== FOOTER ===== -->
        <footer class="footer">
            <div class="container footer-inner">
                <span class="footer-logo">MIRRASIM</span>
                <span class="footer-copy">© 2026 Все права защищены</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const mobileOpen = ref(false)
const activeSection = ref('hero')
const navRef = ref(null)

const menuItems = [
    { id: 'services', label: 'Услуги', href: '#services' },
    { id: 'about', label: 'О компании', href: '#about' },
    { id: 'work', label: 'Проекты', href: '#work' },
    { id: 'contact', label: 'Связаться', href: '#contact' },
]

const scrollTo = (href, id) => {
    const target = document.querySelector(href)
    if (!target) return
    const offset = navRef.value?.offsetHeight || 80
    const top = target.getBoundingClientRect().top + window.pageYOffset - offset
    window.scrollTo({ top, behavior: 'smooth' })
    activeSection.value = id
}

let observer = null
let ticking = false

const updateActiveSection = () => {
    if (ticking) return
    ticking = true
    requestAnimationFrame(() => {
        const sections = menuItems.map((item) => ({
            id: item.id,
            el: document.getElementById(item.id),
        }))

        const scrollY = window.scrollY + 120

        let found = 'hero'
        for (const section of sections) {
            if (!section.el) continue
            const top = section.el.offsetTop
            const height = section.el.offsetHeight
            if (scrollY >= top && scrollY < top + height) {
                found = section.id
                break
            }
        }

        activeSection.value = found
        ticking = false
    })
}

onMounted(() => {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (e) => {
            const href = anchor.getAttribute('href')
            if (href === '#') return
            e.preventDefault()
            const target = document.querySelector(href)
            if (!target) return
            const offset = navRef.value?.offsetHeight || 80
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset
            window.scrollTo({ top, behavior: 'smooth' })
        })
    })

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id
                }
            })
        },
        { threshold: 0.25 }
    )

    menuItems.forEach((item) => {
        const el = document.getElementById(item.id)
        if (el) observer.observe(el)
    })

    window.addEventListener('scroll', updateActiveSection)
})

onUnmounted(() => {
    if (observer) {
        menuItems.forEach((item) => {
            const el = document.getElementById(item.id)
            if (el) observer.unobserve(el)
        })
        observer = null
    }
    window.removeEventListener('scroll', updateActiveSection)
})
</script>

<style scoped>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

.landing {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #ffffff;
    color: #1a1a2e;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ===== НАВИГАЦИЯ ===== */
.nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    padding: 14px 0;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-logo {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: #1a1a2e;
    text-decoration: none;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-link {
    padding: 6px 14px;
    color: #4a4a6a;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s;
    cursor: pointer;
}

.nav-link:hover {
    color: #1a1a2e;
    background: rgba(0, 0, 0, 0.04);
}

.nav-link.active {
    color: #1a1a2e;
    background: rgba(0, 0, 0, 0.06);
}

.nav-cta {
    background: #1a1a2e;
    color: white !important;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s;
    white-space: nowrap;
}

.nav-cta:hover {
    background: #2a2a4e;
}

/* ===== БУРГЕР ===== */
.burger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
}

.burger span {
    display: block;
    width: 24px;
    height: 2px;
    background: #1a1a2e;
    border-radius: 2px;
}

/* ===== МОБИЛЬНОЕ МЕНЮ ===== */
.mobile-menu {
    display: none;
    flex-direction: column;
    gap: 8px;
    padding: 16px 0 8px;
    border-top: 1px solid rgba(0, 0, 0, 0.04);
    margin-top: 16px;
}

.mobile-menu.open {
    display: flex;
}

.mobile-menu a {
    padding: 10px 0;
    color: #4a4a6a;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    cursor: pointer;
}

.mobile-menu a:hover {
    color: #1a1a2e;
}

.mobile-cta {
    background: #1a1a2e;
    color: white !important;
    text-align: center;
    padding: 12px !important;
    border-radius: 8px;
    font-weight: 600;
    border-bottom: none !important;
}

.mobile-cta:hover {
    background: #2a2a4e !important;
}

/* ===== HERO ===== */
.hero {
    padding: 120px 0 80px;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: #faf9fe;
}

.hero-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    width: 100%;
}

/* ===== ПОЛОСКА КАК НА МАКЕТЕ ===== */
.hero-line {
    width: 100%;
    max-width: 1200px;
    height: 2px;
    background: #f97316;
    margin: 0 auto 16px auto;
    border-radius: 2px;
}

.hero-label {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #f97316;
    margin-bottom: 16px;
}

.hero-title {
    font-size: 56px;
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -1.5px;
}

.hero-accent {
    background: linear-gradient(135deg, #f97316, #dc2626);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-text {
    font-size: 18px;
    color: #6a6a8a;
    line-height: 1.7;
    margin: 20px 0 32px;
}

.hero-buttons {
    display: flex;
    gap: 16px;
}

.btn-primary {
    display: inline-block;
    background: #1a1a2e;
    color: white;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: #2a2a4e;
    transform: translateY(-2px);
}

.btn-secondary {
    display: inline-block;
    background: transparent;
    color: #1a1a2e;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
}

.btn-secondary:hover {
    border-color: #1a1a2e;
    background: #faf9fe;
}

.hero-right {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.hero-block {
    background: white;
    padding: 32px 24px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(0, 0, 0, 0.03);
}

.hero-block-number {
    font-size: 40px;
    font-weight: 700;
    color: #1a1a2e;
}

.hero-block-label {
    font-size: 14px;
    color: #6a6a8a;
    margin-top: 4px;
}

.services {
    padding: 80px 0;
    background: #ffffff;
}

.section-label {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #f97316;
    margin-bottom: 8px;
}

.section-title {
    font-size: 38px;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: 40px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.service {
    padding: 24px;
    border-top: 3px solid #f0f0f0;
    transition: all 0.3s;
}

.service:hover {
    border-top-color: #f97316;
}

.service-marker {
    font-size: 14px;
    font-weight: 700;
    color: #c0c0d0;
    margin-bottom: 12px;
}

.service-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
}

.service-desc {
    font-size: 14px;
    color: #6a6a8a;
    line-height: 1.6;
}

.about {
    padding: 80px 0;
    background: #faf9fe;
}

.about-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.about-text {
    font-size: 16px;
    color: #4a4a6a;
    line-height: 1.8;
    margin: 16px 0 24px;
}

.about-list {
    list-style: none;
    padding: 0;
}

.about-list li {
    padding: 6px 0;
    font-size: 15px;
    color: #4a4a6a;
}

.about-block-placeholder {
    background: linear-gradient(135deg, #fef3c7, #f97316);
    border-radius: 16px;
    min-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.work {
    padding: 80px 0;
    background: #ffffff;
}

.work-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.work-item {
    background: #faf9fe;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s;
}

.work-item:hover {
    transform: translateY(-4px);
}

.work-placeholder {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    background: #f0f0f0;
}

.work-info {
    padding: 20px 24px 24px;
}

.work-info h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
}

.work-info p {
    font-size: 14px;
    color: #6a6a8a;
}

.contact {
    padding: 80px 0;
    background: #faf9fe;
}

.contact-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

.contact-text {
    font-size: 16px;
    color: #6a6a8a;
    margin: 16px 0 32px;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-phone {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a2e;
    text-decoration: none;
}

.contact-phone:hover {
    color: #f97316;
}

.contact-email {
    color: #4a4a6a;
    text-decoration: none;
}

.contact-address {
    color: #8a8aaa;
    font-size: 14px;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-input {
    padding: 14px 18px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    background: white;
    transition: border-color 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #1a1a2e;
}

.form-textarea {
    resize: vertical;
    font-family: inherit;
}

.btn-full {
    width: 100%;
    text-align: center;
}

.footer {
    padding: 32px 0;
    background: #1a1a2e;
    color: #aab8d4;
}

.footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-logo {
    font-weight: 700;
    font-size: 18px;
    color: white;
}

.footer-copy {
    font-size: 14px;
    color: #6a7a9a;
}

/* ===== АДАПТИВ ===== */
@media (max-width: 1024px) {
    .services-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .hero-right {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }

    .burger {
        display: flex;
    }

    .hero-inner,
    .about-inner,
    .contact-inner {
        grid-template-columns: 1fr;
    }

    .hero-title {
        font-size: 36px;
    }

    .hero-right {
        grid-template-columns: 1fr 1fr;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .work-grid {
        grid-template-columns: 1fr;
    }

    .hero-buttons {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        text-align: center;
    }
}
</style>
