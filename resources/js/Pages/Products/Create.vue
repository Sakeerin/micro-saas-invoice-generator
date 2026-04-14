<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    name_en: '',
    description: '',
    unit: 'งาน',
    unit_price: '',
    currency: 'THB',
    default_wht_rate: '3.00',
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Add Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Add Product / Service
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
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
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="3"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="unit_price" value="Price *" />
                                    <TextInput
                                        id="unit_price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.unit_price"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.unit_price" />
                                </div>
                                <div>
                                    <InputLabel for="currency" value="Currency" />
                                    <select
                                        id="currency"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.currency"
                                    >
                                        <option value="THB">THB</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.currency" />
                                </div>
                                <div>
                                    <InputLabel for="unit" value="Unit *" />
                                    <TextInput
                                        id="unit"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.unit"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.unit" />
                                </div>
                            </div>

                            <div class="w-1/3">
                                <InputLabel for="default_wht_rate" value="Default WHT Rate (%)" />
                                <select
                                    id="default_wht_rate"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.default_wht_rate"
                                >
                                    <option value="0.00">0% - No WHT</option>
                                    <option value="1.00">1% - Rent, Interest</option>
                                    <option value="3.00">3% - Service, Labor</option>
                                    <option value="5.00">5% - Advertising</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.default_wht_rate" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Product</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
