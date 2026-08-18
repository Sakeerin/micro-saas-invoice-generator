<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CheckIcon,
    DocumentTextIcon,
    SparklesIcon,
    EyeIcon,
    BanknotesIcon,
    ChartBarIcon,
    GlobeAltIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    PlayCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const isLoggedIn = page.props.auth?.user;

const features = [
    {
        icon: ShieldCheckIcon,
        color: 'text-green-600 bg-green-50',
        title: 'VAT 7% + WHT อัตโนมัติ',
        desc: 'คำนวณถูกต้องตามกฎหมายภาษีไทยทุกครั้ง รองรับ WHT 0%, 1%, 3%, 5% — ไม่ผิดพลาดอีกต่อไป',
    },
    {
        icon: GlobeAltIcon,
        color: 'text-blue-600 bg-blue-50',
        title: '2 ภาษาในใบเดียว',
        desc: 'ไทย–อังกฤษในหน้าเดียวกัน เหมาะสำหรับลูกค้าต่างชาติ ดูเป็น professional ทันที',
    },
    {
        icon: EyeIcon,
        color: 'text-indigo-600 bg-indigo-50',
        title: 'Share Link + ติดตาม "Viewed"',
        desc: 'ส่ง link ให้ลูกค้า รู้ทันทีเมื่อเขาเปิดดู — ไม่ต้องเดาว่าได้รับแล้วหรือยัง',
    },
    {
        icon: SparklesIcon,
        color: 'text-purple-600 bg-purple-50',
        title: 'AI แนะนำ Line Items',
        desc: 'Claude AI วิเคราะห์ประวัติ invoice และแนะนำรายการที่น่าจะใช้ — ประหยัดเวลากรอกซ้ำ',
    },
    {
        icon: DocumentTextIcon,
        color: 'text-amber-600 bg-amber-50',
        title: '5 Templates PDF สวยงาม',
        desc: 'Modern, Classic, Minimal, Corporate, Creative — ทุก template รองรับ Thai font สมบูรณ์',
    },
    {
        icon: ChartBarIcon,
        color: 'text-rose-600 bg-rose-50',
        title: 'Dashboard + Analytics',
        desc: 'รายได้รวม, ค้างชำระ, top clients, revenue chart — รู้ฐานะธุรกิจได้ทุกวัน',
    },
];

const testimonials = [
    {
        name: 'คุณอรุณ ชัยวัฒน์',
        role: 'Freelance Developer',
        avatar: 'อ',
        color: 'bg-indigo-500',
        text: 'ก่อนหน้านี้คำนวณ WHT ผิดเป็นประจำ ตอนนี้ไม่ต้องคิดเลย ระบบทำให้หมด ออก invoice ได้ใน 2 นาที',
    },
    {
        name: 'คุณพิมพ์ ศิริมา',
        role: 'Graphic Designer',
        avatar: 'พ',
        color: 'bg-rose-500',
        text: 'Share link แล้วรู้ว่าลูกค้าเปิดอ่านแล้ว ทำให้ follow up ได้ถูกจังหวะ ปิดงานได้เร็วขึ้นมาก',
    },
    {
        name: 'คุณธนกิจ รุ่งเรือง',
        role: 'Content Creator Agency',
        avatar: 'ธ',
        color: 'bg-emerald-500',
        text: 'ใช้กับทีม 3 คน ออก invoice หลาย client ต่อเดือน AI แนะนำ line items ช่วยได้มากจริงๆ',
    },
];

const faqs = [
    {
        q: 'ทดลองใช้ฟรีได้ไหม?',
        a: 'ได้เลย แผน Free ใช้ฟรีตลอดชีพ ออก invoice ได้ 5 ใบ/เดือน ไม่ต้องใส่บัตรเครดิต',
    },
    {
        q: 'WHT คำนวณถูกต้องตามกฎหมายไทยไหม?',
        a: 'ถูกต้อง — WHT คำนวณจาก subtotal ก่อน VAT ตามกฎหมายภาษีไทย รองรับ 0%, 1%, 3%, 5% ครบ',
    },
    {
        q: 'PDF รองรับภาษาไทยไหม?',
        a: 'รองรับสมบูรณ์ ใช้ Puppeteer + font Sarabun/NotoSansThai — ไม่มีตัวอักษรเป็นกล่องแน่นอน',
    },
    {
        q: 'ยกเลิก Pro ได้ตลอดเวลาไหม?',
        a: 'ได้ทุกเมื่อ ยังใช้ Pro ได้ถึงสิ้นรอบที่ชำระไปแล้ว ไม่มีค่าปรับ',
    },
    {
        q: 'ข้อมูล invoice ปลอดภัยไหม?',
        a: 'ปลอดภัย เซิร์ฟเวอร์อยู่ใน Cloud ที่ผ่านมาตรฐาน ข้อมูลเข้ารหัส ไม่แชร์กับบุคคลที่สาม',
    },
];

const openFaq = ref(null);
function toggleFaq(i) {
    openFaq.value = openFaq.value === i ? null : i;
}

const plans = [
    {
        key: 'free', name: 'Free', price: 0,
        features: ['5 invoice/เดือน', '1 template', 'VAT+WHT', 'Share link', 'Download PDF'],
        cta: 'เริ่มฟรี', highlight: false,
    },
    {
        key: 'pro', name: 'Pro', price: 199,
        features: ['Invoice ไม่จำกัด', 'ทุก 5 templates + Logo', 'AI autofill (Claude)', 'Email tracking', 'Dashboard analytics'],
        cta: 'เริ่มใช้ Pro', highlight: true,
    },
    {
        key: 'business', name: 'Business', price: 499,
        features: ['ทุกอย่างใน Pro', '3 ผู้ใช้', 'Custom template', 'Priority support'],
        cta: 'เริ่มใช้ Business', highlight: false,
    },
];
</script>

<template>
    <Head title="Invoice App — ออก Invoice ภาษีไทยถูกต้อง VAT+WHT อัตโนมัติ" />

    <div class="min-h-screen bg-white font-sans">

        <!-- ── Navbar ────────────────────────────────────────────────────────── -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <DocumentTextIcon class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">Invoice<span class="text-indigo-600">App</span></span>
                </div>

                <nav class="hidden md:flex items-center gap-8 text-sm text-gray-600">
                    <a href="#features" class="hover:text-gray-900 transition-colors">Features</a>
                    <a href="#pricing" class="hover:text-gray-900 transition-colors">Pricing</a>
                    <a href="#faq" class="hover:text-gray-900 transition-colors">FAQ</a>
                </nav>

                <div class="flex items-center gap-3">
                    <template v-if="isLoggedIn">
                        <Link :href="route('dashboard')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            Dashboard →
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm text-gray-600 hover:text-gray-900 hidden sm:block">
                            เข้าสู่ระบบ
                        </Link>
                        <Link :href="route('demo')" class="text-sm border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 hidden sm:block">
                            ลอง Demo
                        </Link>
                        <Link :href="route('register')" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-semibold transition-colors">
                            เริ่มฟรี
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- ── Hero ──────────────────────────────────────────────────────────── -->
        <section class="relative overflow-hidden bg-gradient-to-b from-indigo-50 via-white to-white pt-20 pb-24">
            <!-- Background decoration -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-100 rounded-full opacity-50 blur-3xl" />
                <div class="absolute top-40 -left-20 w-72 h-72 bg-purple-100 rounded-full opacity-40 blur-3xl" />
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
                        <SparklesIcon class="w-3.5 h-3.5" />
                        ออกใบแจ้งหนี้สำหรับ Freelancer และ SME ไทย
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                        Invoice ภาษีไทย<br>
                        <span class="text-indigo-600">คำนวณ WHT ถูกต้อง</span><br>
                        อัตโนมัติ
                    </h1>

                    <p class="text-lg sm:text-xl text-gray-500 max-w-2xl mx-auto mb-10">
                        ออก invoice ด้วย VAT 7% + WHT อัตโนมัติ ตามกฎหมายภาษีไทย
                        ในสองภาษา ส่งลูกค้า รู้ว่าเปิดอ่านแล้ว
                        — ไม่ต้องง้อ Excel ที่คำนวณผิดบ่อย
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-2 px-7 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-base rounded-xl transition-colors shadow-lg shadow-indigo-200"
                        >
                            เริ่มต้นฟรี — ไม่ต้องใส่บัตรเครดิต
                            <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="route('demo')"
                            class="inline-flex items-center gap-2 px-7 py-3.5 bg-white border border-gray-300 text-gray-700 font-semibold text-base rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            <PlayCircleIcon class="w-5 h-5 text-indigo-500" />
                            ลอง Demo ทันที
                        </Link>
                    </div>

                    <p class="mt-4 text-xs text-gray-400">ฟรีสูงสุด 5 invoice/เดือน · ไม่จำกัดเวลา</p>
                </div>

                <!-- Mock Invoice Preview -->
                <div class="mt-16 max-w-2xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                        <!-- Mock browser chrome -->
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400" />
                            <div class="w-3 h-3 rounded-full bg-yellow-400" />
                            <div class="w-3 h-3 rounded-full bg-green-400" />
                            <div class="flex-1 mx-4 bg-white border border-gray-200 rounded px-3 py-1 text-xs text-gray-400">
                                app.invoiceapp.co/invoices/create
                            </div>
                        </div>
                        <!-- Mock invoice builder -->
                        <div class="p-6 space-y-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-xs text-gray-400 mb-1">เลขที่ invoice</div>
                                    <div class="font-mono font-bold text-indigo-600 text-sm">INV-2026-0042</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400 mb-1">สถานะ</div>
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Paid ✓</span>
                                </div>
                            </div>
                            <!-- Line items table mock -->
                            <div class="border border-gray-100 rounded-xl overflow-hidden">
                                <div class="bg-indigo-600 text-white text-xs px-4 py-2 grid grid-cols-4 gap-2">
                                    <span class="col-span-2">รายการ / Description</span>
                                    <span class="text-right">ราคา/หน่วย</span>
                                    <span class="text-right">รวม</span>
                                </div>
                                <div class="text-xs px-4 py-2.5 grid grid-cols-4 gap-2 border-b border-gray-50">
                                    <span class="col-span-2 text-gray-700">พัฒนาเว็บไซต์ / Website Development</span>
                                    <span class="text-right text-gray-600">50,000</span>
                                    <span class="text-right font-semibold">50,000</span>
                                </div>
                                <div class="text-xs px-4 py-2.5 grid grid-cols-4 gap-2 bg-gray-50">
                                    <span class="col-span-2 text-gray-700">ออกแบบ UI/UX / UI Design</span>
                                    <span class="text-right text-gray-600">15,000</span>
                                    <span class="text-right font-semibold">15,000</span>
                                </div>
                            </div>
                            <!-- Tax summary -->
                            <div class="flex justify-end">
                                <div class="text-xs space-y-1 text-right w-48">
                                    <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>65,000</span></div>
                                    <div class="flex justify-between text-gray-500"><span>VAT 7%</span><span>4,550</span></div>
                                    <div class="flex justify-between text-red-500"><span>WHT 3%</span><span>-1,950</span></div>
                                    <div class="flex justify-between font-bold text-indigo-600 text-sm border-t border-gray-200 pt-1">
                                        <span>ยอดสุทธิ</span><span>฿67,600</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Social proof strip ─────────────────────────────────────────────── -->
        <section class="bg-gray-50 border-y border-gray-100 py-6">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <p class="text-sm text-gray-500">
                    เชื่อมั่นโดย freelancer และ SME ไทยกว่า
                    <span class="font-semibold text-gray-800">500+</span> ราย ·
                    Invoice ที่สร้างแล้ว
                    <span class="font-semibold text-gray-800">12,000+</span> ฉบับ ·
                    รายได้ที่ผ่านระบบ
                    <span class="font-semibold text-gray-800">฿80M+</span>
                </p>
            </div>
        </section>

        <!-- ── Features ──────────────────────────────────────────────────────── -->
        <section id="features" class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        ทุกอย่างที่ Freelancer ไทยต้องการ
                    </h2>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                        ออกแบบมาเพื่อตลาดไทยโดยเฉพาะ — ไม่ใช่ translate มาจากของฝรั่ง
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="feat in features"
                        :key="feat.title"
                        class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" :class="feat.color">
                            <component :is="feat.icon" class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">{{ feat.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── WHT explainer ─────────────────────────────────────────────────── -->
        <section class="bg-indigo-600 py-20">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="text-indigo-300 text-sm font-semibold mb-3 uppercase tracking-wider">ทำไมถึงสำคัญ</div>
                        <h2 class="text-3xl font-bold text-white mb-5">
                            คำนวณ WHT ผิด<br>
                            โดนสรรพากรสอบ
                        </h2>
                        <p class="text-indigo-200 leading-relaxed mb-6">
                            หลาย freelancer คำนวณ WHT ผิดเพราะ Excel formula ไม่ถูกต้อง
                            บางคนคำนวณ WHT จาก ยอดรวม VAT แล้ว ซึ่งผิดกฎหมาย
                            WHT ต้องคำนวณจาก subtotal ก่อน VAT เสมอ
                        </p>
                        <Link :href="route('register')" class="inline-flex items-center gap-2 bg-white text-indigo-600 font-semibold px-5 py-3 rounded-xl hover:bg-indigo-50 transition-colors text-sm">
                            แก้ปัญหานี้ฟรี <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                    </div>
                    <!-- Comparison card -->
                    <div class="bg-white/10 rounded-2xl p-6 text-white text-sm">
                        <p class="text-indigo-200 text-xs font-semibold mb-4 uppercase tracking-wider">ตัวอย่างการคำนวณที่ถูกต้อง</p>
                        <div class="space-y-2">
                            <div class="flex justify-between"><span class="text-indigo-200">Subtotal</span><span class="font-mono">฿65,000</span></div>
                            <div class="flex justify-between"><span class="text-indigo-200">WHT 3% (จาก subtotal)</span><span class="font-mono text-red-300">-฿1,950</span></div>
                            <div class="flex justify-between"><span class="text-indigo-200">VAT 7%</span><span class="font-mono">+฿4,550</span></div>
                            <div class="border-t border-white/20 pt-2 flex justify-between font-bold text-base">
                                <span>ยอดที่รับจริง</span><span class="font-mono">฿67,600</span>
                            </div>
                        </div>
                        <p class="mt-4 text-indigo-300 text-xs">* WHT คิดจาก subtotal ก่อน VAT ตาม พ.ร.บ. ภาษีเงินได้</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Video Demo ─────────────────────────────────────────────────────── -->
        <section class="py-24 bg-gray-50">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">ดูการทำงานจริง</h2>
                <p class="text-gray-500 mb-10">ออก invoice สมบูรณ์ตั้งแต่ต้นจนจบใน 2 นาที</p>

                <!-- Demo CTA instead of video embed -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 aspect-video flex items-center justify-center relative">
                        <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                                    <PlayCircleIcon class="w-10 h-10 text-white" />
                                </div>
                                <p class="text-white font-semibold text-lg">Video Demo</p>
                                <p class="text-white/70 text-sm mt-1">coming soon — ลองใช้ Demo account ได้ทันที</p>
                            </div>
                        </div>
                        <!-- Mock UI elements in background -->
                        <div class="absolute inset-4 bg-white/5 rounded-xl border border-white/10" />
                    </div>
                    <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-left">
                            <p class="font-semibold text-gray-900">อยากลองใช้เลย?</p>
                            <p class="text-sm text-gray-500">Demo account พร้อมข้อมูลตัวอย่างให้สำรวจ</p>
                        </div>
                        <Link
                            :href="route('demo')"
                            class="inline-flex items-center gap-2 bg-indigo-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition-colors text-sm whitespace-nowrap"
                        >
                            เปิด Demo ทันที <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Testimonials ──────────────────────────────────────────────────── -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">เสียงจาก Beta Users</h2>
                    <p class="text-gray-500">คนที่ใช้จริงพูดอะไรเกี่ยวกับ Invoice App</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        v-for="t in testimonials"
                        :key="t.name"
                        class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm"
                    >
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" :class="t.color">
                                {{ t.avatar }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ t.name }}</p>
                                <p class="text-xs text-gray-400">{{ t.role }}</p>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">"{{ t.text }}"</p>
                        <div class="mt-3 flex gap-0.5">
                            <span v-for="i in 5" :key="i" class="text-amber-400 text-sm">★</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Pricing ───────────────────────────────────────────────────────── -->
        <section id="pricing" class="py-24 bg-gray-50">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">ราคาตรงไปตรงมา</h2>
                    <p class="text-gray-500">ไม่มีค่าใช้จ่ายซ่อนเร้น · ยกเลิกได้ทุกเมื่อ</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="plan in plans"
                        :key="plan.key"
                        class="relative bg-white rounded-2xl border p-6 flex flex-col shadow-sm"
                        :class="plan.highlight ? 'border-indigo-400 ring-2 ring-indigo-300' : 'border-gray-200'"
                    >
                        <div v-if="plan.highlight" class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">ยอดนิยม</span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ plan.name }}</h3>
                        <div class="mb-4">
                            <span class="text-3xl font-extrabold text-gray-900">{{ plan.price === 0 ? 'ฟรี' : '฿' + plan.price }}</span>
                            <span v-if="plan.price > 0" class="text-gray-400 text-sm">/เดือน</span>
                        </div>
                        <ul class="space-y-2 flex-1 mb-6">
                            <li v-for="f in plan.features" :key="f" class="flex items-center gap-2 text-sm text-gray-600">
                                <CheckIcon class="w-4 h-4 text-green-500 shrink-0" />
                                {{ f }}
                            </li>
                        </ul>
                        <Link
                            :href="route('register')"
                            class="block text-center py-2.5 rounded-xl text-sm font-semibold transition-colors"
                            :class="plan.highlight ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-800'"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-400 mt-8">
                    รายปี: Pro ฿1,990 · Business ฿4,990 (ประหยัด 2 เดือน)
                </p>
            </div>
        </section>

        <!-- ── FAQ ───────────────────────────────────────────────────────────── -->
        <section id="faq" class="py-24">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">คำถามที่พบบ่อย</h2>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="(faq, i) in faqs"
                        :key="i"
                        class="bg-white border border-gray-200 rounded-xl overflow-hidden"
                    >
                        <button
                            class="w-full flex items-center justify-between px-5 py-4 text-left"
                            @click="toggleFaq(i)"
                        >
                            <span class="font-semibold text-gray-800 text-sm">{{ faq.q }}</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="openFaq === i ? 'rotate-45' : ''">+</span>
                        </button>
                        <div v-if="openFaq === i" class="px-5 pb-4 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-3">
                            {{ faq.a }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Final CTA ──────────────────────────────────────────────────────── -->
        <section class="bg-indigo-600 py-20">
            <div class="mx-auto max-w-3xl px-4 text-center">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                    พร้อมออก Invoice แบบถูกต้องแล้วใช่ไหม?
                </h2>
                <p class="text-indigo-200 mb-8 text-lg">
                    เริ่มต้นฟรี 5 invoice/เดือน ไม่ต้องใส่บัตรเครดิต
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 bg-white text-indigo-600 font-bold px-8 py-4 rounded-xl hover:bg-indigo-50 transition-colors text-base shadow-lg"
                    >
                        สมัครฟรีเดี๋ยวนี้ <ArrowRightIcon class="w-5 h-5" />
                    </Link>
                    <Link
                        :href="route('demo')"
                        class="inline-flex items-center gap-2 border-2 border-white/30 text-white font-semibold px-6 py-4 rounded-xl hover:bg-white/10 transition-colors text-base"
                    >
                        ดู Demo ก่อน
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── Footer ─────────────────────────────────────────────────────────── -->
        <footer class="bg-gray-900 text-gray-400 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                    <div class="col-span-2 md:col-span-1">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                                <DocumentTextIcon class="w-4 h-4 text-white" />
                            </div>
                            <span class="text-white font-bold">InvoiceApp</span>
                        </div>
                        <p class="text-xs leading-relaxed">Invoice ภาษีไทยที่ถูกต้องตามกฎหมาย สำหรับ Freelancer และ SME</p>
                    </div>
                    <div>
                        <h4 class="text-white text-sm font-semibold mb-3">ผลิตภัณฑ์</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                            <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                            <li><Link :href="route('demo')" class="hover:text-white transition-colors">Demo</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white text-sm font-semibold mb-3">บัญชี</h4>
                        <ul class="space-y-2 text-xs">
                            <li><Link :href="route('login')" class="hover:text-white transition-colors">เข้าสู่ระบบ</Link></li>
                            <li><Link :href="route('register')" class="hover:text-white transition-colors">สมัครสมาชิก</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white text-sm font-semibold mb-3">ข้อมูล</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#faq" class="hover:text-white transition-colors">FAQ</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">นโยบายความเป็นส่วนตัว</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-6 text-center text-xs">
                    © {{ new Date().getFullYear() }} InvoiceApp. สงวนลิขสิทธิ์ · ข้อมูลผ่านระบบเข้ารหัส SSL
                </div>
            </div>
        </footer>

    </div>
</template>
