<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch } from 'vue';
import LineItemsEditor from '@/Components/InvoiceBuilder/LineItemsEditor.vue';
import ProductPickerModal from '@/Components/InvoiceBuilder/ProductPickerModal.vue';
import TaxSummary from '@/Components/InvoiceBuilder/TaxSummary.vue';
import { useModal } from 'vue-final-modal';

const props = defineProps({
    clients: Array,
    products: Array,
    defaultInvoiceNumber: String,
    company: Object,
});

const form = useForm({
    client_id: '',
    invoice_number: props.defaultInvoiceNumber,
    reference: '',
    issue_date: new Date().toISOString().substr(0, 10),
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().substr(0, 10), // Default 30 days
    currency: props.company.default_currency || 'THB',
    client_name: '',
    client_name_en: '',
    client_address: '',
    client_address_en: '',
    client_tax_id: '',
    items: [],
    discount_type: 'none',
    discount_value: 0,
    notes: '',
    payment_terms: props.company.bank_account ? `ธนาคาร: ${props.company.bank_name}\nเลขบัญชี: ${props.company.bank_account}\nชื่อบัญชี: ${props.company.bank_account_name}` : '',
    language: 'th-en',
    template: 'modern',
    vat_rate: 7,
    wht_rate: 0,
});

const templates = [
    { id: 'modern', name: 'Modern', description: 'สะอาดตา สไตล์ร่วมสมัย' },
    { id: 'classic', name: 'Classic', description: 'เป็นทางการ เรียบหรู' },
    { id: 'minimal', name: 'Minimal', description: 'เรียบง่าย ตัดทอนส่วนเกิน' },
    { id: 'corporate', name: 'Corporate', description: 'น่าเชื่อถือ เหมาะสำหรับองค์กร' },
    { id: 'creative', name: 'Creative', description: 'โดดเด่น มีสีสัน' },
];

const selectedClientId = ref('');
const currentItemIndex = ref(null);

const { open: openProductPicker, close: closeProductPicker } = useModal({
    component: ProductPickerModal,
    attrs: {
        products: props.products,
        onSelect(product) {
            if (currentItemIndex.value !== null) {
                const item = form.items[currentItemIndex.value];
                item.product_id = product.id;
                item.name = product.name;
                item.name_en = product.name_en;
                item.description = product.description;
                item.unit = product.unit;
                item.unit_price = product.unit_price;
            }
            closeProductPicker();
        },
        onClose() {
            closeProductPicker();
        },
    },
});

const handleOpenProductPicker = (index) => {
    currentItemIndex.value = index;
    openProductPicker();
};

watch(selectedClientId, (newId) => {
    const client = props.clients.find(c => c.id === newId);
    if (client) {
        form.client_id = client.id;
        form.client_name = client.name;
        form.client_name_en = client.name_en;
        form.client_address = client.address;
        form.client_address_en = client.address_en;
        form.client_tax_id = client.tax_id;
    } else {
        form.client_id = '';
        form.client_name = '';
        form.client_name_en = '';
        form.client_address = '';
        form.client_address_en = '';
        form.client_tax_id = '';
    }
});

const submit = () => {
    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="Create Invoice" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Invoice
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Left Column: Core Info -->
                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                                
                                <div class="mb-4">
                                    <InputLabel for="client_select" value="Select Client" />
                                    <select
                                        id="client_select"
                                        v-model="selectedClientId"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">-- Manual Entry / New Client --</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }} {{ client.tax_id ? `(${client.tax_id})` : '' }}
                                        </option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                    <div>
                                        <InputLabel for="client_name" value="Client Name (Thai) *" />
                                        <TextInput
                                            id="client_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.client_name"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.client_name" />
                                    </div>
                                    <div>
                                        <InputLabel for="client_name_en" value="Client Name (English)" />
                                        <TextInput
                                            id="client_name_en"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.client_name_en"
                                        />
                                        <InputError class="mt-2" :message="form.errors.client_name_en" />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="client_tax_id" value="Tax ID" />
                                    <TextInput
                                        id="client_tax_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.client_tax_id"
                                    />
                                    <InputError class="mt-2" :message="form.errors.client_tax_id" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <InputLabel for="client_address" value="Address (Thai)" />
                                        <textarea
                                            id="client_address"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.client_address"
                                            rows="3"
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.client_address" />
                                    </div>
                                    <div>
                                        <InputLabel for="client_address_en" value="Address (English)" />
                                        <textarea
                                            id="client_address_en"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.client_address_en"
                                            rows="3"
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.client_address_en" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Line Items</h3>
                                <LineItemsEditor 
                                    v-model="form.items" 
                                    :products="products"
                                    :currency="form.currency"
                                    @open-product-picker="handleOpenProductPicker"
                                />
                            </div>

                            <!-- Discount & Notes -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Discount</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="discount_type" value="Discount Type" />
                                            <select
                                                id="discount_type"
                                                v-model="form.discount_type"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            >
                                                <option value="none">No Discount</option>
                                                <option value="percent">Percentage (%)</option>
                                                <option value="amount">Fixed Amount</option>
                                            </select>
                                        </div>
                                        <div v-if="form.discount_type !== 'none'">
                                            <InputLabel 
                                                for="discount_value" 
                                                :value="form.discount_type === 'percent' ? 'Discount Percentage (%)' : `Discount Amount (${form.currency})`" 
                                            />
                                            <TextInput
                                                id="discount_value"
                                                type="number"
                                                step="any"
                                                class="mt-1 block w-full"
                                                v-model.number="form.discount_value"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Language & Currency</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="language" value="Invoice Language" />
                                            <select
                                                id="language"
                                                v-model="form.language"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            >
                                                <option value="th">ภาษาไทย (Thai)</option>
                                                <option value="en">English</option>
                                                <option value="th-en">Bilingual (Thai + English)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel for="currency" value="Currency" />
                                            <select
                                                id="currency"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                v-model="form.currency"
                                            >
                                                <option value="THB">THB (฿)</option>
                                                <option value="USD">USD ($)</option>
                                                <option value="EUR">EUR (€)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes & Payment Terms</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="notes" value="Notes (Visible on Invoice)" />
                                        <textarea
                                            id="notes"
                                            rows="4"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.notes"
                                            placeholder="e.g. ขอบคุณที่ใช้บริการ, โปรดชำระภายในวันที่กำหนด"
                                        ></textarea>
                                    </div>
                                    <div>
                                        <InputLabel for="payment_terms" value="Payment Terms / Bank Info" />
                                        <textarea
                                            id="payment_terms"
                                            rows="4"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.payment_terms"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings & Summary -->
                        <div class="space-y-6">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="invoice_number" value="Invoice Number *" />
                                        <TextInput
                                            id="invoice_number"
                                            type="text"
                                            class="mt-1 block w-full bg-gray-50"
                                            v-model="form.invoice_number"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.invoice_number" />
                                    </div>

                                    <div>
                                        <InputLabel for="reference" value="Reference (PO Number)" />
                                        <TextInput
                                            id="reference"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.reference"
                                        />
                                        <InputError class="mt-2" :message="form.errors.reference" />
                                    </div>

                                    <div>
                                        <InputLabel for="issue_date" value="Issue Date *" />
                                        <TextInput
                                            id="issue_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.issue_date"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.issue_date" />
                                    </div>

                                    <div>
                                        <InputLabel for="due_date" value="Due Date" />
                                        <TextInput
                                            id="due_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.due_date"
                                        />
                                        <InputError class="mt-2" :message="form.errors.due_date" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Template Selection</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        v-for="tpl in templates"
                                        :key="tpl.id"
                                        type="button"
                                        @click="form.template = tpl.id"
                                        class="relative flex flex-col items-center p-2 border-2 rounded-lg transition-all"
                                        :class="form.template === tpl.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                    >
                                        <div class="w-full aspect-[3/4] bg-gray-100 rounded mb-2 flex items-center justify-center overflow-hidden">
                                            <!-- Placeholder for template preview thumbnail -->
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ tpl.name }}</span>
                                        </div>
                                        <span class="text-xs font-medium text-gray-900">{{ tpl.name }}</span>
                                        
                                        <div v-if="form.template === tpl.id" class="absolute top-1 right-1">
                                            <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                                <TaxSummary 
                                    :items="form.items"
                                    :discount-type="form.discount_type"
                                    :discount-value="form.discount_value"
                                    v-model:vat-rate="form.vat_rate"
                                    v-model:wht-rate="form.wht_rate"
                                    :currency="form.currency"
                                />
                                
                                <div class="flex flex-col space-y-4 mt-8">
                                    <PrimaryButton class="w-full justify-center py-3" :disabled="form.processing">
                                        Save Invoice Draft
                                    </PrimaryButton>
                                    
                                    <Link
                                        :href="route('invoices.index')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
