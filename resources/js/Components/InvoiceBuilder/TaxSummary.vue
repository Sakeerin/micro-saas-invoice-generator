<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    discountType: {
        type: String,
        default: 'none'
    },
    discountValue: {
        type: Number,
        default: 0
    },
    vatRate: {
        type: Number,
        default: 7
    },
    whtRate: {
        type: Number,
        default: 0
    },
    currency: {
        type: String,
        default: 'THB'
    }
});

const emit = defineEmits(['update:vatRate', 'update:whtRate']);

const subtotal = computed(() => {
    return props.items.reduce((sum, item) => {
        const lineTotal = (item.quantity || 0) * (item.unit_price || 0);
        const discount = lineTotal * ((item.discount_percent || 0) / 100);
        return sum + (lineTotal - discount);
    }, 0);
});

const discountAmount = computed(() => {
    if (props.discountType === 'percent') {
        return subtotal.value * (props.discountValue / 100);
    } else if (props.discountType === 'amount') {
        return Math.min(props.discountValue, subtotal.value);
    }
    return 0;
});

const subtotalAfterDiscount = computed(() => {
    return subtotal.value - discountAmount.value;
});

const vatAmount = computed(() => {
    return subtotalAfterDiscount.value * (props.vatRate / 100);
});

const whtAmount = computed(() => {
    return subtotalAfterDiscount.value * (props.whtRate / 100);
});

const total = computed(() => {
    return subtotalAfterDiscount.value + vatAmount.value - whtAmount.value;
});

const formatNumber = (val) => {
    return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
};

const whtRates = [
    { rate: 0, label: '0% - ไม่มี' },
    { rate: 1, label: '1% - ค่าเช่า/ขนส่ง' },
    { rate: 3, label: '3% - ค่าบริการ/จ้างทำของ' },
    { rate: 5, label: '5% - ค่าโฆษณา' },
];
</script>

<template>
    <div class="space-y-4">
        <!-- Tax Controls -->
        <div class="grid grid-cols-2 gap-4 border-b pb-4 mb-4">
            <div>
                <InputLabel for="vat_rate_select" value="VAT (%)" />
                <select
                    id="vat_rate_select"
                    :value="vatRate"
                    @change="emit('update:vatRate', Number($event.target.value))"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                >
                    <option :value="0">ไม่มี (0%)</option>
                    <option :value="7">7%</option>
                </select>
            </div>
            <div>
                <InputLabel for="wht_rate_select" value="WHT (%)" />
                <select
                    id="wht_rate_select"
                    :value="whtRate"
                    @change="emit('update:whtRate', Number($event.target.value))"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                >
                    <option v-for="rate in whtRates" :key="rate.rate" :value="rate.rate">
                        {{ rate.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Summary Display -->
        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
                <span>ยอดรวม (Subtotal)</span>
                <span>{{ formatNumber(subtotal) }} {{ currency }}</span>
            </div>
            
            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                <span>ส่วนลด (Discount)</span>
                <span>- {{ formatNumber(discountAmount) }} {{ currency }}</span>
            </div>

            <div v-if="vatRate > 0" class="flex justify-between text-gray-600">
                <span>ภาษีมูลค่าเพิ่ม (VAT {{ vatRate }}%)</span>
                <span>{{ formatNumber(vatAmount) }} {{ currency }}</span>
            </div>

            <div v-if="whtRate > 0" class="flex justify-between text-red-600">
                <span>หัก ณ ที่จ่าย (WHT {{ whtRate }}%)</span>
                <span>- {{ formatNumber(whtAmount) }} {{ currency }}</span>
            </div>

            <div class="flex justify-between pt-4 border-t border-gray-200">
                <span class="text-base font-bold text-gray-900">ยอดสุทธิ (Total Due)</span>
                <span class="text-xl font-bold text-indigo-600">{{ formatNumber(total) }} {{ currency }}</span>
            </div>
            
            <p class="text-[10px] text-gray-400 mt-2 italic">
                * ยอดสุทธิ = (รวมหลังหักส่วนลด + VAT) - WHT
            </p>
        </div>
    </div>
</template>
