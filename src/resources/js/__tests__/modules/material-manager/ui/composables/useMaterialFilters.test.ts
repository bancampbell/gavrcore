import { describe, it, expect, vi, beforeEach } from 'vitest'

import { useMaterialFilters } from '@/modules/MaterialManager/ui/composables/useMaterialFilters'

vi.mock('@inertiajs/vue3', () => ({
  router: { get: vi.fn() },
}))

import { router } from '@inertiajs/vue3'

function createProps(overrides: Record<string, unknown> = {}) {
  return {
    materials: {
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      ...(overrides.materials as Record<string, unknown> || {}),
    },
    filters: overrides.filters as Record<string, unknown> | undefined,
    perPage: overrides.perPage as number | undefined,
  }
}

describe('useMaterialFilters', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('initializes filters from props', () => {
    const result = useMaterialFilters(createProps({
      filters: { search: 'hello', state: 'published' },
    }))

    expect(result.filters.value.search).toBe('hello')
    expect(result.filters.value.state).toBe('published')
  })

  it('uses default values when no props provided', () => {
    const result = useMaterialFilters(createProps())

    expect(result.filters.value.search).toBe('')
    expect(result.filters.value.state).toBe('')
    expect(result.filters.value.sort).toBe('id')
    expect(result.filters.value.direction).toBe('desc')
    expect(result.filters.value.category_id).toBeNull()
    expect(result.filters.value.author).toBeNull()
  })

  it('applyFilters calls router.get with all params', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 2, last_page: 5, total: 50 },
      filters: { search: 'test', state: 'draft' },
      perPage: 20,
    }))

    result.applyFilters()

    expect(router.get).toHaveBeenCalledWith(
      '/admin/materials',
      expect.objectContaining({
        search: 'test',
        state: 'draft',
        page: 1,
        per_page: 20,
        sort: 'id',
        direction: 'desc',
      }),
      expect.objectContaining({ preserveState: true, preserveScroll: true }),
    )
  })

  it('resetFilters resets all values and calls router', () => {
    const result = useMaterialFilters(createProps({
      filters: { search: 'hello', state: 'published', category_id: 5 },
    }))

    result.resetFilters()

    expect(result.filters.value.search).toBe('')
    expect(result.filters.value.state).toBe('')
    expect(result.filters.value.category_id).toBeNull()
    expect(router.get).toHaveBeenCalled()
  })

  it('prevPage decrements page when not on first page', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 3, last_page: 5, total: 50 },
    }))

    result.prevPage()

    expect(router.get).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({ page: 2 }),
      expect.any(Object),
    )
  })

  it('prevPage does nothing on first page', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 1, last_page: 5, total: 50 },
    }))

    result.prevPage()

    expect(router.get).not.toHaveBeenCalled()
  })

  it('nextPage increments page when not on last page', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 3, last_page: 5, total: 50 },
    }))

    result.nextPage()

    expect(router.get).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({ page: 4 }),
      expect.any(Object),
    )
  })

  it('nextPage does nothing on last page', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 5, last_page: 5, total: 50 },
    }))

    result.nextPage()

    expect(router.get).not.toHaveBeenCalled()
  })

  it('toggleSort toggles direction on same column', () => {
    const result = useMaterialFilters(createProps({
      filters: { sort: 'id', direction: 'asc' },
    }))

    result.toggleSort('id')

    expect(result.filters.value.direction).toBe('desc')
  })

  it('toggleSort changes column and resets to asc', () => {
    const result = useMaterialFilters(createProps({
      filters: { sort: 'id', direction: 'desc' },
    }))

    result.toggleSort('title')

    expect(result.filters.value.sort).toBe('title')
    expect(result.filters.value.direction).toBe('asc')
  })

  it('changePerPage calls applyFilters with page 1', () => {
    const result = useMaterialFilters(createProps({
      materials: { current_page: 3, last_page: 5, total: 50 },
    }))

    result.changePerPage()

    expect(router.get).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({ page: 1 }),
      expect.any(Object),
    )
  })
})
