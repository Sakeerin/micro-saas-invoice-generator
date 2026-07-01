<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/outline';

const plans = [
    {
        key: 'free',
        name: 'Free',
        price: 0,
        period: '/เดือน',
        description: 'เหมาะสำหรับ freelancer ที่เพิ่งเริ่มต้น',
        color: 'gray',
        features: [
            '5 invoice ต่อเดือน',
            '1 template (Modern)',
            'Download PDF',
            'Share link สำหรับลูกค้า',
            'คำนวณ VAT + WHT อัตโนมัติ',
        ],
        missing: ['AI Line Item Suggestions', 'Email invoice พร้อม tracking', 'ทุก template', 'Logo ในใบ invoice'],
    },
    {
        key: 'pro',
        name: 'Pro',
        price: 199,
        period: '/เดือน',
        description: 'เหมาะสำหรับ freelancer และ SME ที่ออก invoice บ่อย',
        color: 'indigo',
        highlight: true,
        features: [
            'Invoice ไม่จำกัด',
            '5 templates + Logo บริษัท',
            'AI แนะนำ line items (Claude)',
            'Email invoice + tracking "viewed"',
            'คำนวณ VAT + WHT อัตโนมัติ',
            'Share link + ประวัติ status',
            'Dashboard + Analytics',
        ],
        missing: [],
    },
    {
        key: 'business',
        name: 'Business',
        price: 499,
        period: '/เดือน',
        description: 'เหมาะสำหรับ agency และทีมขนาดเล็ก',
        color: 'purple',
        features: [
            'ทุกอย่างใน Pro',
            'สมาชิก 3 คน',
            'Custom template',
            'Priority support',
            'API access (coming soon)',
        ],
        missing: [],
    },
];

const colorMap = {
    gray: {
        badge: 'bg-gray-100 text-gray-700',
        button: 'bg-gray-800 hover:bg-gray-900 text-white',
        border: 'border-gray-200',
        check: 'text-gray-500',
        ring: '',
    },
    indigo: {
        badge: 'bg-indigo-600 text-white',
        button: 'bg-indigo-600 hover:bg-indigo-700 text-white',
        border: 'border-indigo-400 ring-2 ring-indigo-400',
        check: 'text-indigo-600',
        ring: 'ring-2 ring-indigo-400',
    },
    purple: {
        badge: 'bg-purple-600 text-white',
        button: 'bg-purple-600 hover:bg-purple-700 text-white',
        border: 'border-purple-300',
        check: 'text-purple-600',
        ring: '',
    },
};
</script>

<template>
    <Head title="Pricing — Invoice App" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <Link href="/" class="text-xl font-bold text-indigo-600">Invoice App</Link>
                <div class="flex items-center gap-4">
                    <Link :href="route('login')" class="text-sm text-gray-600 hover:text-gray-900">เข้าสู่ระบบ</Link>
                    <Link :href="route('register')" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">สมัครฟรี</Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <div class="py-16 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">เลือกแผนที่เหมาะกับคุณ</h1>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                เริ่มต้นฟรี ไม่ต้องใส่บัตรเครดิต · อัปเกรดได้ทุกเมื่อ · ยกเลิกได้ตลอดเวลา
            </p>
        </div>

        <!-- Plans grid -->
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 pb-24">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.key"
                    class="relative bg-white rounded-2xl border p-8 flex flex-col shadow-sm"
                    :class="colorMap[plan.color].border"
                >
                    <!-- Popular badge -->
                    <div v-if="plan.highlight" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                            ยอดนิยม
                        </span>
                    </div>

                    <div class="mb-6">
                        <span
                            class="inline-block text-sm font-semibold px-3 py-1 rounded-full mb-3"
                            :class="colorMap[plan.color].badge"
                        >
                            {{ plan.name }}
                        </span>
                        <div class="flex items-end gap-1">
                            <span class="text-4xl font-bold text-gray-900">
                                {{ plan.price === 0 ? 'ฟรี' : '฿' + plan.price.toLocaleString() }}
                            </span>
                            <span v-if="plan.price > 0" class="text-gray-400 text-sm mb-1">{{ plan.period }}</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ plan.description }}</p>
                    </div>

                    <Link
                        :href="route('register')"
                        class="block text-center py-2.5 rounded-xl text-sm font-semibold mb-6 transition-colors"
                        :class="colorMap[plan.color].button"
                    >
                        {{ plan.price === 0 ? 'เริ่มต้นฟรี' : 'เริ่มใช้ ' + plan.name }}
                    </Link>

                    <ul class="space-y-3 flex-1">
                        <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-2 text-sm text-gray-700">
                            <CheckIcon class="w-4 h-4 mt-0.5 shrink-0" :class="colorMap[plan.color].check" />
                            {{ feat }}
                        </li>
                        <li v-for="feat in plan.missing" :key="feat" class="flex items-start gap-2 text-sm text-gray-400 line-through">
                            <span class="w-4 h-4 mt-0.5 shrink-0 flex items-center justify-center">✗</span>
                            {{ feat }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Annual discount note -->
            <p class="mt-10 text-center text-sm text-gray-500">
                ประหยัดเพิ่ม 2 เดือนเมื่อชำระรายปี —
                <strong>Pro ฿1,990/ปี</strong> · <strong>Business ฿4,990/ปี</strong>
                <span class="ml-1 text-indigo-600">(ติดต่อเราเพื่ออัปเกรดรายปี)</span>
            </p>

            <!-- FAQ -->
            <div class="mt-16">
                <h2 class="text-xl font-bold text-gray-900 text-center mb-8">คำถามที่พบบ่อย</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">ยกเลิกได้ตลอดเวลาไหม?</h3>
                        <p class="text-sm text-gray-500">ได้ทุกเมื่อ และคุณยังใช้ Pro ได้ถึงสิ้นรอบที่ชำระไปแล้ว</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">รองรับการชำระด้วยอะไรบ้าง?</h3>
                        <p class="text-sm text-gray-500">บัตรเครดิต/เดบิต และ PromptPay ผ่าน Omise</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">มี WHT (หัก ณ ที่จ่าย) ไหม?</h3>
                        <p class="text-sm text-gray-500">มี ทุกแผน รองรับ WHT 0%, 1%, 3%, 5% ตามกฎหมายภาษีไทย</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1">AI แนะนำ line items คืออะไร?</h3>
                        <p class="text-sm text-gray-500">AI (Claude) วิเคราะห์ประวัติ invoice และแนะนำรายการสินค้า/บริการที่น่าจะใช้กับลูกค้านั้น</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
