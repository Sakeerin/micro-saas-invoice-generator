<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { SparklesIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    clientId: { type: String, default: null },
    currentItems: { type: Array, default: () => [] },
    currency: { type: String, default: 'THB' },
});

const emit = defineEmits(['add-item']);

const isLoading = ref(false);
const suggestions = ref([]);
const error = ref(null);
const isVisible = ref(false);

const getSuggestions = async () => {
    isLoading.value = true;
    error.value = null;
    suggestions.value = [];
    isVisible.value = true;

    try {
        const response = await axios.post(route('api.ai.suggest'), {
            client_id: props.clientId || null,
            current_items: props.currentItems.filter(i => i.name),
        });
        suggestions.value = response.data.suggestions ?? [];
    } catch (e) {
        error.value = 'ไม่สามารถดึงคำแนะนำได้ กรุณาลองใหม่อีกครั้ง';
        console.error('AI suggest error', e);
    } finally {
        isLoading.value = false;
    }
};

const addItem = (suggestion) => {
    emit('add-item', {
        id: crypto.randomUUID(),
        product_id: null,
        name: suggestion.name,
        name_en: suggestion.name_en ?? '',
        description: suggestion.description ?? '',
        quantity: suggestion.quantity,
        unit: suggestion.unit,
        unit_price: suggestion.unit_price,
        discount_percent: 0,
        line_total: suggestion.line_total,
    });
    suggestions.value = suggestions.value.filter(s => s.id !== suggestion.id);
};

const dismiss = () => {
    isVisible.value = false;
    suggestions.value = [];
    error.value = null;
};

const fmt = (val) => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
</script>

<template>
    <div class="mt-3">
        <button
            type="button"
            @click="getSuggestions"
            :disabled="isLoading"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-purple-600 hover:text-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
            <SparklesIcon class="w-4 h-4" :class="{ 'animate-pulse': isLoading }" />
            <span v-if="!isLoading">AI แนะนำรายการ</span>
            <span v-else>กำลังวิเคราะห์...</span>
        </button>

        <div
            v-if="isVisible"
            class="mt-3 rounded-lg border border-purple-200 bg-purple-50"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-purple-100">
                <div class="flex items-center gap-2">
                    <SparklesIcon class="w-4 h-4 text-purple-600" />
                    <span class="text-sm font-semibold text-purple-800">AI Suggestions</span>
                </div>
                <button type="button" @click="dismiss" class="text-purple-400 hover:text-purple-700 transition-colors">
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="flex items-center justify-center gap-2 py-6 text-sm text-purple-600">
                <svg class="animate-spin h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                กำลังวิเคราะห์ประวัติและแนะนำรายการ...
            </div>

            <!-- Error -->
            <div v-else-if="error" class="px-4 py-4 text-sm text-red-600 text-center">
                {{ error }}
            </div>

            <!-- Empty -->
            <div v-else-if="suggestions.length === 0" class="px-4 py-4 text-sm text-purple-500 text-center">
                ไม่พบข้อมูลเพียงพอสำหรับการแนะนำ
            </div>

            <!-- Suggestions list -->
            <ul v-else class="divide-y divide-purple-100">
                <li
                    v-for="suggestion in suggestions"
                    :key="suggestion.id"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-purple-100/50 transition-colors"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ suggestion.name }}</p>
                        <p v-if="suggestion.name_en" class="text-xs text-gray-500 truncate">{{ suggestion.name_en }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ suggestion.quantity }} {{ suggestion.unit }}
                            × {{ fmt(suggestion.unit_price) }} {{ currency }}
                            <span class="ml-1 font-semibold text-gray-700">= {{ fmt(suggestion.line_total) }}</span>
                        </p>
                        <p v-if="suggestion.description" class="mt-0.5 text-xs text-gray-400 truncate">{{ suggestion.description }}</p>
                    </div>
                    <button
                        type="button"
                        @click="addItem(suggestion)"
                        class="shrink-0 inline-flex items-center gap-1 rounded-md bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700 transition-colors"
                    >
                        <PlusIcon class="w-3 h-3" />
                        เพิ่ม
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
