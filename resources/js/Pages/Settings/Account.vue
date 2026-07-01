<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ user: Object });

const page = usePage();
const flash = computed(() => page.props.flash);

const profileForm = useForm({
    name:  props.user.name ?? '',
    email: props.user.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password:         '',
    password_confirmation: '',
});

const deleteForm = useForm({ password: '' });
const showDeleteModal = ref(false);

function submitProfile() {
    profileForm.patch(route('settings.account.update'));
}

function submitPassword() {
    passwordForm.put(route('settings.account.password'), {
        onSuccess: () => passwordForm.reset(),
    });
}

function confirmDelete() {
    deleteForm.delete(route('settings.account.delete'), {
        onError: () => { deleteForm.reset('password'); },
    });
}

const joinDate = computed(() => {
    if (!props.user.created_at) return '—';
    return new Date(props.user.created_at).toLocaleDateString('th-TH', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
});
</script>

<template>
    <Head title="Account Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">ตั้งค่าบัญชี</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">

                <!-- Flash -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                    {{ flash.success }}
                </div>

                <!-- Profile -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">ข้อมูลส่วนตัว</h3>
                    <form @submit.prevent="submitProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ</label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-500">{{ profileForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-500">{{ profileForm.errors.email }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-400">สมาชิกตั้งแต่ {{ joinDate }}</p>
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition-colors disabled:opacity-60"
                            >
                                {{ profileForm.processing ? 'กำลังบันทึก…' : 'บันทึก' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">เปลี่ยนรหัสผ่าน</h3>
                    <form @submit.prevent="submitPassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านปัจจุบัน</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                autocomplete="current-password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition-colors disabled:opacity-60"
                            >
                                {{ passwordForm.processing ? 'กำลังบันทึก…' : 'เปลี่ยนรหัสผ่าน' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- PDPA: Delete account -->
                <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
                    <div class="flex items-start gap-3">
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-400 mt-0.5 shrink-0" />
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-700 mb-1">ลบบัญชี (PDPA)</h3>
                            <p class="text-sm text-gray-500 mb-3">
                                การลบบัญชีจะลบข้อมูลทั้งหมดของคุณออกจากระบบถาวร ไม่สามารถกู้คืนได้
                                ตามสิทธิ์ตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล (PDPA)
                            </p>
                            <button
                                type="button"
                                @click="showDeleteModal = true"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors"
                            >
                                ลบบัญชีของฉัน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Delete modal -->
                <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">ยืนยันการลบบัญชี</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            กรุณากรอกรหัสผ่านเพื่อยืนยัน การดำเนินการนี้ไม่สามารถยกเลิกได้
                        </p>
                        <form @submit.prevent="confirmDelete" class="space-y-4">
                            <input
                                v-model="deleteForm.password"
                                type="password"
                                placeholder="รหัสผ่านปัจจุบัน"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            />
                            <p v-if="deleteForm.errors.password" class="text-xs text-red-500">{{ deleteForm.errors.password }}</p>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="showDeleteModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50"
                                >
                                    ยกเลิก
                                </button>
                                <button
                                    type="submit"
                                    :disabled="deleteForm.processing || !deleteForm.password"
                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl disabled:opacity-60"
                                >
                                    {{ deleteForm.processing ? 'กำลังลบ…' : 'ลบบัญชี' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
