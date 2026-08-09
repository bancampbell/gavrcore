import { describe, it, expect, vi, beforeEach } from 'vitest'

import { useMaterials } from '@/modules/MaterialManager/ui/composables/useMaterials'
import { materialApi } from '@/modules/MaterialManager/infrastructure/api/material-api'
import { router } from '@inertiajs/vue3'
import type { Material, MaterialsData, MaterialFilters } from '@/modules/MaterialManager/ui/types'

vi.mock('@/modules/MaterialManager/infrastructure/api/material-api', () => ({
    materialApi: {
        toggleHomepage: vi.fn(),
        moveToTrash: vi.fn(),
        restore: vi.fn(),
        forceDelete: vi.fn(),
        publish: vi.fn(),
        unpublish: vi.fn(),
        emptyTrash: vi.fn(),
    },
}))

vi.mock('@inertiajs/vue3', () => ({
    router: { visit: vi.fn(), get: vi.fn() },
}))

function createMockProps(overrides: {
    data?: Partial<Material>[];
    materials?: Partial<MaterialsData>;
    filters?: Partial<MaterialFilters>;
    perPage?: number;
    isLanding?: boolean;
} = {}): {
    materials: MaterialsData;
    filters?: MaterialFilters;
    perPage?: number;
    isLanding?: boolean;
} {
    const data = (overrides.data ?? []).map((m, i) => createMockMaterial({ id: i + 1, ...m }))
    const materials: MaterialsData = {
        data,
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: data.length,
        from: data.length > 0 ? 1 : null,
        to: data.length > 0 ? data.length : null,
        ...overrides.materials,
    }
    return {
        materials,
        ...(overrides.filters ? { filters: overrides.filters as MaterialFilters } : {}),
        ...(overrides.perPage !== undefined ? { perPage: overrides.perPage } : {}),
        ...(overrides.isLanding !== undefined ? { isLanding: overrides.isLanding } : {}),
    }
}

function createMockMaterial(overrides: Partial<Material> = {}): Material {
    return {
        id: 1,
        title: 'Test',
        slug: 'test',
        alias: 'test',
        content: null,
        category_id: null,
        user_id: 1,
        state: 'draft',
        access: 'public',
        views: 0,
        published_at: null,
        featured: false,
        show_on_homepage: false,
        show_date: true,
        show_author: true,
        show_category: true,
        show_views: true,
        use_global_settings: true,
        template: null,
        meta_title: null,
        meta_description: null,
        meta_keywords: null,
        created_at: '2026-01-01T00:00:00Z',
        updated_at: '2026-01-01T00:00:00Z',
        deleted_at: null,
        status_label: 'Не опубликовано',
        access_label: 'Public',
        status_color: 'rose',
        ...overrides,
    }
}

describe('useMaterials', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('items initialized from props.materials.data', () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1, title: 'First' })],
        })
        const { items } = useMaterials(props)

        expect(items.value).toHaveLength(1)
        expect(items.value[0].title).toBe('First')
    })

    it('items is empty array when no data provided', () => {
        const props = createMockProps()
        const { items } = useMaterials(props)

        expect(items.value).toEqual([])
    })

    it('toggleHomepage performs optimistic update', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1, show_on_homepage: false })],
        })
        const { items, toggleHomepage } = useMaterials(props)

        vi.mocked(materialApi.toggleHomepage).mockResolvedValueOnce({ message: 'OK' })

        await toggleHomepage(items.value[0])

        expect(items.value[0].show_on_homepage).toBe(true)
        expect(materialApi.toggleHomepage).toHaveBeenCalledWith(1, true)
    })

    it('toggleHomepage rolls back on error and shows notification', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1, show_on_homepage: false })],
        })
        const { items, toggleHomepage, notification } = useMaterials(props)

        vi.mocked(materialApi.toggleHomepage).mockRejectedValueOnce(new Error('fail'))

        await toggleHomepage(items.value[0])

        expect(items.value[0].show_on_homepage).toBe(false)
        expect(notification.value.message).toBe('Ошибка при обновлении статуса "На главной"')
        expect(notification.value.type).toBe('error')
    })

    it('toggleHomepage clears other homepage flags when enabling', async () => {
        const props = createMockProps({
            data: [
                createMockMaterial({ id: 1, show_on_homepage: true }),
                createMockMaterial({ id: 2, show_on_homepage: false }),
            ],
        })
        const { items, toggleHomepage } = useMaterials(props)

        vi.mocked(materialApi.toggleHomepage).mockResolvedValueOnce({ message: 'OK' })

        await toggleHomepage(items.value[1])

        expect(items.value[0].show_on_homepage).toBe(false)
        expect(items.value[1].show_on_homepage).toBe(true)
    })

    it('toggleHomepage does nothing when already toggling same id', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1, show_on_homepage: false })],
        })
        const { items, toggleHomepage, togglingHomepageIds } = useMaterials(props)

        togglingHomepageIds.value.push(1)
        vi.mocked(materialApi.toggleHomepage).mockResolvedValueOnce({ message: 'OK' })

        await toggleHomepage(items.value[0])

        expect(materialApi.toggleHomepage).not.toHaveBeenCalled()
    })

    it('moveToTrash calls api and clears selection', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 }), createMockMaterial({ id: 2 })],
        })
        const { selectedMaterials, selectAll, moveToTrash } = useMaterials(props)

        selectAll()
        vi.mocked(materialApi.moveToTrash).mockResolvedValueOnce({ message: 'OK' })

        await moveToTrash()

        expect(materialApi.moveToTrash).toHaveBeenCalledWith([1, 2])
        expect(selectedMaterials.value).toEqual([])
    })

    it('moveToTrash does nothing when no selection', async () => {
        const props = createMockProps({ data: [] })
        const { moveToTrash } = useMaterials(props)

        await moveToTrash()

        expect(materialApi.moveToTrash).not.toHaveBeenCalled()
    })

    it('restoreSelected opens restore modal', () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 })],
        })
        const { toggleSelect, restoreSelected, modalOpen, modalTitle } = useMaterials(props)

        toggleSelect(1)
        restoreSelected()

        expect(modalOpen.value).toBe(true)
        expect(modalTitle.value).toBe('Восстановление материалов')
    })

    it('restoreSelected shows error when no selection', () => {
        const props = createMockProps({ data: [] })
        const { restoreSelected, notification } = useMaterials(props)

        restoreSelected()

        expect(notification.value.message).toBe('Выберите материалы для восстановления')
        expect(notification.value.type).toBe('error')
    })

    it('publishSelected calls api and clears selection', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 })],
        })
        const { selectedMaterials, toggleSelect, publishSelected } = useMaterials(props)

        toggleSelect(1)
        vi.mocked(materialApi.publish).mockResolvedValueOnce({ message: 'Published' })

        await publishSelected()

        expect(materialApi.publish).toHaveBeenCalledWith([1])
        expect(selectedMaterials.value).toEqual([])
    })

    it('unpublishSelected calls api and clears selection', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 })],
        })
        const { selectedMaterials, toggleSelect, unpublishSelected } = useMaterials(props)

        toggleSelect(1)
        vi.mocked(materialApi.unpublish).mockResolvedValueOnce({ message: 'Unpublished' })

        await unpublishSelected()

        expect(materialApi.unpublish).toHaveBeenCalledWith([1])
        expect(selectedMaterials.value).toEqual([])
    })

    it('confirmAction dispatches restore when pendingAction is restore', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 })],
        })
        const { toggleSelect, restoreSelected, confirmAction } = useMaterials(props)

        toggleSelect(1)
        restoreSelected()
        vi.mocked(materialApi.restore).mockResolvedValueOnce({ message: 'Restored' })

        await confirmAction()

        expect(materialApi.restore).toHaveBeenCalledWith([1])
    })

    it('confirmAction dispatches forceDelete when pendingAction is delete', async () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 })],
        })
        const { toggleSelect, openDeleteModal, confirmAction } = useMaterials(props)

        toggleSelect(1)
        openDeleteModal(1)
        vi.mocked(materialApi.forceDelete).mockResolvedValueOnce({ message: 'Deleted' })

        await confirmAction()

        expect(materialApi.forceDelete).toHaveBeenCalledWith([1])
    })

    it('confirmAction dispatches emptyTrash when pendingAction is empty', async () => {
        const props = createMockProps({ data: [] })
        const { openEmptyTrashModal, confirmAction } = useMaterials(props)

        openEmptyTrashModal()
        vi.mocked(materialApi.emptyTrash).mockResolvedValueOnce({ message: 'Emptied' })

        await confirmAction()

        expect(materialApi.emptyTrash).toHaveBeenCalled()
    })

    it('confirmAction does nothing when no pending action', async () => {
        const props = createMockProps({ data: [] })
        const { confirmAction } = useMaterials(props)

        await confirmAction()

        expect(materialApi.restore).not.toHaveBeenCalled()
        expect(materialApi.forceDelete).not.toHaveBeenCalled()
        expect(materialApi.emptyTrash).not.toHaveBeenCalled()
    })

    it('formatDate returns non-empty formatted string for valid ISO date', () => {
        const props = createMockProps({ data: [] })
        const { formatDate } = useMaterials(props)

        const result = formatDate('2026-08-09T14:30:00Z')

        expect(result).toMatch(/^\d{2}\.\d{2}\.\d{2} \d{2}:\d{2}$/)
    })

    it('formatDate returns empty string for empty input', () => {
        const props = createMockProps({ data: [] })
        const { formatDate } = useMaterials(props)

        expect(formatDate('')).toBe('')
    })

    it('editSelected navigates to edit page for single selection', () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 5 })],
        })
        const { toggleSelect, editSelected } = useMaterials(props)

        toggleSelect(5)
        editSelected()

        expect(router.visit).toHaveBeenCalledWith('/admin/materials/5/edit')
    })

    it('editSelected shows error for multiple selection', () => {
        const props = createMockProps({
            data: [createMockMaterial({ id: 1 }), createMockMaterial({ id: 2 })],
        })
        const { toggleSelect, editSelected, notification } = useMaterials(props)

        toggleSelect(1)
        toggleSelect(2)
        editSelected()

        expect(notification.value.message).toBe('Выберите только один материал для редактирования')
    })

    it('editSelected shows error when no selection', () => {
        const props = createMockProps({ data: [] })
        const { editSelected, notification } = useMaterials(props)

        editSelected()

        expect(notification.value.message).toBe('Выберите материал для редактирования')
    })
})
