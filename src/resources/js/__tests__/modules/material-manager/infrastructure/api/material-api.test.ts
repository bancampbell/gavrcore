import { describe, it, expect, vi, beforeEach } from 'vitest'

import { materialApi } from '@/modules/MaterialManager/infrastructure/api/material-api'

// material-axios.ts экспортирует default, material-api.ts импортирует его как default
vi.mock('@/modules/MaterialManager/infrastructure/api/material-axios', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
  },
}))

import materialAxios from '@/modules/MaterialManager/infrastructure/api/material-axios'

describe('materialApi', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('getPaginated', () => {
    it('calls GET with params and returns data', async () => {
      const mockData = { data: [{ id: 1, title: 'Test' }], meta: { last_page: 1 } }
      vi.mocked(materialAxios.get).mockResolvedValueOnce({ data: mockData })

      const result = await materialApi.getPaginated({ search: 'test' }, 1)

      expect(materialAxios.get).toHaveBeenCalledWith('/', {
        params: { search: 'test', page: 1 },
        signal: expect.any(AbortSignal),
      })
      expect(result).toEqual(mockData)
    })

    it('aborts previous request when called again', async () => {
      const abortSpy = vi.spyOn(AbortController.prototype, 'abort')
      vi.mocked(materialAxios.get).mockResolvedValue({ data: {} })

      await materialApi.getPaginated({}, 1)
      await materialApi.getPaginated({}, 2)

      expect(abortSpy).toHaveBeenCalled()
    })
  })

  describe('toggleHomepage', () => {
    it('sends PUT with show_on_homepage flag', async () => {
      vi.mocked(materialAxios.put).mockResolvedValueOnce({ data: { message: 'OK' } })

      await materialApi.toggleHomepage(5, true)

      expect(materialAxios.put).toHaveBeenCalledWith('/5', {
        show_on_homepage: true,
      })
    })
  })

  describe('restore', () => {
    it('sends POST to restore endpoint with ids', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'Restored', count: 2 } })

      const result = await materialApi.restore([1, 2])

      expect(materialAxios.post).toHaveBeenCalledWith('/restore', { ids: [1, 2] })
      expect(result).toEqual({ message: 'Restored', count: 2 })
    })
  })

  describe('moveToTrash', () => {
    it('sends POST to bulk-trash endpoint', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'OK' } })

      await materialApi.moveToTrash([3, 4])

      expect(materialAxios.post).toHaveBeenCalledWith('/bulk-trash', { ids: [3, 4] })
    })
  })

  describe('forceDelete', () => {
    it('sends POST to force-delete endpoint', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'Deleted' } })

      await materialApi.forceDelete([5])

      expect(materialAxios.post).toHaveBeenCalledWith('/force-delete', { ids: [5] })
    })
  })

  describe('emptyTrash', () => {
    it('sends POST to empty-trash endpoint', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'Emptied' } })

      await materialApi.emptyTrash()

      expect(materialAxios.post).toHaveBeenCalledWith('/empty-trash', {})
    })
  })

  describe('publish', () => {
    it('sends POST to bulk-publish endpoint', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'Published' } })

      await materialApi.publish([1, 2])

      expect(materialAxios.post).toHaveBeenCalledWith('/bulk-publish', { ids: [1, 2] })
    })
  })

  describe('unpublish', () => {
    it('sends POST to bulk-unpublish endpoint', async () => {
      vi.mocked(materialAxios.post).mockResolvedValueOnce({ data: { message: 'Unpublished' } })

      await materialApi.unpublish([3])

      expect(materialAxios.post).toHaveBeenCalledWith('/bulk-unpublish', { ids: [3] })
    })
  })
})
