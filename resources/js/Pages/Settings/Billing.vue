<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CheckIcon, CreditCardIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    subscription: Object,
    plans: Object,
    omisePublicKey: String,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const selectedPlan = ref(null);
const isProcessing = ref(false);
const cardError = ref('');
const showCardForm = ref(false);

const currentPlan = computed(() => props.subscription?.plan ?? 'free');

const planList = [
    {
        key: 'free',
        name: 'Free',
        price: 0,
        invoiceLimit: '5 invoice/เดือน',
        color: 'gray',
        features: ['5 invoice ต่อเดือน', '1 template', 'Download PDF'],
    },
    {
        key: 'pro',
        name: 'Pro',
        price: 199,
        invoiceLimit: 'ไม่จำกัด',
        color: 'indigo',
        features: ['Invoice ไม่จำกัด', 'AI autofill', 'ทุก templates', 'Email tracking'],
    },
    {
        key: 'business',
        name: 'Business',
        price: 499,
        invoiceLimit: 'ไม่จำกัด',
        color: 'purple',
        features: ['ทุกอย่างใน Pro', '3 ผู้ใช้', 'Custom template', 'Priority support'],
    },
];

const colorBorder = { gray: 'border-gray-200', indigo: 'border-indigo-400 ring-2 ring-indigo-200', purple: 'border-purple-300' };
const colorBadge = { gray: 'bg-gray-100 text-gray-700', indigo: 'bg-indigo-600 text-white', purple: 'bg-purple-600 text-white' };

function selectPlan(planKey) {
    if (planKey === currentPlan.value) return;
    selectedPlan.value = planKey;
    showCardForm.value = planKey !== 'free';
    cardError.value = '';
}

const upgradeForm = useForm({ plan: '', card_token: '' });
const cancelForm = useForm({});

async function submitUpgrade() {
    if (!selectedPlan.value || selectedPlan.value === 'free') return;

    isProcessing.value = true;
    cardError.value = '';

    try {
        // Use Omise.js to tokenize the card
        const token = await createOmiseToken();
        upgradeForm.plan = selectedPlan.value;
        upgradeForm.card_token = token;
        upgradeForm.post(route('settings.billing.upgrade'), {
            onError: (errors) => {
                cardError.value = errors.card_token || errors.error || 'เกิดข้อผิดพลาด';
            },
        });
    } catch (err) {
        cardError.value = err.message || 'ไม่สามารถ tokenize บัตรได้';
    } finally {
        isProcessing.value = false;
    }
}

function createOmiseToken() {
    return new Promise((resolve, reject) => {
        if (!window.Omise) {
            reject(new Error('Omise.js ไม่ได้โหลด — กรุณาเพิ่ม OMISE_PUBLIC_KEY'));
            return;
        }
        window.Omise.setPublicKey(props.omisePublicKey);
        // Open Omise card popup
        window.OmiseCard.open({
            frameLabel: 'Invoice App',
            submitLabel: 'ชำระเงิน',
            currency: 'THB',
            amount: (props.plans[selectedPlan.value]?.price ?? 0) * 100,
            onCreateTokenSuccess: (token) => resolve(token),
            onFormClosed: () => reject(new Error('ปิดฟอร์มก่อนชำระ')),
        });
    });
}

function confirmCancel() {
    if (!confirm('ต้องการยกเลิกแผน Pro/Business หรือไม่? คุณจะกลับมาใช้แผน Free เมื่อสิ้นรอบ')) return;
    cancelForm.post(route('settings.billing.cancel'));
}

const monthUsed = computed(() => props.subscription?.invoice_count_this_month ?? 0);
const periodEnd = computed(() => {
    if (!props.subscription?.current_period_end) return null;
    return new Date(props.subscription.current_period_end).toLocaleDateString('th-TH', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
});
</script>

<template>
    <Head title="Billing — Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">การสมัครสมาชิก</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">

                <!-- Flash messages -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    {{ flash.error }}
                </div>

                <!-- Current plan status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">แผนปัจจุบัน</h3>
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900 capitalize">{{ currentPlan }}</p>
                            <p v-if="currentPlan === 'free'" class="text-sm text-gray-500 mt-1">
                                ใช้ไปแล้ว {{ monthUsed }}/5 invoice เดือนนี้
                            </p>
                            <p v-else-if="periodEnd" class="text-sm text-gray-500 mt-1">
                                ต่ออายุ: {{ periodEnd }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a v-if="currentPlan !== 'free'" href="#plans" class="text-sm text-gray-500 hover:text-gray-700">
                                เปลี่ยนแผน
                            </a>
                            <button
                                v-if="currentPlan !== 'free'"
                                @click="confirmCancel"
                                :disabled="cancelForm.processing"
                                class="text-sm text-red-500 hover:text-red-700 disabled:opacity-50"
                            >
                                ยกเลิกแผน
                            </button>
                        </div>
                    </div>

                    <!-- Free plan progress bar -->
                    <div v-if="currentPlan === 'free'" class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Invoice เดือนนี้</span>
                            <span>{{ monthUsed }}/5</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div
                                class="h-2 rounded-full transition-all"
                                :class="monthUsed >= 5 ? 'bg-red-500' : monthUsed >= 3 ? 'bg-amber-500' : 'bg-indigo-500'"
                                :style="{ width: Math.min((monthUsed / 5) * 100, 100) + '%' }"
                            />
                        </div>
                        <p v-if="monthUsed >= 5" class="mt-2 text-xs text-red-600 font-medium">
                            ครบ 5 invoice แล้ว — อัปเกรดเพื่อออก invoice เพิ่ม
                        </p>
                    </div>
                </div>

                <!-- Plan selector -->
                <div id="plans" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">เลือกแผน</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div
                            v-for="plan in planList"
                            :key="plan.key"
                            class="relative rounded-xl border p-5 cursor-pointer transition-all"
                            :class="[
                                colorBorder[plan.color],
                                selectedPlan === plan.key ? 'ring-2 ring-offset-1 ring-indigo-500' : '',
                                plan.key === currentPlan ? 'opacity-60 cursor-not-allowed' : 'hover:shadow-md',
                            ]"
                            @click="selectPlan(plan.key)"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="colorBadge[plan.color]">
                                    {{ plan.name }}
                                </span>
                                <span v-if="plan.key === currentPlan" class="text-xs text-gray-400 font-medium">แผนปัจจุบัน</span>
                                <div
                                    v-else-if="selectedPlan === plan.key"
                                    class="w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center"
                                >
                                    <CheckIcon class="w-3 h-3 text-white" />
                                </div>
                            </div>
                            <p class="text-xl font-bold text-gray-900 mt-2">
                                {{ plan.price === 0 ? 'ฟรี' : '฿' + plan.price }}
                                <span v-if="plan.price > 0" class="text-xs text-gray-400 font-normal">/เดือน</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ plan.invoiceLimit }}</p>
                            <ul class="mt-3 space-y-1">
                                <li v-for="f in plan.features" :key="f" class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <CheckIcon class="w-3 h-3 text-green-500 shrink-0" />
                                    {{ f }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payment form -->
                <div v-if="showCardForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <CreditCardIcon class="w-5 h-5 text-indigo-500" />
                        <h3 class="text-sm font-semibold text-gray-700">ชำระเงิน</h3>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-4 text-sm text-indigo-700">
                        คุณกำลังอัปเกรดเป็นแผน <strong class="capitalize">{{ selectedPlan }}</strong>
                        — ฿{{ props.plans[selectedPlan]?.price ?? 0 }}/เดือน
                    </div>

                    <p v-if="cardError" class="mb-3 text-sm text-red-600">{{ cardError }}</p>

                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                        <ShieldCheckIcon class="w-4 h-4 text-green-500" />
                        ชำระผ่าน Omise — ปลอดภัย PCI DSS Level 1 · ไม่เก็บข้อมูลบัตรในเซิร์ฟเวอร์
                    </div>

                    <button
                        @click="submitUpgrade"
                        :disabled="isProcessing || upgradeForm.processing"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition-colors disabled:opacity-60"
                    >
                        <span v-if="isProcessing || upgradeForm.processing">กำลังดำเนินการ…</span>
                        <span v-else>อัปเกรดเป็น {{ selectedPlan === 'pro' ? 'Pro' : 'Business' }} →</span>
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
