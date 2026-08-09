import { ref, watch, computed } from 'vue';
import type { MaterialsData } from '../types';

export function useMaterialSelection(props: { materials: MaterialsData }) {
    const selectedMaterials = ref<number[]>([]);
    const allSelected = computed(() => {
        const currentData = props.materials.data || [];
        return selectedMaterials.value.length === currentData.length && currentData.length > 0;
    });

    const toggleSelect = (id: number) => {
        const index = selectedMaterials.value.indexOf(id);
        if (index === -1) {
            selectedMaterials.value.push(id);
        } else {
            selectedMaterials.value.splice(index, 1);
        }
    };

    const selectAll = () => {
        const currentData = props.materials.data || [];
        if (selectedMaterials.value.length === currentData.length) {
            selectedMaterials.value = [];
        } else {
            selectedMaterials.value = currentData.map(m => m.id);
        }
    };

    return {
        selectedMaterials,
        allSelected,
        toggleSelect,
        selectAll,
    };
}
