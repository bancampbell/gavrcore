import { describe, it, expect } from 'vitest'

import { useMaterialSelection } from '@/modules/MaterialManager/ui/composables/useMaterialSelection'

function createProps(data: { id: number }[] = []) {
  return { materials: { data } }
}

describe('useMaterialSelection', () => {
  it('toggleSelect adds id when not selected', () => {
    const { selectedMaterials, toggleSelect } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }]))

    toggleSelect(2)

    expect(selectedMaterials.value).toContain(2)
    expect(selectedMaterials.value).toHaveLength(1)
  })

  it('toggleSelect removes id when already selected', () => {
    const { selectedMaterials, toggleSelect } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }]))

    toggleSelect(2)
    toggleSelect(2)

    expect(selectedMaterials.value).not.toContain(2)
    expect(selectedMaterials.value).toEqual([])
  })

  it('selectAll selects all ids when none selected', () => {
    const { selectedMaterials, selectAll } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }, { id: 3 }]))

    selectAll()

    expect(selectedMaterials.value).toEqual([1, 2, 3])
  })

  it('selectAll clears selection when all selected', () => {
    const { selectedMaterials, selectAll } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }]))

    selectAll()
    selectAll()

    expect(selectedMaterials.value).toEqual([])
  })

  it('allSelected is true when all materials selected', () => {
    const { selectedMaterials, allSelected, selectAll } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }]))

    expect(allSelected.value).toBe(false)

    selectAll()
    expect(allSelected.value).toBe(true)
  })

  it('allSelected is false when partially selected', () => {
    const { selectedMaterials, allSelected } = useMaterialSelection(createProps([{ id: 1 }, { id: 2 }, { id: 3 }]))

    selectedMaterials.value = [1, 2]
    expect(allSelected.value).toBe(false)
  })

  it('allSelected is false when no materials exist', () => {
    const { allSelected } = useMaterialSelection(createProps([]))

    expect(allSelected.value).toBe(false)
  })
})
