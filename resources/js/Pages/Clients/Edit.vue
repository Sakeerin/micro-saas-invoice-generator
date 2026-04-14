<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
});

const form = useForm({
    name: props.client.name,
    name_en: props.client.name_en || '',
    address: props.client.address || '',
    address_en: props.client.address_en || '',
    tax_id: props.client.tax_id || '',
    contact_name: props.client.contact_name || '',
    contact_email: props.client.contact_email || '',
    contact_phone: props.client.contact_phone || '',
    default_currency: props.client.default_currency || 'THB',
    notes: props.client.notes || '',
});

const isSearchingDbd = ref(false);

const lookupDbd = async () => {
    if (form.tax_id.length !== 13) {
        alert('Please enter a valid 13-digit Tax ID');
        return;
    }

    isSearchingDbd.value = true;
    try {
        const response = await axios.get(route('api.dbd.lookup', { tax_id: form.tax_id }));
        if (response.data) {
            form.name = response.data.name;
            form.name_en = response.data.name_en;
            form.address = response.data.address;
        }
    } catch (error) {
        console.error('DBD Lookup failed', error);
        alert('Could not find company information. Please enter manually.');
    } finally {
        isSearchingDbd.value = false;
    }
};

const submit = () => {
    form.patch(route('clients.update', props.client.id));
};
</script>

<template>
    <Head title="Edit Client" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Client: {{ client.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <InputLabel for="tax_id" value="Tax ID (13 digits)" />
                                    <TextInput
                                        id="tax_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.tax_id"
                                        maxlength="13"
                                    />
                                    <InputError class="mt-2" :message="form.errors.tax_id" />
                                </div>
                                <button
                                    type="button"
                                    @click="lookupDbd"
                                    :disabled="isSearchingDbd"
                                    class="mb-0.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                                >
                                    {{ isSearchingDbd ? 'Searching...' : 'DBD Lookup' }}
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="name" value="Name (Thai) *" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div>
                                    <InputLabel for="name_en" value="Name (English)" />
                                    <TextInput
                                        id="name_en"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name_en"
                                    />
                                    <InputError class="mt-2" :message="form.errors.name_en" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="address" value="Address (Thai)" />
                                <textarea
                                    id="address"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.address"
                                    rows="3"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div>
                                <InputLabel for="address_en" value="Address (English)" />
                                <textarea
                                    id="address_en"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.address_en"
                                    rows="3"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.address_en" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="contact_name" value="Contact Person" />
                                    <TextInput
                                        id="contact_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_name"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_name" />
                                </div>
                                <div>
                                    <InputLabel for="contact_email" value="Email" />
                                    <TextInput
                                        id="contact_email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_email"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_email" />
                                </div>
                                <div>
                                    <InputLabel for="contact_phone" value="Phone" />
                                    <TextInput
                                        id="contact_phone"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_phone"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_phone" />
                                </div>
                            </div>

                            <div class="w-1/3">
                                <InputLabel for="default_currency" value="Default Currency" />
                                <select
                                    id="default_currency"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.default_currency"
                                >
                                    <option value="THB">THB</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.default_currency" />
                            </div>

                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.notes"
                                    rows="2"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update Client</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
