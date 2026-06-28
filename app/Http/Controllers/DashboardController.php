<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $company = auth()->user()->companies()->first();
        $companyId = $company->id;
        $currency = $company->default_currency ?? 'THB';

        // ── KPI cards ──────────────────────────────────────────────────────────
        $totalRevenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->sum('total');

        $outstanding = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'viewed', 'overdue'])
            ->sum('total');

        $draftCount = Invoice::where('company_id', $companyId)
            ->where('status', 'draft')
            ->count();

        $overdueCount = Invoice::where('company_id', $companyId)
            ->where('status', 'overdue')
            ->count();

        $totalInvoices = Invoice::where('company_id', $companyId)->count();

        // ── Revenue by month (last 6 months) ────────────────────────────────────
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

        $rawMonthly = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('paid_at', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(total) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($row) => "{$row->year}-{$row->month}");

        // Build a full 6-month series (fill gaps with 0)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->year.'-'.$date->month;
            $revenueByMonth[] = [
                'label' => $date->locale('th')->isoFormat('MMM YY'),
                'total' => isset($rawMonthly[$key]) ? (float) $rawMonthly[$key]->total : 0,
            ];
        }

        // ── Top 5 clients by revenue ────────────────────────────────────────────
        $topClients = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->select('client_name', 'client_name_en', 'client_id')
            ->selectRaw('SUM(total) as total_revenue, COUNT(*) as invoice_count')
            ->groupBy('client_id', 'client_name', 'client_name_en')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->client_name,
                'name_en' => $row->client_name_en,
                'total_revenue' => (float) $row->total_revenue,
                'invoice_count' => (int) $row->invoice_count,
            ]);

        // ── Average days from issue_date to paid_at ─────────────────────────────
        $avgDays = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->selectRaw('AVG(DATEDIFF(paid_at, issue_date)) as avg_days')
            ->value('avg_days');

        // ── Recent invoices (last 5) ─────────────────────────────────────────────
        $recentInvoices = Invoice::where('company_id', $companyId)
            ->with('client:id,name,name_en')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'invoice_number', 'client_name', 'total', 'status', 'issue_date', 'currency']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_revenue' => (float) $totalRevenue,
                'outstanding' => (float) $outstanding,
                'draft_count' => $draftCount,
                'overdue_count' => $overdueCount,
                'total_invoices' => $totalInvoices,
                'avg_days_to_payment' => $avgDays !== null ? round((float) $avgDays, 1) : null,
            ],
            'revenue_by_month' => $revenueByMonth,
            'top_clients' => $topClients,
            'recent_invoices' => $recentInvoices,
            'currency' => $currency,
        ]);
    }
}
