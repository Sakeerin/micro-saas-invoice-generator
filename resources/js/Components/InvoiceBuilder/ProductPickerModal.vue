<script setup>
import { ref, computed } from 'vue';
import { VueFinalModal } from 'vue-final-modal';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['select', 'close']);

const searchQuery = ref('');

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;
    const query = searchQuery.value.toLowerCase();
    return props.products.filter(p => 
        p.name.toLowerCase().includes(query) || 
        (p.name_en && p.name_en.toLowerCase().includes(query)) ||
        (p.description && p.description.toLowerCase().includes(query))
    );
});

const selectProduct = (product) => {
    emit('select', product);
};
</script>

<template>
    <VueFinalModal
        class="flex justify-center items-center"
        content-class="relative flex flex-col max-h-full mx-4 p-6 bg-white rounded-xl border shadow-2xl w-full max-w-2xl"
    >
        <div class="flex items-center justify-between mb-4 border-b pb-4">
            <h3 class="text-xl font-bold text-gray-900">เลือกสินค้า / Select Product</h3>
            <button @click="emit('close')" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-6 h-6" />
            </button>
        </div>

        <div class="mb-4 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" />
            </div>
            <input
                type="text"
                v-model="searchQuery"
                placeholder="ค้นหาชื่อสินค้า, รายละเอียด..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
        </div>

        <div class="overflow-y-auto flex-1 min-h-[300px] max-h-[400px]">
            <div v-if="filteredProducts.length === 0" class="text-center py-12 text-gray-500">
                ไม่พบสินค้าที่คุณค้นหา
            </div>
            <table v-else class="w-full text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2">ชื่อสินค้า</th>
                        <th class="px-4 py-2 text-right">ราคา</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr 
                        v-for="product in filteredProducts" 
                        :key="product.id"
                        @click="selectProduct(product)"
                        class="hover:bg-indigo-50 cursor-pointer transition-colors"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ product.name }}</div>
                            <div v-if="product.name_en" class="text-xs text-gray-500">{{ product.name_en }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900">
                            {{ new Intl.NumberFormat('th-TH').format(product.unit_price) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <SecondaryButton size="sm">เลือก</SecondaryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <SecondaryButton @click="emit('close')">ยกเลิก</SecondaryButton>
        </div>
    </VueFinalModal>
</template>
