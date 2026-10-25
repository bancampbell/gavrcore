import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

import MaterialSelectorModal from '@/modules/MenuManager/ui/components/MaterialSelectorModal.vue'

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
    },
}))

import axios from 'axios'

const materials = [
    { id: 1, title: 'Программа долгосрочных сбережений', slug: 'pds', state: 'published' },
    { id: 2, title: 'Черновик новости', slug: 'draft-news', state: 'draft' },
]

describe('MaterialSelectorModal', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        document.body.innerHTML = ''
    })

    const openModal = async () => {
        const wrapper = mount(MaterialSelectorModal, {
            props: { show: false },
            attachTo: document.body,
        })
        await wrapper.setProps({ show: true })
        await flushPromises()
        return wrapper
    }

    it('загружает материалы из рабочего эндпоинта /admin/materials/list без Bearer', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: materials })

        await openModal()

        expect(axios.get).toHaveBeenCalledTimes(1)
        const [url, config] = vi.mocked(axios.get).mock.calls[0]
        expect(url).toBe('/admin/materials/list')
        expect(config?.headers?.Authorization ?? null).toBeNull()
    })

    it('рендерит голый массив материалов (формат /admin/materials/list)', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: materials })

        await openModal()

        expect(document.body.textContent).toContain('Программа долгосрочных сбережений')
        expect(document.body.textContent).toContain('Черновик новости')
        expect(document.body.textContent).not.toContain('Материалы не найдены')
    })

    it('при выборе эмитит select с id, title и slug (заполняет пункт меню)', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: materials })
        const wrapper = await openModal()

        const rows = document.body.querySelectorAll('.material-list-wrapper .flex.px-4')
        expect(rows.length).toBe(2)
        await (rows[0] as HTMLElement).click()
        await flushPromises()

        const confirmBtn = Array.from(document.body.querySelectorAll('button')).find(
            (b) => b.textContent?.trim() === 'Выбрать',
        ) as HTMLButtonElement
        expect(confirmBtn.disabled).toBe(false)
        await confirmBtn.click()
        await flushPromises()

        const events = wrapper.emitted('select')
        expect(events).toBeTruthy()
        expect(events![0]).toEqual([{ id: 1, title: 'Программа долгосрочных сбережений', slug: 'pds' }])
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('фильтрует список по поиску на клиенте', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: materials })
        await openModal()

        const input = document.body.querySelector('.filters-bar input[type="text"]') as HTMLInputElement
        input.value = 'черновик'
        input.dispatchEvent(new Event('input'))
        await new Promise((r) => setTimeout(r, 350))
        await flushPromises()

        expect(axios.get).toHaveBeenCalledTimes(1)
        expect(document.body.textContent).toContain('Черновик новости')
        expect(document.body.textContent).not.toContain('Программа долгосрочных сбережений')
    })
})
