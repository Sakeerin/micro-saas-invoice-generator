<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const step = ref(1);

const form = useForm({
    name: '',
    name_en: '',
    address: '',
    address_en: '',
    tax_id: '',
    phone: '',
    email: '',
    bank_name: '',
    bank_account: '',
    bank_account_name: '',
    brand_color: '#1a56db',
    invoice_prefix: 'INV',
    logo: null,
});

const nextStep = () => {
    if (step.value < 3) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submit = () => {
    form.post(route('company.store'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Company Setup Wizard" />

        <div class="mb-8">
            <h2 class="text-center text-2xl font-bold text-gray-900">
                Set up your Company
            </h2>
            <p class="text-center text-sm text-gray-600">
                Step {{ step }} of 3
            </p>
            <div class="mt-4 flex justify-center">
                <div class="flex items-center">
                    <div :class="['h-2 w-12 rounded-full', step >= 1 ? 'bg-indigo-600' : 'bg-gray-200']"></div>
                    <div :class="['ml-2 h-2 w-12 rounded-full', step >= 2 ? 'bg-indigo-600' : 'bg-gray-200']"></div>
                    <div :class="['ml-2 h-2 w-12 rounded-full', step >= 3 ? 'bg-indigo-600' : 'bg-gray-200']"></div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <!-- Step 1: Company Info -->
            <div v-if="step === 1">
                <div class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Company Name (Thai)" />
                        <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="name_en" value="Company Name (English)" />
                        <TextInput id="name_en" type="text" class="mt-1 block w-full" v-model="form.name_en" />
                        <InputError class="mt-2" :message="form.errors.name_en" />
                    </div>

                    <div>
                        <InputLabel for="tax_id" value="Tax ID (13 digits)" />
                        <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" maxlength="13" />
                        <InputError class="mt-2" :message="form.errors.tax_id" />
                    </div>

                    <div>
                        <InputLabel for="address" value="Address (Thai)" />
                        <textarea
                            id="address"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.address"
                            rows="3"
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <div>
                        <InputLabel for="logo" value="Company Logo" />
                        <input
                            id="logo"
                            type="file"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                            @input="form.logo = $event.target.files[0]"
                            accept="image/*"
                        />
                        <InputError class="mt-2" :message="form.errors.logo" />
                    </div>
                </div>
            </div>

            <!-- Step 2: Bank Info -->
            <div v-if="step === 2">
                <div class="space-y-4">
                    <div>
                        <InputLabel for="bank_name" value="Bank Name" />
                        <TextInput id="bank_name" type="text" class="mt-1 block w-full" v-model="form.bank_name" placeholder="e.g. Kasikorn Bank" />
                        <InputError class="mt-2" :message="form.errors.bank_name" />
                    </div>

                    <div>
                        <InputLabel for="bank_account" value="Bank Account Number" />
                        <TextInput id="bank_account" type="text" class="mt-1 block w-full" v-model="form.bank_account" />
                        <InputError class="mt-2" :message="form.errors.bank_account" />
                    </div>

                    <div>
                        <InputLabel for="bank_account_name" value="Account Holder Name" />
                        <TextInput id="bank_account_name" type="text" class="mt-1 block w-full" v-model="form.bank_account_name" />
                        <InputError class="mt-2" :message="form.errors.bank_account_name" />
                    </div>
                </div>
            </div>

            <!-- Step 3: Invoice Settings -->
            <div v-if="step === 3">
                <div class="space-y-4">
                    <div>
                        <InputLabel for="invoice_prefix" value="Invoice Prefix" />
                        <TextInput id="invoice_prefix" type="text" class="mt-1 block w-full" v-model="form.invoice_prefix" />
                        <InputError class="mt-2" :message="form.errors.invoice_prefix" />
                    </div>

                    <div>
                        <InputLabel for="brand_color" value="Brand Color" />
                        <div class="mt-1 flex items-center">
                            <input
                                id="brand_color"
                                type="color"
                                class="h-10 w-10 cursor-pointer rounded border-gray-300"
                                v-model="form.brand_color"
                            />
                            <TextInput type="text" class="ml-2 block w-full" v-model="form.brand_color" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.brand_color" />
                    </div>

                    <div class="mt-4 rounded-lg border p-4" :style="{ borderColor: form.brand_color }">
                        <p class="text-sm font-semibold" :style="{ color: form.brand_color }">Brand Color Preview</p>
                        <div class="mt-2 h-4 w-full rounded" :style="{ backgroundColor: form.brand_color }"></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button
                    v-if="step > 1"
                    type="button"
                    @click="prevStep"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Previous
                </button>
                <div v-else></div>

                <PrimaryButton
                    v-if="step < 3"
                    type="button"
                    @click="nextStep"
                    class="ms-4"
                >
                    Next
                </PrimaryButton>

                <PrimaryButton
                    v-else
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Finish Setup
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
