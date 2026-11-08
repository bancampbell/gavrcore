import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'

import ToastContainer from '@/components/shared/ToastContainer.vue'
import { useToast } from '@/composables/useToast'

describe('ToastContainer', () => {
    beforeEach(() => {
        useToast().clearAll()
        document.body.innerHTML = ''
    })

    const mountContainer = () => mount(ToastContainer, { attachTo: document.body })

    it('info-уведомление рендерится нейтральным синим, success/error — своими стилями', async () => {
        const { success, error, info } = useToast()
        success('Сохранено')
        error('Ошибка')
        info('Справочно')

        mountContainer()

        const toasts = document.body.querySelectorAll('.px-4.py-3')
        expect(toasts.length).toBe(3)
        // success — зелёный фон через inline-style, текст белый
        expect(toasts[0].className).toContain('text-white')
        expect(toasts[0].getAttribute('style')).toContain('background-color: rgb(77, 182, 77)')
        // error — rose
        expect(toasts[1].className).toContain('bg-rose-500')
        // info — нейтральный синий (квирка «всё, кроме success, рендерится rose», TECHDEBT п.35)
        expect(toasts[2].className).toContain('bg-blue-500')
        expect(toasts[2].className).not.toContain('bg-rose-500')
    })
})
