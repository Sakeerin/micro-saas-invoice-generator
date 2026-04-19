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
});

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
                                        <InputError class="mt-2" :message="form.errors.currency" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <div class="flex flex-col space-y-4">
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
