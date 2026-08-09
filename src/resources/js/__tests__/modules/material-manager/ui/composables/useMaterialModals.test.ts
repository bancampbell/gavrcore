import { describe, it, expect } from 'vitest'

import { useMaterialModals } from '@/modules/MaterialManager/ui/composables/useMaterialModals'

describe('useMaterialModals', () => {
  it('openRestoreModal sets correct state for single item', () => {
    const { openRestoreModal, modalOpen, modalTitle, modalMessage, modalConfirmText, pendingAction } = useMaterialModals()

    openRestoreModal(1)

    expect(modalOpen.value).toBe(true)
    expect(modalTitle.value).toBe('Восстановление материалов')
    expect(modalMessage.value).toBe('Вы уверены, что хотите восстановить выбранный материал?')
    expect(modalConfirmText.value).toBe('Восстановить')
    expect(pendingAction.value).toBe('restore')
  })

  it('openRestoreModal sets correct state for multiple items', () => {
    const { openRestoreModal, modalOpen, modalMessage, modalConfirmText, pendingAction } = useMaterialModals()

    openRestoreModal(5)

    expect(modalOpen.value).toBe(true)
    expect(modalMessage.value).toBe('Вы уверены, что хотите восстановить 5 материалов?')
    expect(modalConfirmText.value).toBe('Восстановить')
    expect(pendingAction.value).toBe('restore')
  })

  it('openDeleteModal sets correct state for single item', () => {
    const { openDeleteModal, modalOpen, modalTitle, modalMessage, modalConfirmText, pendingAction } = useMaterialModals()

    openDeleteModal(1)

    expect(modalOpen.value).toBe(true)
    expect(modalTitle.value).toBe('Удаление материалов')
    expect(modalMessage.value).toBe('Вы уверены, что хотите удалить выбранный материал навсегда? Это действие нельзя отменить.')
    expect(modalConfirmText.value).toBe('Удалить навсегда')
    expect(pendingAction.value).toBe('delete')
  })

  it('openDeleteModal sets correct state for multiple items', () => {
    const { openDeleteModal, modalMessage } = useMaterialModals()

    openDeleteModal(3)

    expect(modalMessage.value).toBe('Вы уверены, что хотите удалить 3 материалов навсегда? Это действие нельзя отменить.')
  })

  it('openEmptyTrashModal sets correct state', () => {
    const { openEmptyTrashModal, modalOpen, modalTitle, modalMessage, modalConfirmText, pendingAction } = useMaterialModals()

    openEmptyTrashModal()

    expect(modalOpen.value).toBe(true)
    expect(modalTitle.value).toBe('Очистка корзины')
    expect(modalMessage.value).toBe('Вы уверены, что хотите очистить корзину? Все материалы будут удалены навсегда.')
    expect(modalConfirmText.value).toBe('Очистить корзину')
    expect(pendingAction.value).toBe('empty')
  })

  it('closeModal clears state', () => {
    const { openRestoreModal, closeModal, modalOpen, pendingAction } = useMaterialModals()

    openRestoreModal(1)
    closeModal()

    expect(modalOpen.value).toBe(false)
    expect(pendingAction.value).toBeNull()
  })
})
