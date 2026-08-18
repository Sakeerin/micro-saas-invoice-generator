<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoController extends Controller
{
    public function create()
    {
        $demo = DB::transaction(function () {
            $email = 'demo_' . Str::random(8) . '@demo.invoiceapp.co';

            $user = User::create([
                'name'              => 'Demo User',
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);

            $company = Company::create([
                'name'               => 'บริษัท สาธิต จำกัด',
                'name_en'            => 'Demo Company Ltd.',
                'tax_id'             => '0105563000001',
                'address'            => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
                'phone'              => '02-000-0000',
                'email'              => $email,
                'invoice_prefix'     => 'DEMO',
                'invoice_next_number' => 7,
                'default_currency'   => 'THB',
            ]);

            $user->companies()->attach($company->id, ['role' => 'owner']);

            // Clients
            $clients = collect([
                [
                    'name'    => 'บริษัท ลูกค้าหนึ่ง จำกัด',
                    'name_en' => 'Client One Co., Ltd.',
                    'tax_id'  => '0105563000010',
                    'email'   => 'client1@example.com',
                    'phone'   => '02-111-1111',
                    'address' => '10 ถนนสีลม กรุงเทพฯ 10500',
                    'contact_name' => 'คุณสมชาย ใจดี',
                ],
                [
                    'name'    => 'บริษัท เทคโนโลยีดี จำกัด',
                    'name_en' => 'GoodTech Co., Ltd.',
                    'tax_id'  => '0105563000020',
                    'email'   => 'hello@goodtech.example.com',
                    'phone'   => '02-222-2222',
                    'address' => '99 ถนนพระราม 9 กรุงเทพฯ 10310',
                    'contact_name' => 'คุณสมหญิง รักงาน',
                ],
                [
                    'name'    => 'นาย ฟรีแลนซ์ เดี่ยว',
                    'name_en' => 'Solo Freelancer',
                    'tax_id'  => null,
                    'email'   => 'solo@example.com',
                    'phone'   => '08-333-3333',
                    'address' => '5/10 บ้านพักอาศัย นนทบุรี 11000',
                    'contact_name' => 'นาย ฟรีแลนซ์ เดี่ยว',
                ],
            ])->map(fn($c) => Client::create(array_merge($c, ['company_id' => $company->id])));

            // Products
            collect([
                ['name' => 'พัฒนาเว็บไซต์', 'name_en' => 'Website Development', 'price' => 50000, 'unit' => 'งาน'],
                ['name' => 'ออกแบบ UI/UX',  'name_en' => 'UI/UX Design',         'price' => 15000, 'unit' => 'งาน'],
                ['name' => 'SEO รายเดือน',  'name_en' => 'Monthly SEO',           'price' => 8000,  'unit' => 'เดือน'],
                ['name' => 'Content Writing', 'name_en' => 'Content Writing',      'price' => 3000,  'unit' => 'บทความ'],
                ['name' => 'Mobile App',     'name_en' => 'Mobile App Development','price' => 120000,'unit' => 'งาน'],
            ])->each(fn($p) => Product::create(array_merge($p, ['company_id' => $company->id])));

            // Invoices
            $invoiceData = [
                [
                    'client'     => $clients[0],
                    'number'     => 'DEMO-2026-0001',
                    'status'     => 'paid',
                    'issue_date' => now()->subDays(60)->toDateString(),
                    'due_date'   => now()->subDays(45)->toDateString(),
                    'paid_at'    => now()->subDays(43),
                    'items' => [
                        ['name' => 'พัฒนาเว็บไซต์', 'name_en' => 'Website Development', 'qty' => 1, 'price' => 50000],
                        ['name' => 'ออกแบบ UI/UX',  'name_en' => 'UI/UX Design',         'qty' => 1, 'price' => 15000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 3,
                ],
                [
                    'client'     => $clients[1],
                    'number'     => 'DEMO-2026-0002',
                    'status'     => 'paid',
                    'issue_date' => now()->subDays(45)->toDateString(),
                    'due_date'   => now()->subDays(30)->toDateString(),
                    'paid_at'    => now()->subDays(28),
                    'items' => [
                        ['name' => 'Mobile App', 'name_en' => 'Mobile App Development', 'qty' => 1, 'price' => 120000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 3,
                ],
                [
                    'client'     => $clients[0],
                    'number'     => 'DEMO-2026-0003',
                    'status'     => 'sent',
                    'issue_date' => now()->subDays(20)->toDateString(),
                    'due_date'   => now()->addDays(10)->toDateString(),
                    'paid_at'    => null,
                    'items' => [
                        ['name' => 'SEO รายเดือน', 'name_en' => 'Monthly SEO', 'qty' => 3, 'price' => 8000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 3,
                ],
                [
                    'client'     => $clients[2],
                    'number'     => 'DEMO-2026-0004',
                    'status'     => 'overdue',
                    'issue_date' => now()->subDays(40)->toDateString(),
                    'due_date'   => now()->subDays(10)->toDateString(),
                    'paid_at'    => null,
                    'items' => [
                        ['name' => 'Content Writing', 'name_en' => 'Content Writing', 'qty' => 5, 'price' => 3000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 1,
                ],
                [
                    'client'     => $clients[1],
                    'number'     => 'DEMO-2026-0005',
                    'status'     => 'draft',
                    'issue_date' => now()->subDays(2)->toDateString(),
                    'due_date'   => now()->addDays(28)->toDateString(),
                    'paid_at'    => null,
                    'items' => [
                        ['name' => 'พัฒนาเว็บไซต์', 'name_en' => 'Website Development', 'qty' => 1, 'price' => 80000],
                        ['name' => 'ออกแบบ UI/UX',  'name_en' => 'UI/UX Design',         'qty' => 1, 'price' => 20000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 3,
                ],
                [
                    'client'     => $clients[0],
                    'number'     => 'DEMO-2026-0006',
                    'status'     => 'viewed',
                    'issue_date' => now()->subDays(5)->toDateString(),
                    'due_date'   => now()->addDays(25)->toDateString(),
                    'paid_at'    => null,
                    'items' => [
                        ['name' => 'SEO รายเดือน', 'name_en' => 'Monthly SEO', 'qty' => 1, 'price' => 8000],
                        ['name' => 'Content Writing', 'name_en' => 'Content Writing', 'qty' => 2, 'price' => 3000],
                    ],
                    'vat_rate' => 7, 'wht_rate' => 3,
                ],
            ];

            foreach ($invoiceData as $inv) {
                $subtotal = collect($inv['items'])->sum(fn($i) => $i['qty'] * $i['price']);
                $vatAmount = round($subtotal * $inv['vat_rate'] / 100, 2);
                $whtAmount = round($subtotal * $inv['wht_rate'] / 100, 2);
                $total     = $subtotal + $vatAmount - $whtAmount;

                $invoice = Invoice::create([
                    'company_id'     => $company->id,
                    'client_id'      => $inv['client']->id,
                    'invoice_number' => $inv['number'],
                    'status'         => $inv['status'],
                    'issue_date'     => $inv['issue_date'],
                    'due_date'       => $inv['due_date'],
                    'paid_at'        => $inv['paid_at'],
                    'vat_rate'       => $inv['vat_rate'],
                    'wht_rate'       => $inv['wht_rate'],
                    'subtotal'       => $subtotal,
                    'vat_amount'     => $vatAmount,
                    'wht_amount'     => $whtAmount,
                    'total'          => $total,
                    'currency'       => 'THB',
                    'template'       => 'modern',
                    'share_token'    => Str::random(32),
                    'notes'          => null,
                ]);

                foreach ($inv['items'] as $order => $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'name'       => $item['name'],
                        'name_en'    => $item['name_en'],
                        'quantity'   => $item['qty'],
                        'unit_price' => $item['price'],
                        'line_total' => $item['qty'] * $item['price'],
                        'sort_order' => $order,
                    ]);
                }
            }

            return $user;
        });

        Auth::login($demo);

        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'ยินดีต้อนรับสู่ Demo Account! สำรวจระบบได้เลย');
    }
}