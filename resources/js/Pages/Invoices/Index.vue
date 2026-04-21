<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    invoices: Object,
    filters: Object,
    clients: Array,
});

const filterForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
    client_id: props.filters.client_id || '',
});

const deleteInvoice = (id) => {
    if (confirm('Are you sure you want to delete this invoice?')) {
        router.delete(route('invoices.destroy', id));
    }
};

const duplicateInvoice = (id) => {
    if (confirm('Do you want to duplicate this invoice? It will be created as a new draft.')) {
        router.post(route('invoices.duplicate', id));
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'draft': return 'bg-gray-100 text-gray-800 border-gray-200';
        case 'sent': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'viewed': return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'paid': return 'bg-green-100 text-green-800 border-green-200';
        case 'overdue': return 'bg-red-100 text-red-800 border-red-200';
        case 'cancelled': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount, currency) => {
    return new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount) + ' ' + currency;
};

// Handle filtering
watch(
    () => filterForm.data(),
    debounce(() => {
        filterForm.get(route('invoices.index'), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 500),
    { deep: true }
);

const resetFilters = () => {
    filterForm.search = '';
    filterForm.status = '';
    filterForm.client_id = '';
};
</script>

<template>
    <Head title="Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Invoices
                </h2>
                <Link
                    :href="route('invoices.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                >
                    Create Invoice
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 bg-white p-4 shadow-sm sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 uppercase mb-1">Search</label>
                            <input
                                type="text"
                                v-model="filterForm.search"
                                placeholder="Search number, client name..."
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase mb-1">Status</label>
                            <select
                                v-model="filterForm.status"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="viewed">Viewed</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase mb-1">Client</label>
                            <select
                                v-model="filterForm.client_id"
                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div v-if="filterForm.search || filterForm.status || filterForm.client_id" class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <button @click="resetFilters" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Invoice Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-0 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ invoice.invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ invoice.client_name }}</div>
                                            <div v-if="invoice.client_name_en" class="text-xs text-gray-500">{{ invoice.client_name_en }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-medium text-gray-700">{{ formatDate(invoice.issue_date) }}</div>
                                            <div v-if="invoice.due_date" class="text-[10px] text-gray-400">Due: {{ formatDate(invoice.due_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                            {{ formatCurrency(invoice.total, invoice.currency) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getStatusBadgeClass(invoice.status)">
                                                {{ invoice.status.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-3">
                                            <button 
                                                @click="duplicateInvoice(invoice.id)" 
                                                class="text-gray-600 hover:text-indigo-600 transition-colors"
                                                title="Duplicate"
                                            >
                                                Duplicate
                                            </button>
                                            <Link :href="route('invoices.edit', invoice.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            <button @click="deleteInvoice(invoice.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="invoices.data.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            No invoices found matching your criteria.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="invoices.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex flex-wrap -mb-1 justify-center sm:justify-end">
                                <template v-for="(link, key) in invoices.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-3 py-1.5 text-xs leading-4 text-gray-400 border rounded bg-white" v-html="link.label" />
                                    <Link v-else class="mr-1 mb-1 px-3 py-1.5 text-xs leading-4 border rounded hover:bg-gray-100 focus:border-indigo-500 focus:text-indigo-500 transition-colors" :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': link.active, 'bg-white': !link.active }" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
