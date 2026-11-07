<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

// --- Модальные окна ---
const showModal2 = ref(false)

function openModal2() { showModal2.value = true }
function closeModal2() { showModal2.value = false }

// --- Мобильное меню ---
const mobileMenu = ref(false)
const heroFormData = ref({ name: '', phone: '' })

const submitHeroForm = () => {
    alert('Спасибо! Мы свяжемся с вами в ближайшее время.')
    heroFormData.value = { name: '', phone: '' }
}

// --- Анимации при скролле ---
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animate-visible')
                    }, 100 * index)
                    observer.unobserve(entry.target)
                }
            })
        },
        { threshold: 0.2 }
    )

    nextTick(() => {
        document.querySelectorAll('.animate-visible:not(.animate-visible--done)').forEach(el => {
            observer.observe(el)
        })
    })
})

// ===== ДАННЫЕ =====
const services = ref([
    { id: 1, title: 'Утепление фасадов', text: 'Современные материалы и технологии для максимальной теплоизоляции' },
    { id: 2, title: 'Утепление балконов', text: 'Превращаем холодные балконы в тёплые и уютные помещения' },
    { id: 3, title: 'Гидроизоляция', text: 'Надёжная защита от влаги и протечек для любых конструкций' }
])

const aboutFacts = ref([
    { title: '10+ лет на рынке', text: 'За это время мы реализовали более 150 проектов по всей стране.' },
    { title: 'Команда профессионалов', text: 'В штате только сертифицированные специалисты с допусками к высотным работам.' },
    { title: 'Гарантия качества', text: 'Даём гарантию до 5 лет на все виды работ.' }
])

const faqs = ref([
    { question: 'Сколько времени занимает утепление?', answer: 'В среднем от 2 до 5 дней, в зависимости от объёма работ.' },
    { question: 'Нужно ли освобождать помещение?', answer: 'Нет, мы работаем снаружи, не мешая внутренним процессам.' },
    { question: 'Даёте ли вы гарантию?', answer: 'Да, гарантия до 5 лет на все виды работ.' },
    { question: 'Работаете по договору?', answer: 'Да, всегда заключаем официальный договор.' }
])

// --- Активное меню ---
const activeSection = ref('hero')

const updateActiveSection = () => {
    const sections = ['hero', 'services', 'about', 'portfolio', 'testimonials', 'faq', 'calculator']
    const scrollPosition = window.scrollY + 120

    for (const section of sections) {
        const element = document.getElementById(section)
        if (element) {
            const offsetTop = element.offsetTop
            const offsetBottom = offsetTop + element.offsetHeight
            if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                activeSection.value = section
                break
            }
        }
    }
}

// --- Кнопка "наверх" ---
const showScrollTop = ref(false)

const handleScroll = () => {
    showScrollTop.value = window.scrollY > 400
    updateActiveSection()
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
    updateActiveSection()
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Плавный скролл к секции
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId)
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
    if (mobileMenu.value) {
        mobileMenu.value = false
    }
}

// ===== КАЛЬКУЛЯТОР =====
const calcData = ref({
    objectType: 'apartment',
    floor: 1,
    option: 'comfort',
    width: 10,
    height: 3,
    material: 'polystyrene',
    thickness: 50,
    hasWindow: false,
    windows: [{ width: 1.5, height: 1.5, count: 1 }],
    needDrip: false,
    dripLength: 0,
})

// Контактный телефон для отправки расчета
const clientPhone = ref('')

// Цены из таблиц (можно редактировать через админку)
const prices = ref({
    table1: {
        polystyrene: { 50: { comfort: 2600, premium: 3300 }, 100: { comfort: 2800, premium: 3500 } },
        extruded: { 50: { comfort: 3000, premium: 3700 }, 100: { comfort: 3500, premium: 4200 } },
        basalt: { 50: { comfort: 3200, premium: 3900 }, 100: { comfort: 4000, premium: 4700 } }
    },
    table2: {
        dripMounting: 600
    },
    table3: {
        plasterSlopes: 700,
        windowDripReplacement: 1500
    }
})

// Добавить окно
const addWindow = () => {
    calcData.value.windows.push({ width: 1.5, height: 1.5, count: 1 })
}

// Удалить окно
const removeWindow = (index) => {
    if (calcData.value.windows.length > 1) {
        calcData.value.windows.splice(index, 1)
    }
}

// Расчеты (автоматические)
const calculated = computed(() => {
    const data = calcData.value
    let errors = []

    // Проверка заполнения
    if (!data.width || data.width <= 0) errors.push('Ширина утепления')
    if (!data.height || data.height <= 0) errors.push('Высота утепления')
    if (!data.objectType) errors.push('Объект утепления')
    if (!data.floor || data.floor <= 0) errors.push('Этаж утепления')

    // Проверка окон
    data.windows.forEach((w, i) => {
        if (!w.width || w.width <= 0) errors.push(`Ширина окна ${i+1}`)
        if (!w.height || w.height <= 0) errors.push(`Высота окна ${i+1}`)
        if (!w.count || w.count <= 0) errors.push(`Количество окон ${i+1}`)
    })

    // Высота по умолчанию 3м для квартиры
    let height = data.height
    if (data.objectType === 'apartment' && !data.height) {
        height = 3
    }

    // Площадь окон
    let windowArea = 0
    let totalWindowCount = 0
    let totalSlopeLength = 0

    data.windows.forEach(w => {
        const area = w.width * w.height * w.count
        windowArea += area
        totalWindowCount += w.count
        totalSlopeLength += (w.width + w.height * 2) * w.count
    })

    // Площадь утепления
    const insulationArea = data.width * height - windowArea

    // Длина отливов
    let dripLength = data.dripLength
    if (!dripLength || dripLength <= 0) {
        dripLength = data.width * 1.1
    }

    // Стоимость из Таблицы 1
    let materialKey = data.material
    let thicknessKey = data.thickness
    let optionKey = data.option

    let pricePerM2 = 0
    if (prices.value.table1[materialKey] && prices.value.table1[materialKey][thicknessKey]) {
        pricePerM2 = prices.value.table1[materialKey][thicknessKey][optionKey] || 0
    }

    const table1Cost = insulationArea * pricePerM2

    // Стоимость из Таблицы 2
    const table2Cost = dripLength * prices.value.table2.dripMounting

    // Стоимость из Таблицы 3
    const table3Cost = (totalSlopeLength * prices.value.table3.plasterSlopes) +
        (totalWindowCount * prices.value.table3.windowDripReplacement)

    // Итоговая стоимость
    const totalCost = table1Cost + table2Cost + table3Cost

    return {
        insulationArea: insulationArea,
        windowArea: windowArea,
        totalWindowCount: totalWindowCount,
        totalSlopeLength: totalSlopeLength,
        dripLength: dripLength,
        table1Cost: table1Cost,
        table2Cost: table2Cost,
        table3Cost: table3Cost,
        totalCost: totalCost,
        errors: errors,
        hasErrors: errors.length > 0,
        height: height,
        pricePerM2: pricePerM2
    }
})

// Отправить расчет
const sendCalculation = () => {
    if (!clientPhone.value || clientPhone.value.length < 10) {
        alert('Пожалуйста, укажите номер телефона для связи!')
        return
    }

    if (calculated.value.hasErrors) {
        alert('Заполнены не все поля! Пожалуйста, заполните все обязательные поля.')
        return
    }

    const result = calculated.value
    const message = `
📋 РАСЧЕТ СТОИМОСТИ УТЕПЛЕНИЯ

📞 Телефон клиента: ${clientPhone.value}

🏗 Объект: ${calcData.value.objectType === 'apartment' ? 'Квартира' : calcData.value.objectType === 'house' ? 'Частный дом' : 'Балкон (лоджия)'}
📌 Этаж: ${calcData.value.floor}
📐 Ширина: ${calcData.value.width} м
📏 Высота: ${result.height} м
🧱 Материал: ${calcData.value.material === 'polystyrene' ? 'Пенополистирол' : calcData.value.material === 'basalt' ? 'Базальтовая плита' : 'Экструдированный пенополистирол'}
📊 Толщина: ${calcData.value.thickness} мм
🎨 Вариант: ${calcData.value.option === 'comfort' ? 'Комфорт' : 'Премиум'}

📐 Площадь утепления: ${result.insulationArea.toFixed(2)} м²
🪟 Площадь окон: ${result.windowArea.toFixed(2)} м²

💰 ИТОГО: ${Math.round(result.totalCost).toLocaleString()} ₽
    `

    alert(`✅ Заявка отправлена!\n\n${message}`)

    // Очищаем телефон
    clientPhone.value = ''
}
</script>

<template>
    <div class="relative overflow-hidden min-h-screen">
        <!-- ===== НАВБАР ===== -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-orange-200/30 shadow-sm transition-all duration-300">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <div class="flex justify-between items-center h-16 md:h-20">
                    <a href="#" @click.prevent="scrollToTop" class="text-2xl font-bold text-orange-600 tracking-tight">
                        <span class="text-orange-500">▲</span> Alpine
                    </a>

                    <div class="hidden lg:flex gap-8">
                        <a href="#" @click.prevent="scrollToSection('hero')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'hero' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            Главная
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'hero' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>
                        <a href="#" @click.prevent="scrollToSection('services')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'services' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            Услуги
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'services' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>
                        <a href="#" @click.prevent="scrollToSection('about')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'about' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            О компании
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'about' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>
                        <a href="#" @click.prevent="scrollToSection('portfolio')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'portfolio' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            Портфолио
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'portfolio' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>
                        <a href="#" @click.prevent="scrollToSection('testimonials')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'testimonials' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            Отзывы
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'testimonials' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>
                        <a href="#" @click.prevent="scrollToSection('faq')"
                           class="text-sm uppercase tracking-widest font-bold transition-colors relative group cursor-pointer"
                           :class="activeSection === 'faq' ? 'text-orange-500' : 'text-gray-800 hover:text-orange-500'">
                            FAQ
                            <span class="absolute -bottom-1 left-0 h-0.5 bg-orange-500 transition-all duration-300"
                                  :class="activeSection === 'faq' ? 'w-full' : 'w-0 group-hover:w-full'"></span>
                        </a>

                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="scrollToSection('calculator')" class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-5 py-2 rounded-full text-sm font-bold hover:scale-105 transition-all shadow-lg shadow-orange-500/30 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25Z" />
                            </svg>
                            Калькулятор
                        </button>

                        <button @click="mobileMenu = !mobileMenu" class="lg:hidden text-gray-600 hover:text-orange-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M3.75 12h16.5m-16.5 6h16.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Мобильное меню -->
                <div v-show="mobileMenu" class="lg:hidden py-4 border-t border-orange-100/50 bg-white/95 backdrop-blur-sm rounded-b-2xl transition-all duration-300">
                    <a href="#" @click.prevent="scrollToSection('hero')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'hero' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">Главная</a>
                    <a href="#" @click.prevent="scrollToSection('services')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'services' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">Услуги</a>
                    <a href="#" @click.prevent="scrollToSection('about')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'about' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">О компании</a>
                    <a href="#" @click.prevent="scrollToSection('portfolio')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'portfolio' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">Портфолио</a>
                    <a href="#" @click.prevent="scrollToSection('testimonials')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'testimonials' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">Отзывы</a>
                    <a href="#" @click.prevent="scrollToSection('faq')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'faq' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">FAQ</a>
                    <a href="#" @click.prevent="scrollToSection('calculator')" class="block py-2.5 px-3 rounded-lg transition-all font-bold"
                       :class="activeSection === 'calculator' ? 'text-orange-500 bg-orange-50' : 'text-gray-800 hover:text-orange-500 hover:bg-orange-50'">Калькулятор</a>
                    <button @click="scrollToSection('calculator')" class="mt-2 block w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white text-center px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25Z" />
                        </svg>
                        Калькулятор
                    </button>
                </div>
            </div>
        </nav>

        <!-- ===== HERO ===== -->
        <section id="hero" class="relative min-h-screen flex items-center pt-24 pb-12 px-4 sm:px-6 lg:px-8 scroll-mt-20"
                 style="background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 30%, #f97316 60%, #ea580c 100%);">
            <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-white/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-white/10 rounded-full blur-[120px]"></div>

            <div class="relative z-10 container mx-auto max-w-7xl">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="animate-visible animate-visible--done">
                        <div class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold text-white border border-white/30 mb-6">
                            🚀 Высотные работы премиум-класса
                        </div>

                        <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold text-white leading-[1.05] tracking-tight drop-shadow-2xl">
                            Утепление с альпинизмом
                        </h1>

                        <p class="mt-4 text-lg sm:text-xl text-white/90 max-w-2xl drop-shadow-lg">
                            Работаем с объектами любой сложности по всей России. Гарантия качества, безопасность и безупречный результат.
                        </p>

                        <div class="flex flex-wrap items-center gap-x-8 gap-y-4 mt-6 text-sm font-bold tracking-widest text-white/90">
                            <span>10+ ЛЕТ ОПЫТА</span>
                            <span class="hidden sm:inline text-white/40">|</span>
                            <span>150+ ОБЪЕКТОВ</span>
                            <span class="hidden sm:inline text-white/40">|</span>
                            <span>98% ДОВОЛЬНЫХ</span>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-8">
                            <a href="#" @click.prevent="scrollToSection('services')" class="px-8 py-3 bg-white text-gray-900 rounded-xl font-bold hover:scale-105 transition-all shadow-2xl shadow-black/30 hover:shadow-black/50">
                                Наши услуги
                            </a>
                        </div>

                        <p class="mt-6 text-white/70 max-w-2xl text-sm leading-relaxed">
                            Мы — специалисты промышленного альпинизма. Утепляем фасады, балконы и крыши с использованием передовых материалов и технологий.
                        </p>
                    </div>

                    <div class="bg-white-custom rounded-3xl border border-white/40 shadow-2xl p-8 animate-visible animate-visible--done" style="animation-delay: 300ms;">
                        <h3 class="text-2xl font-bold text-white text-center mb-2">Получить персональное предложение</h3>
                        <p class="text-white/80 text-sm text-center mb-6">Заполните форму и мы свяжемся с вами</p>

                        <form @submit.prevent="submitHeroForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white/90 mb-1">Ваше имя</label>
                                <input type="text" v-model="heroFormData.name" required
                                       placeholder="Иван Иванов"
                                       class="w-full px-4 py-3 bg-white/20 rounded-xl border border-white/30 text-white placeholder-white/60 focus:border-white focus:ring-2 focus:ring-white/30 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white/90 mb-1">Номер телефона</label>
                                <input type="tel" v-model="heroFormData.phone" required
                                       placeholder="+7 (999) 123-45-67"
                                       class="w-full px-4 py-3 bg-white/20 rounded-xl border border-white/30 text-white placeholder-white/60 focus:border-white focus:ring-2 focus:ring-white/30 outline-none transition-all">
                            </div>
                            <button type="submit" class="w-full px-6 py-3.5 bg-white text-gray-900 rounded-xl font-bold hover:scale-105 transition-all shadow-xl shadow-black/30 text-lg border-2 border-white/50 hover:bg-white/90">
                                Получить предложение
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== УСЛУГИ ===== -->
        <section id="services" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 bg-white scroll-mt-20">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16 animate-visible">
                    <span class="text-sm font-semibold text-orange-500 uppercase tracking-wider">Услуги</span>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-2">Что мы предлагаем</h2>
                    <p class="mt-3 text-gray-500 max-w-2xl mx-auto">Профессиональные услуги промышленного альпинизма для вашего бизнеса</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(service, idx) in services" :key="idx"
                         class="group bg-gray-50 p-6 rounded-2xl border border-gray-200 hover:border-orange-400 hover:shadow-xl transition-all hover:-translate-y-1 cursor-pointer animate-visible"
                         :style="{ animationDelay: `${100 * (idx + 1)}ms` }"
                         @click="openModal2">
                        <div class="text-5xl mb-4">🏗️</div>
                        <h3 class="text-xl font-bold text-gray-800">{{ service.title }}</h3>
                        <p class="mt-2 text-gray-500 text-sm leading-relaxed">{{ service.text }}</p>
                        <div class="mt-4 text-orange-500 font-semibold text-sm group-hover:translate-x-2 transition-all">Подробнее →</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== О КОМПАНИИ ===== -->
        <section id="about" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 scroll-mt-20"
                 style="background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 30%, #e8e8e8 60%, #f0f0f0 100%);">
            <div class="container mx-auto max-w-6xl">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="animate-visible">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">О компании</span>
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mt-2">
                            Надёжный партнёр
                            <span class="block text-orange-500">с альпинизмом</span>
                        </h2>
                        <p class="mt-4 text-gray-600 leading-relaxed text-lg">
                            Alpine — это команда профессионалов, объединённая страстью к качественному строительству. Мы создаём объекты, которыми гордимся.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li v-for="(fact, idx) in aboutFacts" :key="idx" class="flex items-start gap-3 text-gray-700 animate-visible" :style="{ animationDelay: `${150 * (idx + 1)}ms` }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-orange-500 mt-1 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <span class="font-semibold">{{ fact.title }}</span>
                                    <p class="text-gray-500 text-sm">{{ fact.text }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white/70 backdrop-blur-sm rounded-3xl h-64 flex items-center justify-center text-gray-400 font-medium border border-gray-200/30 shadow-xl animate-visible" style="animation-delay: 300ms;">
                        🏗️ Проекты Alpine
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== ПОРТФОЛИО ===== -->
        <section id="portfolio" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 bg-white scroll-mt-20">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16 animate-visible">
                    <span class="text-sm font-semibold text-orange-500 uppercase tracking-wider">Портфолио</span>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-2">Наши проекты</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(project, index) in [
                        { icon: '🏢', title: 'ЖК Солнечный', text: '24 этажа, утепление фасада' },
                        { icon: '🏢', title: 'БЦ Деловой', text: '16 этажей, мойка и герметизация' },
                        { icon: '🏢', title: 'ТЦ Гранд', text: '12 этажей, монтаж вентфасада' }
                    ]" :key="index"
                         class="group bg-gray-50 rounded-2xl overflow-hidden border border-gray-200 hover:border-orange-400 hover:shadow-xl transition-all hover:-translate-y-1 animate-visible"
                         :style="{ animationDelay: `${100 * (index + 1)}ms` }">
                        <div class="h-48 bg-gradient-to-br from-orange-200 to-amber-200 flex items-center justify-center text-6xl">
                            {{ project.icon }}
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-gray-800">{{ project.title }}</h4>
                            <p class="text-gray-500 text-sm mt-1">{{ project.text }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== ОТЗЫВЫ ===== -->
        <section id="testimonials" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 scroll-mt-20"
                 style="background: linear-gradient(135deg, #fdf2f0 0%, #f8e4e0 30%, #f5ddd8 60%, #f8e8e4 100%);">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16 animate-visible">
                    <span class="text-sm font-semibold text-rose-400 uppercase tracking-wider">Отзывы</span>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mt-2">Что говорят клиенты</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(review, index) in [
                        { name: 'Иван Петров', company: 'ООО СтройГарант', text: 'Отличная работа! Утеплили фасад за 3 дня. Рекомендую.' },
                        { name: 'Мария Смирнова', company: 'ЖК Солнечный', text: 'Профессиональный подход, все быстро и качественно.' },
                        { name: 'Алексей Иванов', company: 'ООО Уютный дом', text: 'Сделали утепление балкона — теперь тепло и уютно.' }
                    ]" :key="index"
                         class="bg-white/70 backdrop-blur-sm p-6 rounded-2xl border border-rose-200/30 hover:bg-white/90 transition-all shadow-sm animate-visible"
                         :style="{ animationDelay: `${100 * (index + 1)}ms` }">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-300 to-rose-400 flex items-center justify-center text-white font-bold text-lg">
                                {{ review.name[0] }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">{{ review.name }}</div>
                                <div class="text-sm text-gray-500">{{ review.company }}</div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed">"{{ review.text }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FAQ ===== -->
        <section id="faq" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 bg-white scroll-mt-20">
            <div class="container mx-auto max-w-4xl">
                <div class="text-center mb-16 animate-visible">
                    <span class="text-sm font-semibold text-orange-500 uppercase tracking-wider">FAQ</span>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-2">Часто задаваемые вопросы</h2>
                </div>
                <div class="space-y-4">
                    <div v-for="(faq, idx) in faqs" :key="idx"
                         class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden hover:border-orange-300 transition-all animate-visible"
                         :style="{ animationDelay: `${100 * (idx + 1)}ms` }">
                        <button @click="faq.open = !faq.open" class="w-full px-6 py-4 flex justify-between items-center text-left">
                            <span class="font-semibold text-gray-800">{{ faq.question }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-orange-500 transition-transform duration-300" :class="{ 'rotate-180': faq.open }">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                        <div v-show="faq.open" class="px-6 pb-4">
                            <p class="text-gray-500 text-sm leading-relaxed">{{ faq.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== КАЛЬКУЛЯТОР ===== -->
        <section id="calculator" class="py-20 md:py-32 px-4 sm:px-6 lg:px-8 scroll-mt-20"
                 style="background: linear-gradient(135deg, #fef5ed 0%, #fae8d8 30%, #f5dfcc 60%, #faeade 100%);">
            <div class="container mx-auto max-w-6xl">
                <div class="grid md:grid-cols-2 gap-12">
                    <div class="animate-visible">
                        <span class="text-sm font-semibold text-orange-400 uppercase tracking-wider">Калькулятор</span>
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mt-2">Онлайн калькулятор</h2>
                        <p class="mt-4 text-gray-600">Рассчитайте примерную стоимость утепления вашего объекта.</p>
                        <div class="mt-8 space-y-4">
                            <a href="tel:+79370063063" class="block text-2xl font-bold text-gray-800 hover:text-orange-500 transition-colors">+7 (937) 006-30-63</a>
                            <a href="mailto:info@alpine.ru" class="block text-gray-600 hover:text-orange-500 transition-colors">info@alpine.ru</a>
                            <p class="text-gray-400">📍 Москва, ул. Строителей, 15</p>
                        </div>
                    </div>

                    <!-- Калькулятор -->
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl border border-orange-200/30 shadow-xl animate-visible" style="animation-delay: 300ms;">
                        <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">Калькулятор стоимости</h3>

                        <div class="space-y-4 max-h-[650px] overflow-y-auto pr-2 custom-scroll">
                            <!-- 1. Объект утепления -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Объект утепления</label>
                                <select v-model="calcData.objectType" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option value="apartment">Квартира</option>
                                    <option value="house">Частный дом</option>
                                    <option value="balcony">Балкон (лоджия)</option>
                                </select>
                            </div>

                            <!-- 2. Этаж -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Этаж утепления</label>
                                <input type="number" v-model.number="calcData.floor" min="1"
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                            </div>

                            <!-- 3. Вариант утепления -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Вариант утепления</label>
                                <select v-model="calcData.option" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option value="comfort">Комфорт (Утеплитель, сетка, штукатурка, покраска)</option>
                                    <option value="premium">Премиум (Утеплитель, сетка, штукатурка, декоративная штукатурка «Короед» или «Шуба», покраска)</option>
                                </select>
                            </div>

                            <!-- 4. Ширина утепления -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ширина утепления, м</label>
                                <input type="number" v-model.number="calcData.width" min="0.01" step="0.01" max="999.99"
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                            </div>

                            <!-- 5. Высота утепления -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Высота утепления, м</label>
                                <input type="number" v-model.number="calcData.height" min="0.01" step="0.01" max="99.99"
                                       :placeholder="calcData.objectType === 'apartment' ? 'По умолчанию 3.0' : ''"
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                <p v-if="calcData.objectType === 'apartment'" class="text-xs text-gray-400 mt-1">* Для квартиры по умолчанию 3м</p>
                            </div>

                            <!-- 6. Материал утеплителя -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Материал утеплителя</label>
                                <select v-model="calcData.material" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option value="polystyrene">Пенополистирол (пенопласт)</option>
                                    <option value="basalt">Базальтовая плита (минеральная вата)</option>
                                    <option value="extruded">Экструдированный пенополистирол (пеноплекс, аналоги)</option>
                                </select>
                            </div>

                            <!-- 7. Толщина утеплителя -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Толщина утеплителя, мм</label>
                                <select v-model="calcData.thickness" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                            </div>

                            <!-- 8. Есть окно -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Есть окно</label>
                                <select v-model="calcData.hasWindow" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option :value="false">Нет</option>
                                    <option :value="true">Да</option>
                                </select>
                            </div>

                            <!-- Окна -->
                            <div v-if="calcData.hasWindow" class="space-y-3 bg-orange-50/50 p-4 rounded-xl border border-orange-200/50">
                                <div v-for="(window, index) in calcData.windows" :key="index" class="border-b border-orange-200/30 pb-3 last:border-0">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-medium text-gray-700 text-sm">Окно {{ index + 1 }}</span>
                                        <button v-if="calcData.windows.length > 1" @click="removeWindow(index)" class="text-red-400 hover:text-red-600 text-sm">
                                            ✕ Удалить
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-500">Ширина, м</label>
                                            <input type="number" v-model.number="window.width" min="0.01" step="0.01"
                                                   class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500">Высота, м</label>
                                            <input type="number" v-model.number="window.height" min="0.01" step="0.01"
                                                   class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500">Кол-во, шт</label>
                                            <input type="number" v-model.number="window.count" min="1" step="1"
                                                   class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all text-sm">
                                        </div>
                                    </div>
                                </div>
                                <button @click="addWindow" class="text-orange-500 hover:text-orange-600 text-sm font-medium flex items-center gap-1">
                                    <span>+</span> Добавить окно
                                </button>
                            </div>

                            <!-- 10. Металлические отливы сверху -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Металлические отливы сверху</label>
                                <select v-model="calcData.needDrip" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                    <option :value="false">Не нужны</option>
                                    <option :value="true">Нужны</option>
                                </select>
                            </div>

                            <div v-if="calcData.needDrip">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Длина отливов, пог.м</label>
                                <input type="number" v-model.number="calcData.dripLength" min="0.01" step="0.01"
                                       :placeholder="`По умолчанию ${(calcData.width * 1.1).toFixed(2)}`"
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                                <p class="text-xs text-gray-400 mt-1">* По умолчанию: ширина × 1.1</p>
                            </div>

                            <!-- Результат (автоматический расчет) -->
                            <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-5 rounded-xl border border-orange-200/50 mt-4">
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Площадь утепления:</span>
                                        <span class="font-semibold">{{ calculated.insulationArea.toFixed(2) }} м²</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Площадь окон:</span>
                                        <span class="font-semibold">{{ calculated.windowArea.toFixed(2) }} м²</span>
                                    </div>
                                    <div class="flex justify-between border-t border-orange-200/50 pt-2 mt-2">
                                        <span class="text-gray-800 font-bold text-base">Примерная стоимость:</span>
                                        <span class="text-2xl font-bold text-orange-600">{{ Math.round(calculated.totalCost).toLocaleString() }} ₽</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">* Точная стоимость рассчитывается после выезда специалиста</p>
                                    <p v-if="calculated.hasErrors" class="text-xs text-red-500 mt-1">
                                        ⚠️ Заполнены не все поля
                                    </p>
                                </div>
                            </div>

                            <!-- Поле для телефона -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ваш номер телефона для связи *</label>
                                <input type="tel" v-model="clientPhone"
                                       placeholder="+7 (999) 123-45-67"
                                       class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                            </div>

                            <!-- Кнопка отправки -->
                            <button @click="sendCalculation" class="w-full px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl font-bold hover:scale-105 transition-all shadow-xl shadow-orange-500/30">
                                📩 Отправить расчет
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="py-6 bg-white border-t border-gray-200 px-4 sm:px-6 lg:px-8">
            <div class="container mx-auto max-w-6xl flex flex-col md:flex-row justify-between items-center gap-4">
                <span class="font-bold text-lg text-orange-600">Alpine</span>
                <span class="text-sm text-gray-400">© 2026 Все права защищены</span>
            </div>
        </footer>

        <!-- ===== МОДАЛКА ===== -->
        <div v-if="showModal2" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300" @click.self="closeModal2">
            <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl transform transition-all duration-300 scale-100">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Рассчитать стоимость</h3>
                <form @submit.prevent="closeModal2">
                    <input type="text" placeholder="Ваше имя" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 mb-3 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    <input type="tel" placeholder="Номер телефона *" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 mb-3 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    <input type="text" placeholder="Тип объекта" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 mb-3 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white py-3 rounded-xl font-bold hover:scale-105 transition-all">Отправить</button>
                </form>
                <button @click="closeModal2" class="mt-4 text-gray-400 hover:text-gray-600 transition-all text-sm">Закрыть</button>
            </div>
        </div>

        <!-- ===== КНОПКА "НАВЕРХ" ===== -->
        <button
            v-show="showScrollTop"
            @click="scrollToTop"
            class="fixed bottom-8 right-8 z-50 bg-gradient-to-r from-orange-500 to-amber-500 text-white p-3 rounded-full shadow-2xl shadow-orange-500/30 hover:scale-110 transition-all duration-300 focus:outline-none"
            aria-label="Наверх"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
            </svg>
        </button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            formData: {
                name: '',
                phone: '',
                message: ''
            }
        }
    },
    methods: {
        handleSubmit() {
            alert('Форма отправлена!')
            this.formData = { name: '', phone: '', message: '' }
        }
    }
}
</script>

<style scoped>
* {
    box-sizing: border-box;
}

.container {
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}

html {
    scroll-behavior: smooth;
}

.bg-white-custom {
    background-color: rgba(255, 255, 255, 0.34);
    backdrop-filter: blur(8px);
}

.animate-visible {
    opacity: 0;
    animation: fadeInUp 0.7s ease forwards;
}
.animate-visible--done {
    opacity: 1;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #f97316;
    border-radius: 10px;
}
</style>
