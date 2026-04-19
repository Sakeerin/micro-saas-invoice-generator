<script setup>
import { ref, watch, computed } from 'vue';
import draggable from 'vuedraggable';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { PlusIcon, TrashIcon, ArrowsUpDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    },
    currency: {
        type: String,
        default: 'THB'
    }
});

const emit = defineEmits(['update:modelValue', 'open-product-picker']);

const items = ref([...props.modelValue]);

// Add default item if empty
if (items.value.length === 0) {
    items.value.push(createNewItem());
}

function createNewItem() {
    return {
        id: crypto.randomUUID(),
        product_id: null,
        name: '',
        name_en: '',
        description: '',
        quantity: 1,
        unit: 'งาน',
        unit_price: 0,
        discount_percent: 0,
        line_total: 0
    };
}

const addItem = () => {
    items.value.push(createNewItem());
};

const removeItem = (index) => {
    items.value.splice(index, 1);
    if (items.value.length === 0) {
        addItem();
    }
};

const calculateLineTotal = (item) => {
    const subtotal = item.quantity * item.unit_price;
    const discount = subtotal * (item.discount_percent / 100);
    return Number((subtotal - discount).toFixed(2));
};

watch(items, (newItems) => {
    newItems.forEach(item => {
        item.line_total = calculateLineTotal(item);
    });
    emit('update:modelValue', newItems);
}, { deep: true });

// Listen for external updates (e.g. from parent or modal)
watch(() => props.modelValue, (newVal) => {
    if (JSON.stringify(newVal) !== JSON.stringify(items.value)) {
        items.value = [...newVal];
    }
}, { deep: true });

const formatNumber = (val) => {
    return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(val);
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase font-semibold">
                    <th class="p-3 w-10"></th>
                    <th class="p-3 min-w-[250px]">รายการ / Description</th>
                    <th class="p-3 w-24">จำนวน</th>
                    <th class="p-3 w-24">หน่วย</th>
                    <th class="p-3 w-32">ราคา/หน่วย</th>
                    <th class="p-3 w-24">ส่วนลด %</th>
                    <th class="p-3 w-32 text-right">รวม</th>
                    <th class="p-3 w-10"></th>
                </tr>
            </thead>
            <draggable 
                v-model="items" 
                tag="tbody" 
                handle=".drag-handle" 
                item-key="id"
                class="divide-y divide-gray-200"
            >
                <template #item="{ element, index }">
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td class="p-3 text-center">
                            <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                                <ArrowsUpDownIcon class="w-5 h-5" />
                            </div>
                        </td>
                        <td class="p-3 space-y-2">
                            <div class="flex gap-2">
                                <TextInput 
                                    v-model="element.name" 
                                    placeholder="ชื่อสินค้า/บริการ (ไทย) *" 
                                    class="w-full text-sm"
                                    required
                                />
                                <button 
                                    type="button"
                                    @click="emit('open-product-picker', index)"
                                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                                    title="เลือกจากสินค้าที่มีอยู่"
                                >
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <TextInput 
                                v-model="element.name_en" 
                                placeholder="Product Name (English)" 
                                class="w-full text-sm"
                            />
                            <textarea 
                                v-model="element.description" 
                                placeholder="รายละเอียดเพิ่มเติม..." 
                                class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="1"
                            ></textarea>
                        </td>
                        <td class="p-3">
                            <TextInput 
                                type="number" 
                                v-model.number="element.quantity" 
                                step="any"
                                class="w-full text-sm text-right"
                            />
                        </td>
                        <td class="p-3">
                            <TextInput 
                                v-model="element.unit" 
                                class="w-full text-sm"
                            />
                        </td>
                        <td class="p-3">
                            <TextInput 
                                type="number" 
                                v-model.number="element.unit_price" 
                                step="any"
                                class="w-full text-sm text-right"
                            />
                        </td>
                        <td class="p-3">
                            <TextInput 
                                type="number" 
                                v-model.number="element.discount_percent" 
                                step="any"
                                class="w-full text-sm text-right"
                            />
                        </td>
                        <td class="p-3 text-right font-medium text-gray-900">
                            {{ formatNumber(element.line_total) }}
                        </td>
                        <td class="p-3 text-center">
                            <button 
                                type="button"
                                @click="removeItem(index)" 
                                class="text-gray-400 hover:text-red-600 transition-colors"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                </template>
            </draggable>
        </table>

        <div class="mt-4 flex justify-between items-center p-3">
            <SecondaryButton type="button" @click="addItem" class="flex items-center gap-2">
                <PlusIcon class="w-4 h-4" />
                เพิ่มรายการ (Add Item)
            </SecondaryButton>
        </div>
    </div>
</template>
