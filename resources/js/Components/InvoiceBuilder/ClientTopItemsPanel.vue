<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { ClockIcon, PlusIcon, PlusCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    clientId: { type: String, default: null },
    currency: { type: String, default: 'THB' },
});

const emit = defineEmits(['add-item', 'add-all-items']);

const items = ref([]);
const isLoading = ref(false);
const addedIds = ref(new Set());

const fetchTopItems = async (clientId) => {
    if (!clientId) {
        items.value = [];
        addedIds.value = new Set();
        return;
    }

    isLoading.value = true;
    try {
        const res = await axios.get(route('api.clients.top-items', clientId));
        items.value = res.data.items ?? [];
        addedIds.value = new Set();
    } catch {
        items.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.clientId, fetchTopItems, { immediate: true });

const addItem = (item) => {
    emit('add-item', {
        id: crypto.randomUUID(),
        product_id: null,
        name: item.name,
        name_en: item.name_en ?? '',
        description: item.description ?? '',
        quantity: 1,
        unit: item.unit,
        unit_price: item.avg_price,
        discount_percent: 0,
        line_total: item.avg_price,
    });
    addedIds.value = new Set([...addedIds.value, item.id]);
};

const addAllItems = () => {
    const toAdd = items.value.filter(i => !addedIds.value.has(i.id));
    toAdd.forEach(addItem);
};

const fmt = (val) => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
</script>

<template>
    <div v-if="clientId" class="mt-4">
        <!-- Loading skeleton -->
        <div v-if="isLoading" class="flex items-center gap-2 text-sm text-gray-400 py-1">
            <svg class="animate-spin h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            กำลังโหลดประวัติรายการ...
        </div>

        <!-- Items panel -->
        <div v-else-if="items.length > 0" class="rounded-lg border border-indigo-100 bg-indigo-50">
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-indigo-100">
                <div class="flex items-center gap-2">
                    <ClockIcon class="w-4 h-4 text-indigo-500" />
                    <span class="text-sm font-semibold text-indigo-800">รายการที่เคยใช้กับลูกค้านี้</span>
                    <span class="text-xs text-indigo-500 font-normal">({{ items.length }} รายการ)</span>
                </div>
                <button
                    v-if="items.some(i => !addedIds.has(i.id))"
                    type="button"
                    @click="addAllItems"
                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
                >
                    <PlusCircleIcon class="w-3.5 h-3.5" />
                    เพิ่มทั้งหมด
                </button>
            </div>

            <ul class="divide-y divide-indigo-100">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center gap-3 px-4 py-2.5 hover:bg-indigo-100/50 transition-colors"
                    :class="{ 'opacity-50': addedIds.has(item.id) }"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                        <p v-if="item.name_en" class="text-xs text-gray-400 truncate">{{ item.name_en }}</p>
                        <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500">
                            <span>{{ item.unit }} · ราคาเฉลี่ย <strong class="text-gray-700">{{ fmt(item.avg_price) }}</strong> {{ currency }}</span>
                            <span v-if="item.min_price !== item.max_price" class="text-gray-400">
                                ({{ fmt(item.min_price) }}–{{ fmt(item.max_price) }})
                            </span>
                            <span class="text-indigo-400">· ใช้แล้ว {{ item.usage_count }}×</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="addItem(item)"
                        :disabled="addedIds.has(item.id)"
                        class="shrink-0 inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                        :class="addedIds.has(item.id)
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                    >
                        <PlusIcon class="w-3 h-3" />
                        {{ addedIds.has(item.id) ? 'เพิ่มแล้ว' : 'เพิ่ม' }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
