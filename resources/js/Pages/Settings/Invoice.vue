<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ company: Object });

const page = usePage();
const flash = computed(() => page.props.flash);

const form = useForm({
    invoice_prefix:      props.company.invoice_prefix ?? 'INV',
    invoice_next_number: props.company.invoice_next_number ?? 1,
    default_vat_rate:    props.company.default_vat_rate ?? 7,
    default_currency:    props.company.default_currency ?? 'THB',
});

const previewNumber = computed(() => {
    const num = String(form.invoice_next_number).padStart(4, '0');
    return `${form.invoice_prefix}-${new Date().getFullYear()}-${num}`;
});

function submit() {
    form.patch(route('settings.invoice.update'));
}
</script>

<template>
    <Head title="Invoice Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">ตั้งค่า Invoice</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">

                <!-- Flash -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                    {{ flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Invoice Number -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">รูปแบบเลขที่ Invoice</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                                <input
                                    v-model="form.invoice_prefix"
                                    type="text"
                                    maxlength="10"
                                    placeholder="INV"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                                <p v-if="form.errors.invoice_prefix" class="mt-1 text-xs text-red-500">{{ form.errors.invoice_prefix }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เลขถัดไป</label>
                                <input
                                    v-model.number="form.invoice_next_number"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                                <p v-if="form.errors.invoice_next_number" class="mt-1 text-xs text-red-500">{{ form.errors.invoice_next_number }}</p>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 mb-1">ตัวอย่างเลขที่ invoice ถัดไป</p>
                            <p class="text-lg font-bold font-mono text-indigo-600">{{ previewNumber }}</p>
                        </div>
                    </div>

                    <!-- Tax & Currency -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">ภาษี และสกุลเงิน</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">อัตรา VAT เริ่มต้น</label>
                                <select
                                    v-model.number="form.default_vat_rate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option :value="0">0% (ไม่มี VAT)</option>
                                    <option :value="7">7% (VAT ปกติ)</option>
                                </select>
                                <p v-if="form.errors.default_vat_rate" class="mt-1 text-xs text-red-500">{{ form.errors.default_vat_rate }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">สกุลเงินเริ่มต้น</label>
                                <select
                                    v-model="form.default_currency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="THB">THB — บาทไทย</option>
                                    <option value="USD">USD — ดอลลาร์สหรัฐ</option>
                                    <option value="EUR">EUR — ยูโร</option>
                                    <option value="SGD">SGD — ดอลลาร์สิงคโปร์</option>
                                </select>
                                <p v-if="form.errors.default_currency" class="mt-1 text-xs text-red-500">{{ form.errors.default_currency }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition-colors disabled:opacity-60"
                        >
                            {{ form.processing ? 'กำลังบันทึก…' : 'บันทึกการเปลี่ยนแปลง' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
