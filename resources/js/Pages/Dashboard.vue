<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import {
    BanknotesIcon,
    ClockIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    UserGroupIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    revenueByMonth: Array,
    topClients: Array,
    recentInvoices: Array,
    currency: { type: String, default: 'THB' },
});

const chartCanvas = ref(null);
let chartInstance = null;

const fmt = (val) =>
    new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val ?? 0);

const fmtCompact = (val) =>
    new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 }).format(val ?? 0);

const statusLabel = {
    draft: { text: 'Draft', cls: 'bg-gray-100 text-gray-600' },
    sent: { text: 'Sent', cls: 'bg-blue-100 text-blue-700' },
    viewed: { text: 'Viewed', cls: 'bg-indigo-100 text-indigo-700' },
    paid: { text: 'Paid', cls: 'bg-green-100 text-green-700' },
    overdue: { text: 'Overdue', cls: 'bg-red-100 text-red-700' },
    cancelled: { text: 'Cancelled', cls: 'bg-gray-100 text-gray-500' },
};

onMounted(() => {
    if (!chartCanvas.value || !props.revenueByMonth?.length) return;

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: props.revenueByMonth.map((m) => m.label),
            datasets: [
                {
                    label: `รายได้ (${props.currency})`,
                    data: props.revenueByMonth.map((m) => m.total),
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ฿${fmt(ctx.raw)}`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => `฿${fmtCompact(v)}`,
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
            },
        },
    });
});

onUnmounted(() => {
    chartInstance?.destroy();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- ── KPI cards ──────────────────────────────────────────── -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

                    <!-- Total Revenue -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 col-span-2 md:col-span-1">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">รายได้รวม (Paid)</span>
                            <BanknotesIcon class="w-5 h-5 text-green-500" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900">฿{{ fmtCompact(stats.total_revenue) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ fmt(stats.total_revenue) }} {{ currency }}</p>
                    </div>

                    <!-- Outstanding -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">ค้างชำระ</span>
                            <ClockIcon class="w-5 h-5 text-amber-500" />
                        </div>
                        <p class="text-2xl font-bold text-amber-600">฿{{ fmtCompact(stats.outstanding) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ fmt(stats.outstanding) }} {{ currency }}</p>
                    </div>

                    <!-- Drafts -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Drafts</span>
                            <DocumentTextIcon class="w-5 h-5 text-gray-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-700">{{ stats.draft_count }}</p>
                        <p class="mt-1 text-xs text-gray-400">invoice ร่าง</p>
                    </div>

                    <!-- Overdue -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">เกินกำหนด</span>
                            <ExclamationTriangleIcon class="w-5 h-5 text-red-400" />
                        </div>
                        <p class="text-2xl font-bold" :class="stats.overdue_count > 0 ? 'text-red-600' : 'text-gray-700'">
                            {{ stats.overdue_count }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">invoice เกินกำหนด</p>
                    </div>

                    <!-- Avg Days to Payment -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">เฉลี่ยรับเงิน</span>
                            <ChartBarIcon class="w-5 h-5 text-indigo-400" />
                        </div>
                        <p class="text-2xl font-bold text-indigo-600">
                            {{ stats.avg_days_to_payment !== null ? stats.avg_days_to_payment : '—' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">วันเฉลี่ย (issue → paid)</p>
                    </div>
                </div>

                <!-- ── Revenue Chart + Top Clients ─────────────────────────── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Revenue by Month chart -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">รายได้รายเดือน (6 เดือนล่าสุด)</h3>
                        <div class="h-56">
                            <canvas ref="chartCanvas" />
                        </div>
                        <div v-if="!stats.total_revenue" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <p class="text-sm text-gray-400">ยังไม่มีข้อมูลรายได้</p>
                        </div>
                    </div>

                    <!-- Top clients -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">Top Clients (รายได้)</h3>
                            <UserGroupIcon class="w-4 h-4 text-gray-400" />
                        </div>

                        <div v-if="topClients.length === 0" class="text-sm text-gray-400 text-center py-8">
                            ยังไม่มีข้อมูล
                        </div>

                        <ul v-else class="space-y-3">
                            <li
                                v-for="(client, i) in topClients"
                                :key="i"
                                class="flex items-center gap-3"
                            >
                                <span class="shrink-0 w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ client.name }}</p>
                                    <p class="text-xs text-gray-400">{{ client.invoice_count }} invoices</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-gray-900">฿{{ fmtCompact(client.total_revenue) }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- ── Recent Invoices ─────────────────────────────────────── -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Invoice ล่าสุด</h3>
                        <Link :href="route('invoices.index')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            ดูทั้งหมด →
                        </Link>
                    </div>

                    <div v-if="recentInvoices.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                        ยังไม่มี invoice —
                        <Link :href="route('invoices.create')" class="text-indigo-600 hover:underline">สร้างใบแรก</Link>
                    </div>

                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3 text-left">เลขที่</th>
                                <th class="px-6 py-3 text-left">ลูกค้า</th>
                                <th class="px-6 py-3 text-left">วันที่</th>
                                <th class="px-6 py-3 text-right">ยอด</th>
                                <th class="px-6 py-3 text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="invoice in recentInvoices"
                                :key="invoice.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-3 font-mono text-indigo-600 font-medium">
                                    {{ invoice.invoice_number }}
                                </td>
                                <td class="px-6 py-3 text-gray-700 truncate max-w-[180px]">{{ invoice.client_name }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ invoice.issue_date }}</td>
                                <td class="px-6 py-3 text-right font-medium text-gray-900">
                                    ฿{{ fmt(invoice.total) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="statusLabel[invoice.status]?.cls ?? 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ statusLabel[invoice.status]?.text ?? invoice.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Quick actions ──────────────────────────────────────── -->
                <div class="flex gap-3 justify-end">
                    <Link
                        :href="route('invoices.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                    >
                        + สร้าง Invoice ใหม่
                    </Link>
                    <Link
                        :href="route('invoices.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        ดู Invoice ทั้งหมด
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
