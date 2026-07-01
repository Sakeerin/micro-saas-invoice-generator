<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { PhotoIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ company: Object });

const page = usePage();
const flash = computed(() => page.props.flash);

const form = useForm({
    name:              props.company.name ?? '',
    name_en:           props.company.name_en ?? '',
    address:           props.company.address ?? '',
    address_en:        props.company.address_en ?? '',
    tax_id:            props.company.tax_id ?? '',
    phone:             props.company.phone ?? '',
    email:             props.company.email ?? '',
    brand_color:       props.company.brand_color ?? '#1a56db',
    bank_name:         props.company.bank_name ?? '',
    bank_account:      props.company.bank_account ?? '',
    bank_account_name: props.company.bank_account_name ?? '',
    logo:              null,
});

const logoPreview = ref(props.company.logo_url ?? null);
const logoInput = ref(null);

function pickLogo() { logoInput.value?.click(); }

function onLogoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.logo = file;
    logoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post(route('settings.company.update'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Company Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">ตั้งค่าบริษัท</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">

                <!-- Flash -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                    {{ flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Logo + Brand Color -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">โลโก้ และสีแบรนด์</h3>
                        <div class="flex items-start gap-6">
                            <div>
                                <div
                                    class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden cursor-pointer hover:border-indigo-400 transition-colors"
                                    @click="pickLogo"
                                >
                                    <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain" />
                                    <PhotoIcon v-else class="w-8 h-8 text-gray-300" />
                                </div>
                                <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                                <button type="button" @click="pickLogo" class="mt-2 text-xs text-indigo-600 hover:underline">
                                    เปลี่ยนโลโก้
                                </button>
                                <p v-if="form.errors.logo" class="text-xs text-red-500 mt-1">{{ form.errors.logo }}</p>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">สีแบรนด์</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="form.brand_color"
                                        type="color"
                                        class="w-12 h-10 rounded border border-gray-300 cursor-pointer"
                                    />
                                    <input
                                        v-model="form.brand_color"
                                        type="text"
                                        maxlength="7"
                                        placeholder="#1a56db"
                                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <div class="w-8 h-8 rounded-lg shadow-sm" :style="{ background: form.brand_color }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">ข้อมูลบริษัท</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบริษัท (ไทย) <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบริษัท (อังกฤษ)</label>
                                <input v-model="form.name_en" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่ (ไทย)</label>
                                <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่ (อังกฤษ)</label>
                                <textarea v-model="form.address_en" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เลขประจำตัวผู้เสียภาษี (13 หลัก)</label>
                                <input v-model="form.tax_id" type="text" maxlength="13" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                                <p v-if="form.errors.tax_id" class="mt-1 text-xs text-red-500">{{ form.errors.tax_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                                <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                                <input v-model="form.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Bank Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">ข้อมูลธนาคาร</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ธนาคาร</label>
                                <input v-model="form.bank_name" type="text" placeholder="เช่น ธนาคารกสิกรไทย" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เลขบัญชี</label>
                                <input v-model="form.bank_account" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อบัญชี</label>
                                <input v-model="form.bank_account_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
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
