import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

import CategorySelectorModal from '@/modules/MenuManager/ui/components/CategorySelectorModal.vue'

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
    },
}))

import axios from 'axios'

// Ответ GET /admin/categories/tree: дерево категорий (обёртка data ресурса Laravel)
const tree = [
    {
        id: 1,
        name: 'Новости',
        alias: 'novosti',
        depth: 0,
        children: [
            { id: 2, name: 'Архив', alias: 'novosti-arkhiv', depth: 1, children: [] },
        ],
    },
    { id: 3, name: 'О компании', alias: 'o-kompanii', depth: 0, children: [] },
]

describe('CategorySelectorModal', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        document.body.innerHTML = ''
    })

    const openModal = async () => {
        const wrapper = mount(CategorySelectorModal, {
            props: { show: false },
            attachTo: document.body,
        })
        await wrapper.setProps({ show: true })
        await flushPromises()
        return wrapper
    }

    it('загружает дерево категорий из /admin/categories/tree и разворачивает его в плоский список', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: { data: tree } })

        await openModal()

        expect(axios.get).toHaveBeenCalledTimes(1)
        expect(vi.mocked(axios.get).mock.calls[0][0]).toBe('/admin/categories/tree')
        expect(document.body.textContent).toContain('Новости')
        expect(document.body.textContent).toContain('Архив')
        expect(document.body.textContent).toContain('О компании')
        expect(document.body.textContent).not.toContain('Категории не найдены')
    })

    it('при выборе эмитит select с id, name и alias (заполняет пункт меню)', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: { data: tree } })
        const wrapper = await openModal()

        const rows = document.body.querySelectorAll('.category-list-wrapper .flex.px-4')
        expect(rows.length).toBe(3)
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
        expect(events![0]).toEqual([{ id: 1, name: 'Новости', alias: 'novosti' }])
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('фильтрует список по поиску на клиенте', async () => {
        vi.mocked(axios.get).mockResolvedValueOnce({ data: { data: tree } })
        await openModal()

        const input = document.body.querySelector('.filters-bar input[type="text"]') as HTMLInputElement
        input.value = 'архив'
        input.dispatchEvent(new Event('input'))
        await new Promise((r) => setTimeout(r, 350))
        await flushPromises()

        expect(axios.get).toHaveBeenCalledTimes(1)
        expect(document.body.textContent).toContain('Архив')
        expect(document.body.textContent).not.toContain('О компании')
    })
})
