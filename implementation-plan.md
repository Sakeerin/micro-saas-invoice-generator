# แผนพัฒนา Micro-SaaS Invoice Generator (Thai-first)

> **สถานะ:** Planning Phase  
> **เวอร์ชัน:** 1.0  
> **อัปเดตล่าสุด:** เมษายน 2026  
> **Stack:** Laravel 11 · Vue 3 · Inertia.js · Browsershot · Claude API · Omise

---

## สารบัญ

1. [ภาพรวมโปรเจค](#1-ภาพรวมโปรเจค)
2. [Tech Stack](#2-tech-stack)
3. [สถาปัตยกรรมระบบ](#3-สถาปัตยกรรมระบบ)
4. [Database Schema](#4-database-schema)
5. [Tax Engine Design](#5-tax-engine-design)
6. [PDF Engine Design](#6-pdf-engine-design)
7. [Timeline แบบละเอียด (10 สัปดาห์)](#7-timeline-แบบละเอียด-10-สัปดาห์)
8. [Monetization & Pricing](#8-monetization--pricing)
9. [Go-to-Market Strategy](#9-go-to-market-strategy)
10. [Risk Assessment](#10-risk-assessment)
11. [Definition of Done](#11-definition-of-done)

---

## 1. ภาพรวมโปรเจค

### Vision
Micro-SaaS สำหรับ freelancer และ SME ไทยที่ออก invoice ได้ถูกต้องตามกฎหมายภาษีไทย (VAT 7% + WHT) ใน 2 ภาษา โดยไม่ต้องง้อ Excel template ที่คำนวณผิดบ่อย

### ปัญหาที่แก้

| ปัญหา | ผลกระทบ |
|---|---|
| คำนวณ WHT ผิดบ่อย | เสียภาษีผิด หรือถูกตรวจสอบจาก สรรพากร |
| Excel template ดูไม่ professional | client ต่างชาติมองภาพลักษณ์ไม่ดี |
| ไม่รู้ว่า client เปิดดู invoice หรือยัง | ติดตามงานยาก ไม่รู้จะ follow up ตอนไหน |
| กรอก ชื่อ/ที่อยู่ บริษัทซ้ำทุกครั้ง | เสียเวลา error-prone |
| ไม่มีเลข running ที่ถูก format | ผิด format ที่สรรพากรกำหนด |

### จุดต่างจากคู่แข่ง

| Feature | Excel | Zoho Invoice | **แพลตฟอร์มนี้** |
|---|---|---|---|
| VAT 7% อัตโนมัติ | manual | ✓ | ✓ |
| WHT (1%, 3%, 5%) | manual | ✗ | **✓** |
| ภาษาไทย + อังกฤษ ใบเดียว | ยาก | ✗ | **✓** |
| เลขภาษี autofill จาก DBD | ✗ | ✗ | **✓** |
| Share link + viewed tracking | ✗ | ✗ | **✓** |
| AI แนะนำ line items | ✗ | ✗ | **✓** |
| ราคา | ฟรี | ฿400+/เดือน | **฿199/เดือน** |

### Target Users
- **Freelancer:** dev, designer, content creator ที่ออก invoice ให้ลูกค้า 2–20 ใบ/เดือน
- **SME เล็ก:** ทีม 1–5 คน ที่ยังใช้ Excel และต้องการ upgrade
- **Agency:** ที่ต้องออก invoice หลายใบหลาย client ต่อเดือน

---

## 2. Tech Stack

### Frontend

```
Vue 3 + Inertia.js       — SPA บน Laravel ไม่ต้องทำ REST API แยก
Tailwind CSS v3          — utility-first styling
Pinia                    — state: invoice draft, line items
Vue Draggable (SortableJS) — drag-to-reorder line items
Vue Final Modal          — modal: client picker, product picker
VueUse                   — composables: useLocalStorage, useDebounce
```

### Backend

```
Laravel 11               — core framework
Laravel Sanctum          — API auth (SPA mode)
Laravel Horizon          — queue monitoring (PDF generation)
Laravel Scout            — search clients/products
```

### PDF Engine

```
Browsershot (spatie/browsershot) — HTML → PDF via Puppeteer
Puppeteer (Node.js)      — headless Chrome สำหรับ render
Cloudflare R2            — PDF storage (ไม่ generate ซ้ำถ้าไม่มีการแก้ไข)
```

> **ทำไม Browsershot แทน DomPDF:**
> DomPDF ไม่รองรับ Thai font ได้ดีพอ, CSS Grid/Flexbox ไม่ทำงาน
> Browsershot ใช้ Puppeteer render HTML จริง → Thai font สมบูรณ์, layout pixel-perfect

### AI Layer

```
Claude claude-sonnet-4-6          — autofill line items จากประวัติ, สรุป invoice
Vercel AI SDK            — streaming suggestions
```

### Database

```
MySQL 8                  — primary database
Redis                    — queue, cache (PDF hash), session
```

### External APIs

```
กรมพัฒนาธุรกิจการค้า (DBD) — ค้นหาข้อมูลบริษัทจากเลขนิติบุคคล 13 หลัก
Omise                    — subscription billing (Pro / Business plan)
Resend / SendGrid        — transactional email (invoice email, tracking pixel)
```

### DevOps

```
Docker + Docker Compose  — local dev (PHP, MySQL, Redis, Node.js สำหรับ Puppeteer)
Azure DevOps CI/CD       — lint → test → build → deploy
Laravel Forge            — server provisioning
Cloudflare               — CDN, WAF, R2 storage
Sentry                   — error tracking
```

---

## 3. สถาปัตยกรรมระบบ

### Directory Structure

```
invoice-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── InvoiceController.php
│   │   │   ├── ClientController.php
│   │   │   ├── ProductController.php
│   │   │   ├── PdfController.php
│   │   │   └── WebhookController.php (Omise)
│   │   └── Requests/
│   │       └── StoreInvoiceRequest.php
│   ├── Services/
│   │   ├── InvoicePdfService.php    ← Browsershot PDF generator
│   │   ├── TaxEngineService.php     ← VAT + WHT calculation
│   │   ├── DbtLookupService.php     ← DBD API lookup
│   │   └── AiAutofillService.php   ← Claude line item suggestions
│   ├── Jobs/
│   │   └── GenerateInvoicePdfJob.php
│   ├── Models/
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Client.php
│   │   └── Product.php
│   └── Events/
│       └── InvoiceViewed.php
├── resources/
│   ├── views/
│   │   └── pdf/
│   │       ├── templates/
│   │       │   ├── modern.blade.php
│   │       │   ├── classic.blade.php
│   │       │   ├── minimal.blade.php
│   │       │   ├── corporate.blade.php
│   │       │   └── creative.blade.php
│   │       └── layout.blade.php
│   └── js/
│       ├── Pages/
│       │   ├── Invoices/
│       │   │   ├── Index.vue
│       │   │   ├── Create.vue    ← invoice builder
│       │   │   ├── Edit.vue
│       │   │   └── Show.vue     ← public share view
│       │   ├── Clients/
│       │   └── Dashboard.vue
│       └── Components/
│           ├── InvoiceBuilder/
│           │   ├── LineItemsEditor.vue
│           │   ├── TaxSummary.vue
│           │   └── AiSuggestPanel.vue
│           └── Shared/
```

### PDF Generation Flow

```
User คลิก "Download PDF"
  │
  ▼
PdfController@generate
  ├── ตรวจ R2 cache: มี PDF hash ตรงกับ invoice updated_at ไหม?
  │   ├── ใช่ → redirect ไป R2 presigned URL (ทันที)
  │   └── ไม่ → dispatch GenerateInvoicePdfJob
  │
  ▼
GenerateInvoicePdfJob (queue: pdf)
  ├── render Blade template → HTML string
  ├── InvoicePdfService::generate(html) → Browsershot
  │     ├── Puppeteer launch headless Chrome
  │     ├── load HTML + inject Thai fonts
  │     ├── wait for fonts ready
  │     └── print to PDF (A4, margins)
  ├── upload PDF → Cloudflare R2
  ├── บันทึก pdf_url + pdf_hash ใน invoices table
  └── broadcast InvoicePdfReady → Vue frontend (Pusher)

Vue frontend รับ event → แสดง download link
```

### Invoice Share Link Flow

```
Freelancer ส่ง share link ให้ลูกค้า
  │
  GET /invoice/share/{token}
  │
  ├── ดึง invoice จาก token (ไม่ต้อง login)
  ├── log view event (IP, user agent, timestamp)
  ├── ถ้าครั้งแรก → update invoice.first_viewed_at
  └── render public invoice view (read-only)

Email pixel tracking (สำหรับ email delivery):
  GET /tracking/pixel/{token}.gif (1x1 transparent GIF)
  └── log open event → update invoice.email_opened_at
```

---

## 4. Database Schema

```sql
-- บริษัท / workspace ของ user
CREATE TABLE companies (
  id              UUID PRIMARY KEY DEFAULT (UUID()),
  user_id         BIGINT UNSIGNED NOT NULL,
  name            TEXT NOT NULL,
  name_en         TEXT,                        -- ชื่อภาษาอังกฤษ
  address         TEXT,
  address_en      TEXT,
  tax_id          CHAR(13),                    -- เลขประจำตัวผู้เสียภาษี 13 หลัก
  phone           TEXT,
  email           TEXT,
  logo_url        TEXT,
  brand_color     CHAR(7) DEFAULT '#1a56db',   -- hex color
  bank_name       TEXT,
  bank_account    TEXT,
  bank_account_name TEXT,
  invoice_prefix  TEXT DEFAULT 'INV',          -- prefix สำหรับเลข invoice
  invoice_next_number INT DEFAULT 1,
  default_vat_rate DECIMAL(5,2) DEFAULT 7.00,
  default_currency CHAR(3) DEFAULT 'THB',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ลูกค้า
CREATE TABLE clients (
  id              UUID PRIMARY KEY DEFAULT (UUID()),
  company_id      UUID NOT NULL,
  name            TEXT NOT NULL,               -- ชื่อภาษาไทย
  name_en         TEXT,
  address         TEXT,
  address_en      TEXT,
  tax_id          CHAR(13),
  contact_name    TEXT,
  contact_email   TEXT,
  contact_phone   TEXT,
  default_currency CHAR(3) DEFAULT 'THB',
  notes           TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- สินค้า / บริการที่ขายบ่อย
CREATE TABLE products (
  id              UUID PRIMARY KEY DEFAULT (UUID()),
  company_id      UUID NOT NULL,
  name            TEXT NOT NULL,               -- ชื่อภาษาไทย
  name_en         TEXT,
  description     TEXT,
  unit            TEXT DEFAULT 'งาน',          -- งาน, ชิ้น, ชั่วโมง, เดือน
  unit_price      DECIMAL(15,4) NOT NULL,
  currency        CHAR(3) DEFAULT 'THB',
  default_wht_rate DECIMAL(5,2) DEFAULT 3.00,  -- WHT rate ที่ใช้บ่อย
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Invoice หลัก
CREATE TABLE invoices (
  id              UUID PRIMARY KEY DEFAULT (UUID()),
  company_id      UUID NOT NULL,
  client_id       UUID,                        -- NULL ได้ (กรอก client info manual)
  invoice_number  TEXT NOT NULL,               -- เช่น INV-2026-0042
  reference       TEXT,                        -- PO number หรือ reference ของ client
  template        TEXT DEFAULT 'modern',       -- modern|classic|minimal|corporate|creative
  language        TEXT DEFAULT 'th-en'         -- th|en|th-en (bilingual)
    CHECK (language IN ('th', 'en', 'th-en')),
  currency        CHAR(3) DEFAULT 'THB',
  exchange_rate   DECIMAL(15,6) DEFAULT 1.0,   -- กรณีออก invoice USD

  -- Client info snapshot (กรณีไม่มี client record หรือ override)
  client_name     TEXT NOT NULL,
  client_name_en  TEXT,
  client_address  TEXT,
  client_address_en TEXT,
  client_tax_id   TEXT,

  -- Dates
  issue_date      DATE NOT NULL DEFAULT (CURDATE()),
  due_date        DATE,

  -- Amounts (คำนวณจาก TaxEngine แล้ว)
  subtotal        DECIMAL(15,2) NOT NULL DEFAULT 0,   -- ก่อน VAT/WHT
  discount_type   ENUM('none','percent','amount') DEFAULT 'none',
  discount_value  DECIMAL(15,4) DEFAULT 0,
  discount_amount DECIMAL(15,2) DEFAULT 0,            -- ยอดลด (บาท)
  subtotal_after_discount DECIMAL(15,2) DEFAULT 0,
  vat_rate        DECIMAL(5,2) DEFAULT 7.00,
  vat_amount      DECIMAL(15,2) DEFAULT 0,
  wht_rate        DECIMAL(5,2) DEFAULT 0,
  wht_amount      DECIMAL(15,2) DEFAULT 0,
  total           DECIMAL(15,2) NOT NULL DEFAULT 0,   -- ยอดสุทธิที่รับ = subtotal + VAT - WHT

  -- Status
  status          ENUM('draft','sent','viewed','paid','overdue','cancelled')
                  DEFAULT 'draft',
  notes           TEXT,                        -- หมายเหตุท้ายใบ
  payment_terms   TEXT,                        -- เงื่อนไขการชำระเงิน

  -- PDF
  pdf_url         TEXT,                        -- Cloudflare R2 URL
  pdf_hash        CHAR(64),                    -- SHA256 ของ invoice data สำหรับ cache

  -- Share & Tracking
  share_token     CHAR(64) UNIQUE,             -- public share URL token
  share_expires_at TIMESTAMP,                  -- NULL = ไม่หมดอายุ
  first_viewed_at  TIMESTAMP,
  last_viewed_at   TIMESTAMP,
  view_count       INT DEFAULT 0,
  email_sent_at    TIMESTAMP,
  email_opened_at  TIMESTAMP,

  paid_at         TIMESTAMP,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY unique_invoice_number (company_id, invoice_number),
  FOREIGN KEY (company_id) REFERENCES companies(id),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);

-- Line items
CREATE TABLE invoice_items (
  id              UUID PRIMARY KEY DEFAULT (UUID()),
  invoice_id      UUID NOT NULL,
  product_id      UUID,                        -- NULL ได้ (manual entry)
  sort_order      TINYINT UNSIGNED NOT NULL,
  name            TEXT NOT NULL,               -- ชื่อรายการ (ไทย)
  name_en         TEXT,                        -- ชื่อรายการ (อังกฤษ)
  description     TEXT,
  quantity        DECIMAL(15,4) NOT NULL DEFAULT 1,
  unit            TEXT DEFAULT 'งาน',
  unit_price      DECIMAL(15,4) NOT NULL,
  discount_percent DECIMAL(5,2) DEFAULT 0,     -- discount ต่อ line
  line_total      DECIMAL(15,2) NOT NULL,       -- quantity × unit_price × (1 - discount%)
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ประวัติ status changes
CREATE TABLE invoice_activities (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id  UUID NOT NULL,
  type        TEXT NOT NULL,   -- created|sent|viewed|paid|cancelled|pdf_generated
  meta        JSON,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Subscription plan
CREATE TABLE subscriptions (
  id                UUID PRIMARY KEY DEFAULT (UUID()),
  user_id           BIGINT UNSIGNED NOT NULL,
  plan              ENUM('free','pro','business') DEFAULT 'free',
  omise_customer_id TEXT,
  omise_subscription_id TEXT,
  current_period_end TIMESTAMP,
  invoice_count_this_month INT DEFAULT 0,      -- สำหรับ enforce free plan limit
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 5. Tax Engine Design

### TaxEngineService

```php
// app/Services/TaxEngineService.php

class TaxEngineService
{
    /**
     * คำนวณ tax ทั้งหมดจาก line items + config
     * Return immutable result object
     */
    public function calculate(TaxCalculationInput $input): TaxResult
    {
        // 1. คำนวณ subtotal จาก line items
        $subtotal = collect($input->items)->sum(function ($item) {
            $lineTotal = $item->quantity * $item->unitPrice;
            if ($item->discountPercent > 0) {
                $lineTotal *= (1 - $item->discountPercent / 100);
            }
            return round($lineTotal, 2);
        });

        // 2. คำนวณ discount ระดับ invoice
        $discountAmount = match ($input->discountType) {
            'percent' => round($subtotal * $input->discountValue / 100, 2),
            'amount'  => min($input->discountValue, $subtotal),
            default   => 0,
        };

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 3. VAT คำนวณบน subtotal หลัง discount
        //    กรณีราคารวม VAT แล้ว: vat = subtotal × rate / (100 + rate)
        //    กรณีราคายังไม่รวม VAT: vat = subtotal × rate / 100
        $vatAmount = $input->vatIncluded
            ? round($subtotalAfterDiscount * $input->vatRate / (100 + $input->vatRate), 2)
            : round($subtotalAfterDiscount * $input->vatRate / 100, 2);

        // 4. WHT คำนวณบน subtotal (ก่อน VAT ตามกฎหมายภาษีไทย)
        $whtAmount = round($subtotalAfterDiscount * $input->whtRate / 100, 2);

        // 5. Total = subtotal + VAT - WHT
        //    (ลูกค้าจ่ายให้เรา = ยอดรวม VAT แล้วหัก WHT ที่เขา remit แทนเรา)
        $total = $subtotalAfterDiscount + $vatAmount - $whtAmount;

        return new TaxResult(
            subtotal:               $subtotal,
            discountAmount:         $discountAmount,
            subtotalAfterDiscount:  $subtotalAfterDiscount,
            vatRate:                $input->vatRate,
            vatAmount:              $vatAmount,
            whtRate:                $input->whtRate,
            whtAmount:              $whtAmount,
            total:                  round($total, 2),
        );
    }
}
```

### ตัวอย่างการคำนวณ

```
Scenario: Freelancer ออก invoice ค่าพัฒนาเว็บ

Line items:
  - Website Development   1 งาน × ฿50,000 = ฿50,000
  - Logo Design           1 งาน × ฿15,000 = ฿15,000

Subtotal:                 ฿65,000
Discount (0%):             ฿0
Subtotal after discount:  ฿65,000
VAT 7%:                   ฿4,550   (65,000 × 7%)
WHT 3%:                   ฿1,950   (65,000 × 3%)
─────────────────────────────────────
Total ที่รับจริง:          ฿67,600  (65,000 + 4,550 − 1,950)

หมายเหตุ:
- Client โอน ฿67,600 ให้ freelancer
- Client นำ WHT ฿1,950 ไปยื่น ภ.ง.ด. 3 แทน freelancer
- Freelancer รับจริง ฿67,600 (ไม่ใช่ ฿65,000)
```

### WHT Rates ที่รองรับ

```php
// config/tax.php

return [
    'vat_rates' => [7.0],          // ปัจจุบัน 7% (อาจเปลี่ยนได้)

    'wht_rates' => [
        ['rate' => 0,   'label' => 'ไม่มี WHT'],
        ['rate' => 1.0, 'label' => '1% - ค่าเช่า, ดอกเบี้ย'],
        ['rate' => 3.0, 'label' => '3% - ค่าบริการ, ค่าจ้าง (นิติบุคคล)'],
        ['rate' => 5.0, 'label' => '5% - ค่าโฆษณา, ค่านายหน้า'],
    ],

    'wht_rates_individual' => [
        ['rate' => 0,   'label' => 'ไม่มี WHT'],
        ['rate' => 3.0, 'label' => '3% - ค่าบริการ (บุคคลธรรมดา)'],
        ['rate' => 5.0, 'label' => '5% - ค่าเช่า'],
    ],
];
```

---

## 6. PDF Engine Design

### InvoicePdfService

```php
// app/Services/InvoicePdfService.php

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    private const THAI_FONTS = [
        'Sarabun'       => storage_path('fonts/Sarabun-Regular.ttf'),
        'Sarabun-Bold'  => storage_path('fonts/Sarabun-Bold.ttf'),
        'NotoSansThai'  => storage_path('fonts/NotoSansThai-Regular.ttf'),
    ];

    public function generate(Invoice $invoice): string
    {
        // 1. Render HTML จาก Blade template
        $html = $this->renderTemplate($invoice);

        // 2. Generate PDF via Puppeteer
        $pdf = Browsershot::html($html)
            ->setNodeBinary('/usr/local/bin/node')
            ->setNpmBinary('/usr/local/bin/npm')
            ->noSandbox()                          // สำหรับ Docker environment
            ->waitUntilNetworkIdle()               // รอ font load
            ->margins(15, 15, 15, 15)             // mm: top, right, bottom, left
            ->format('A4')
            ->showBackground()                     // แสดง background color ด้วย
            ->pdf();

        // 3. Upload ไป Cloudflare R2
        $filename = "invoices/{$invoice->company_id}/{$invoice->id}.pdf";
        Storage::disk('r2')->put($filename, $pdf, [
            'ContentType' => 'application/pdf',
            'CacheControl' => 'max-age=3600',
        ]);

        // 4. บันทึก URL + hash กลับไป invoice
        $pdfUrl  = Storage::disk('r2')->temporaryUrl($filename, now()->addHours(24));
        $pdfHash = hash('sha256', $invoice->updated_at->timestamp . $invoice->id);

        $invoice->update([
            'pdf_url'  => $pdfUrl,
            'pdf_hash' => $pdfHash,
        ]);

        return $pdfUrl;
    }

    private function renderTemplate(Invoice $invoice): string
    {
        // inject font CSS สำหรับ Puppeteer
        $fontCss = $this->buildFontCss();

        return view("pdf.templates.{$invoice->template}", [
            'invoice' => $invoice->load(['items', 'client', 'company']),
            'fontCss' => $fontCss,
        ])->render();
    }

    private function buildFontCss(): string
    {
        $css = '';
        foreach (self::THAI_FONTS as $family => $path) {
            $base64 = base64_encode(file_get_contents($path));
            $css .= "@font-face {
                font-family: '{$family}';
                src: url('data:font/truetype;base64,{$base64}') format('truetype');
            }\n";
        }
        return $css;
    }

    /**
     * ตรวจว่า PDF cache ยังใช้ได้ไหม
     */
    public function isCacheValid(Invoice $invoice): bool
    {
        if (!$invoice->pdf_hash || !$invoice->pdf_url) return false;

        $expectedHash = hash('sha256', $invoice->updated_at->timestamp . $invoice->id);
        return hash_equals($expectedHash, $invoice->pdf_hash);
    }
}
```

### Blade PDF Template (modern)

```html
{{-- resources/views/pdf/templates/modern.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<style>
{!! $fontCss !!}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Sarabun', 'NotoSansThai', sans-serif;
  font-size: 11pt;
  color: #1a1a1a;
  line-height: 1.6;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 2px solid {{ $invoice->company->brand_color ?? '#1a56db' }};
}

.company-logo img {
  max-height: 64px;
  max-width: 180px;
}

.invoice-title {
  font-size: 24pt;
  font-weight: bold;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
  text-align: right;
}

.invoice-meta {
  text-align: right;
  font-size: 10pt;
  color: #555;
  margin-top: 4px;
}

.parties {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  margin-bottom: 28px;
}

.party-label {
  font-size: 9pt;
  font-weight: bold;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.party-name {
  font-size: 13pt;
  font-weight: bold;
  margin-bottom: 4px;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 24px;
}

table.items thead th {
  background: {{ $invoice->company->brand_color ?? '#1a56db' }};
  color: white;
  padding: 8px 10px;
  font-size: 9pt;
  font-weight: bold;
  text-align: left;
}

table.items thead th:last-child,
table.items thead th:nth-last-child(2),
table.items thead th:nth-last-child(3) {
  text-align: right;
}

table.items tbody tr:nth-child(even) {
  background: #f8f9fa;
}

table.items tbody td {
  padding: 8px 10px;
  font-size: 10pt;
  vertical-align: top;
  border-bottom: 0.5px solid #e5e7eb;
}

table.items tbody td:last-child,
table.items tbody td:nth-last-child(2),
table.items tbody td:nth-last-child(3) {
  text-align: right;
}

.summary {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 28px;
}

.summary-table {
  min-width: 260px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
  border-bottom: 0.5px solid #e5e7eb;
  font-size: 10pt;
}

.summary-row.total {
  font-size: 14pt;
  font-weight: bold;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
  border-top: 2px solid {{ $invoice->company->brand_color ?? '#1a56db' }};
  border-bottom: none;
  padding-top: 8px;
  margin-top: 4px;
}

.footer {
  margin-top: 32px;
  padding-top: 16px;
  border-top: 0.5px solid #e5e7eb;
  font-size: 9pt;
  color: #888;
}

.bank-info {
  background: #f8f9fa;
  border-radius: 6px;
  padding: 12px 16px;
  margin-bottom: 16px;
  font-size: 10pt;
}

.bank-info strong {
  color: #333;
}
</style>
</head>
<body>

<div class="header">
  <div class="company-logo">
    @if($invoice->company->logo_url)
      <img src="{{ $invoice->company->logo_url }}" alt="{{ $invoice->company->name }}">
    @else
      <div class="company-name" style="font-size:16pt;font-weight:bold">
        {{ $invoice->company->name }}
      </div>
    @endif
    <div style="font-size:9pt;color:#888;margin-top:4px">
      {{ $invoice->company->address }}<br>
      @if($invoice->company->tax_id)
        เลขประจำตัวผู้เสียภาษี: {{ $invoice->company->tax_id }}
      @endif
    </div>
  </div>
  <div>
    <div class="invoice-title">
      @if($invoice->language === 'th') ใบแจ้งหนี้
      @elseif($invoice->language === 'en') INVOICE
      @else ใบแจ้งหนี้ / INVOICE
      @endif
    </div>
    <div class="invoice-meta">
      <strong>เลขที่:</strong> {{ $invoice->invoice_number }}<br>
      <strong>วันที่:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->thaiDate() }}<br>
      @if($invoice->due_date)
        <strong>ครบกำหนด:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->thaiDate() }}
      @endif
    </div>
  </div>
</div>

<div class="parties">
  <div>
    <div class="party-label">ผู้ออกใบแจ้งหนี้ / From</div>
    <div class="party-name">{{ $invoice->company->name }}</div>
    @if($invoice->company->name_en)
      <div style="color:#555">{{ $invoice->company->name_en }}</div>
    @endif
    <div style="color:#555;font-size:10pt">{{ $invoice->company->address }}</div>
    @if($invoice->company->tax_id)
      <div style="color:#555;font-size:10pt">เลขภาษี: {{ $invoice->company->tax_id }}</div>
    @endif
  </div>
  <div>
    <div class="party-label">ผู้รับใบแจ้งหนี้ / Bill To</div>
    <div class="party-name">{{ $invoice->client_name }}</div>
    @if($invoice->client_name_en)
      <div style="color:#555">{{ $invoice->client_name_en }}</div>
    @endif
    <div style="color:#555;font-size:10pt">{{ $invoice->client_address }}</div>
    @if($invoice->client_tax_id)
      <div style="color:#555;font-size:10pt">เลขภาษี: {{ $invoice->client_tax_id }}</div>
    @endif
  </div>
</div>

<table class="items">
  <thead>
    <tr>
      <th style="width:40%">รายการ / Description</th>
      <th style="width:10%">จำนวน</th>
      <th style="width:10%">หน่วย</th>
      <th style="width:15%">ราคา/หน่วย</th>
      <th style="width:10%">ส่วนลด</th>
      <th style="width:15%">รวม</th>
    </tr>
  </thead>
  <tbody>
    @foreach($invoice->items->sortBy('sort_order') as $item)
    <tr>
      <td>
        <strong>{{ $item->name }}</strong>
        @if($item->name_en)<br><span style="color:#777;font-size:9pt">{{ $item->name_en }}</span>@endif
        @if($item->description)<br><span style="color:#999;font-size:9pt">{{ $item->description }}</span>@endif
      </td>
      <td>{{ number_format($item->quantity, 2) }}</td>
      <td>{{ $item->unit }}</td>
      <td>{{ number_format($item->unit_price, 2) }}</td>
      <td>{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
      <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="summary">
  <div class="summary-table">
    <div class="summary-row">
      <span>ยอดรวม / Subtotal</span>
      <span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
    </div>
    @if($invoice->discount_amount > 0)
    <div class="summary-row">
      <span>ส่วนลด / Discount</span>
      <span style="color:#dc2626">- {{ number_format($invoice->discount_amount, 2) }}</span>
    </div>
    @endif
    @if($invoice->vat_rate > 0)
    <div class="summary-row">
      <span>VAT {{ $invoice->vat_rate }}%</span>
      <span>{{ number_format($invoice->vat_amount, 2) }}</span>
    </div>
    @endif
    @if($invoice->wht_rate > 0)
    <div class="summary-row">
      <span>หัก ณ ที่จ่าย {{ $invoice->wht_rate }}% / WHT</span>
      <span style="color:#dc2626">- {{ number_format($invoice->wht_amount, 2) }}</span>
    </div>
    @endif
    <div class="summary-row total">
      <span>ยอดสุทธิ / Total Due</span>
      <span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
    </div>
  </div>
</div>

@if($invoice->company->bank_name)
<div class="bank-info">
  <strong>ข้อมูลการชำระเงิน / Payment Details</strong><br>
  ธนาคาร: {{ $invoice->company->bank_name }} |
  เลขบัญชี: {{ $invoice->company->bank_account }} |
  ชื่อบัญชี: {{ $invoice->company->bank_account_name }}
</div>
@endif

@if($invoice->notes)
<div style="font-size:10pt;margin-bottom:16px">
  <strong>หมายเหตุ / Notes:</strong><br>
  {{ $invoice->notes }}
</div>
@endif

<div class="footer">
  <div style="display:flex;justify-content:space-between">
    <span>สร้างโดย invoice.example.com</span>
    <span>{{ $invoice->invoice_number }} · {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
  </div>
</div>

</body>
</html>
```

---

## 7. Timeline แบบละเอียด (10 สัปดาห์)

---

### Phase 1: Foundation + Auth + Company Setup (สัปดาห์ 1–2)

**เป้าหมาย:** User สมัคร ตั้งค่าบริษัท เพิ่มลูกค้าและสินค้าได้

#### สัปดาห์ 1 — Project Setup + Auth

**วันที่ 1–2: Bootstrap**
- [x] Laravel 11 project, Docker Compose (PHP, MySQL, Redis, Node.js)
- [x] Vue 3 + Inertia.js + Tailwind CSS setup
- [x] Azure DevOps pipeline: lint → PHPStan → test → deploy staging
- [x] ติดตั้ง `spatie/browsershot` + Puppeteer ใน Docker image

**วันที่ 3–4: Auth**
- [x] Email + password register/login (Laravel Breeze + Inertia)
- [x] Google OAuth (Laravel Socialite)
- [x] Email verification
- [x] `/dashboard` protected route

**วันที่ 5: Company Setup Wizard**
- [x] Onboarding wizard 3 ขั้นตอน: company info → bank info → invoice settings
- [x] Migration: `companies`, `subscriptions`
- [x] Logo upload (Cloudflare R2 via Spatie Media Library)
- [x] Brand color picker (hex input + preview)

**Deliverable สัปดาห์ 1:** สมัคร → ตั้งบริษัทได้ → dashboard พร้อม

---

#### สัปดาห์ 2 — Client & Product Database

**วันที่ 6–7: Client CRUD**
- [x] Migration: `clients`
- [x] Client list, create, edit, delete
- [x] Search (Laravel Scout + database driver ก่อน, Meilisearch ทีหลัง)
- [ ] Import client จาก CSV (optional Phase 2)

**วันที่ 8: DBD API Integration**
- [x] `DbtLookupService`: เรียก DBD API จากเลขนิติบุคคล 13 หลัก
- [x] Autofill: ชื่อบริษัท, ที่อยู่, ประเภทธุรกิจ
- [x] Graceful fallback ถ้า API ไม่ตอบ (timeout 3 วินาที)

**วันที่ 9–10: Product CRUD**
- [x] Migration: `products`
- [x] Product list, create, edit, delete
- [x] Quick-add จาก invoice builder (ไม่ต้องออกหน้า)
- [x] Default WHT rate ต่อ product

**Deliverable สัปดาห์ 2:** Client + Product database พร้อม, DBD lookup ทำงาน

---

### Phase 2: Invoice Builder + Tax Engine (สัปดาห์ 3–4)

**เป้าหมาย:** สร้าง invoice ได้ครบ ตัวเลขถูกต้อง

#### สัปดาห์ 3 — Invoice Builder UI

**วันที่ 11–12: Invoice Form Core**
- [x] Migration: `invoices`, `invoice_items`
- [x] หน้า Create Invoice: client picker, issue date, due date, currency
- [x] Invoice number auto-generate: `{PREFIX}-{YEAR}-{NNNN}` เช่น `INV-2026-0001`
- [x] Reference field (PO number)

**วันที่ 13–14: Line Items Editor**
- [x] `LineItemsEditor.vue`: add, edit, delete, reorder (Vue Draggable)
- [x] Quick-select จาก product database (modal picker)
- [x] Inline edit: name, name_en, quantity, unit, unit_price, discount_percent
- [x] Real-time line total calculation (computed)

**วันที่ 15: Discount + Notes**
- [ ] Invoice-level discount: none | percent | amount (toggle)
- [ ] Notes / payment terms textarea
- [ ] Language selector: ไทย | English | ทั้งสองภาษา
- [ ] Template selector (preview thumbnail)

**Deliverable สัปดาห์ 3:** สร้าง invoice ด้วย line items ได้ (ยังไม่ได้คำนวณ tax)

---

#### สัปดาห์ 4 — Tax Engine + Summary

**วันที่ 16–17: TaxEngineService**
- [ ] `TaxEngineService::calculate()` ตาม design ข้างบน
- [ ] Unit test ครบทุก scenario:
  - VAT only (WHT 0%)
  - WHT only (VAT 0%)
  - VAT + WHT (ทั้งคู่)
  - Discount percent + VAT + WHT
  - Discount amount + VAT + WHT
  - มูลค่า 0 บาท (edge case)
  - ตัวเลขทศนิยม (precision test)

**วันที่ 18–19: Tax Summary UI**
- [ ] `TaxSummary.vue`: แสดง subtotal, discount, VAT, WHT, total แบบ real-time
- [ ] WHT rate selector (0%, 1%, 3%, 5%)
- [ ] VAT toggle (มี/ไม่มี)
- [ ] บันทึก invoice (draft) ด้วย tax amounts ที่คำนวณแล้ว

**วันที่ 20: Invoice Management**
- [ ] Invoice list: status badge, amount, client, date
- [ ] Filter: status, date range, client
- [ ] Pagination
- [ ] Duplicate invoice (copy ทุกอย่างยกเว้นวันที่และ number)

**Deliverable สัปดาห์ 4:** สร้าง invoice ครบ คำนวณ VAT+WHT ถูกต้อง

---

### Phase 3: PDF Engine + Templates (สัปดาห์ 5–6)

**เป้าหมาย:** Download PDF pixel-perfect พร้อม Thai font

#### สัปดาห์ 5 — Browsershot Setup + Template 1

**วันที่ 21–22: Browsershot + Queue**
- [ ] `GenerateInvoicePdfJob` ส่งเข้า queue `pdf` แยก
- [ ] `InvoicePdfService::generate()` ตาม design ข้างบน
- [ ] Download Thai fonts: Sarabun-Regular, Sarabun-Bold, NotoSansThai-Regular
- [ ] inject base64 font CSS ใน HTML template
- [ ] Test: generate PDF ของ invoice จริง ดู Thai font แสดงถูกต้อง

**วันที่ 23–24: Modern Template**
- [ ] `modern.blade.php` ตาม HTML template ข้างบน
- [ ] ทดสอบ: A4 size, margins, page break ระหว่าง line items ยาว
- [ ] ทดสอบ: logo, brand color, bilingual content

**วันที่ 25: R2 Cache + Download**
- [ ] `InvoicePdfService::isCacheValid()` ตรวจ hash
- [ ] `PdfController@download`: ตรวจ cache → dispatch job ถ้าไม่มี
- [ ] Pusher broadcast `InvoicePdfReady` → Vue แสดง download link อัตโนมัติ
- [ ] Presigned URL หมดอายุ 24 ชั่วโมง

**Deliverable สัปดาห์ 5:** Download PDF ได้ Thai font สมบูรณ์

---

#### สัปดาห์ 6 — อีก 4 Templates + Preview

**วันที่ 26–27: Classic + Minimal Templates**
- [ ] `classic.blade.php`: เส้นตาราง, header สีเข้ม, ดูเป็นทางการ
- [ ] `minimal.blade.php`: ไม่มี border, whitespace เยอะ, สไตล์ modern

**วันที่ 28–29: Corporate + Creative Templates**
- [ ] `corporate.blade.php`: สีขาวดำ, header แถบกว้าง, เหมาะองค์กร
- [ ] `creative.blade.php`: accent color เข้ม, layout สองคอลัมน์

**วันที่ 30: Live Preview**
- [ ] Invoice builder แสดง preview ด้านขวา (iframe render HTML เดียวกับ PDF)
- [ ] Preview update เมื่อ line items เปลี่ยน (debounce 500ms)
- [ ] Template switcher: คลิกเปลี่ยน template เห็นผลทันที

**Deliverable สัปดาห์ 6:** 5 templates พร้อม live preview ใน builder

---

### Phase 4: Share Link + Status Tracking + AI (สัปดาห์ 7–8)

**เป้าหมาย:** ส่ง invoice ให้ลูกค้าได้ รู้ว่าเปิดอ่านแล้วหรือยัง

#### สัปดาห์ 7 — Share Link + Email + Tracking

**วันที่ 31–32: Share Link**
- [ ] Generate `share_token` (random 64-char) เมื่อ invoice ถูกส่ง
- [ ] Public route: `GET /invoice/share/{token}` (ไม่ต้อง login)
- [ ] Public view: read-only invoice หน้าเดิม + download PDF button
- [ ] Copy link button ใน invoice detail

**วันที่ 33–34: Email Delivery**
- [ ] Email template: subject "ใบแจ้งหนี้ {number} จาก {company}"
- [ ] Email body: invoice summary + link + download button
- [ ] Tracking pixel: `<img src="/tracking/pixel/{token}.gif" width="1" height="1">`
- [ ] `GET /tracking/pixel/{token}.gif` → log open event → return 1x1 GIF

**วันที่ 35: Status Flow**
- [ ] Status badge: draft → sent → viewed → paid → overdue
- [ ] Auto-update `viewed` เมื่อ share link เปิด (ครั้งแรก)
- [ ] Auto-update `overdue` ด้วย scheduler (เช้าทุกวัน ตรวจ due_date)
- [ ] Mark as paid manually (button)
- [ ] `invoice_activities` log ทุก event

**Deliverable สัปดาห์ 7:** ส่ง invoice ผ่าน email + รู้ว่า viewed แล้ว

---

#### สัปดาห์ 8 — AI Autofill

**วันที่ 36–37: Line Item Suggestions**
- [ ] `AiAutofillService`: ส่ง client history + current items ไปหา Claude
- [ ] Claude return: suggested line items + price range จากประวัติ
- [ ] Vercel AI SDK `streamText()` → streaming suggestions ใน `AiSuggestPanel.vue`
- [ ] User คลิก "เพิ่ม" → add line item ทันที

**วันที่ 38–39: Smart Autofill**
- [ ] เมื่อเลือก client → auto-suggest line items ที่เคยใช้กับ client นี้ (top 5)
- [ ] Price memory: แนะนำราคาเฉลี่ยของ service นั้นๆ
- [ ] Invoice number format จาก company settings

**วันที่ 40: Dashboard + Analytics**
- [ ] Dashboard: total revenue (paid), outstanding (sent+viewed+overdue), draft count
- [ ] Chart: revenue by month (last 6 months) ใช้ Chart.js
- [ ] Top clients by revenue
- [ ] Average days to payment

**Deliverable สัปดาห์ 8:** AI autofill ทำงาน, dashboard แสดงข้อมูลครบ

---

### Phase 5: Billing + Launch (สัปดาห์ 9–10)

**เป้าหมาย:** เก็บเงินได้จริง + launch

#### สัปดาห์ 9 — Subscription Billing

**วันที่ 41–42: Omise Subscription**
- [ ] Omise Customer + PromptPay / card setup
- [ ] Plan: Free (5 invoice/เดือน), Pro (฿199), Business (฿499)
- [ ] Webhook: `customer.subscription.renewed` → update subscription
- [ ] `SubscriptionMiddleware`: ตรวจ plan → block ถ้าเกิน Free limit

**วันที่ 43–44: Billing UI**
- [ ] Pricing page (public)
- [ ] `/settings/billing`: plan info, upgrade button, invoice history
- [ ] Upgrade flow: เลือก plan → กรอก card / PromptPay → confirm
- [ ] Downgrade: cancel subscription → plan ลดเมื่อสิ้นรอบ

**วันที่ 45: Settings Pages**
- [ ] `/settings/company` — แก้ข้อมูลบริษัท, logo, brand color
- [ ] `/settings/invoice` — prefix, default template, default language
- [ ] `/settings/account` — password, email, delete account (PDPA)

**Deliverable สัปดาห์ 9:** Billing ทำงาน, upgrade/downgrade ได้

---

#### สัปดาห์ 10 — Polish + Launch

**วันที่ 46–47: Landing Page**
- [ ] หน้า landing: hero, features, pricing, testimonials (beta users), FAQ
- [ ] Video demo (screen record 90 วินาที)
- [ ] `/demo`: สร้าง demo account พร้อม sample data

**วันที่ 48: Performance + Security**
- [ ] Rate limiting: `/invoice/share` (100 req/min per IP), PDF generate (5 req/min per user)
- [ ] Cache invoice list (Redis TTL 1 นาที, invalidate เมื่อมีการเปลี่ยนแปลง)
- [ ] Limit PDF concurrent jobs: queue `pdf` max 3 workers
- [ ] PDPA: export ข้อมูลทั้งหมด JSON, delete account

**วันที่ 49: Beta Testing**
- [ ] Invite 20 beta users (freelancer + SME เล็ก)
- [ ] Collect feedback ด้วย Typeform
- [ ] Fix critical bugs จาก beta

**วันที่ 50: Launch**
- [ ] โพสต์ใน Facebook Group: Freelance Thailand, Thai Startup
- [ ] โพสต์ใน Pantip: กระทู้บัญชี/ภาษี
- [ ] ProductHunt submission (เวลา 00:01 PST)
- [ ] Monitor: Sentry, Horizon queue, error rate

**Deliverable สัปดาห์ 10:** v1.0 public 🚀

---

## 8. Monetization & Pricing

### Pricing Tiers

| แผน | ราคา/เดือน | Invoice/เดือน | Templates | AI | Tracking | Users |
|---|---|---|---|---|---|---|
| **Free** | ฿0 | 5 | 1 | ✗ | ✗ | 1 |
| **Pro** | ฿199 | ไม่จำกัด | 5 + logo | ✓ | ✓ | 1 |
| **Business** | ฿499 | ไม่จำกัด | 5 + custom | ✓ | ✓ | 3 |

### Annual Discount
- Pro Annual: ฿1,990/ปี (ประหยัด ฿398 = 2 เดือนฟรี)
- Business Annual: ฿4,990/ปี (ประหยัด ฿998)

### Unit Economics

```
Pro user:
  Revenue:               ฿199/เดือน
  Claude API cost:       ~฿3/เดือน  (autofill ~20 calls × ฿0.15)
  Omise fee:             ~฿6/เดือน  (3% ของ ฿199)
  Net per Pro user:      ~฿190/เดือน

Infra cost (คงที่):
  VPS (Forge):           ฿1,200/เดือน
  Cloudflare R2:         ฿150/เดือน (PDF storage)
  Redis:                 ฿300/เดือน
  Meilisearch:           ฿300/เดือน
  Resend email:          ฿200/เดือน
  Total infra:           ~฿2,150/เดือน

Break-even: ~12 Pro users → MRR ฿2,388 > infra ฿2,150
```

### Revenue Projection (ปีแรก)

| เดือน | Free MAU | Pro | Business | MRR | Cumulative Revenue |
|---|---|---|---|---|---|
| 1–2 | 80 | 15 | 3 | ฿4,482 | ฿8,964 |
| 3–4 | 200 | 50 | 10 | ฿14,900 | ฿38,764 |
| 5–6 | 500 | 120 | 25 | ฿36,355 | ฿111,474 |
| 7–9 | 1,000 | 250 | 50 | ฿74,450 | ฿334,824 |
| 10–12 | 2,000 | 450 | 90 | ฿133,410 | ฿735,054 |

> ARR ปีแรก: **~฿700,000–฿900,000** (conservative)

---

## 9. Go-to-Market Strategy

### Phase 0: Pre-launch (ก่อน launch 4 สัปดาห์)
- สร้าง waitlist landing page: "แจ้งเตือนเมื่อเปิดให้ใช้"
- โพสต์ content ใน Facebook Group: "ปัญหา WHT คำนวณผิดบ่อย" → educate market
- Recruit beta users 20 คนจาก network freelancer

### Acquisition Channels

| Channel | Target | Timeline | Expected |
|---|---|---|---|
| Pantip (บัญชี/ภาษี) | SME, freelancer | เดือน 1 | organic |
| Facebook Group Freelance TH | freelancer | เดือน 1 | 50 signups |
| ProductHunt | global dev | เดือน 1 | 200 signups |
| Google SEO "invoice ไทย WHT" | long-tail | เดือน 3+ | 20 signups/เดือน |
| YouTube "วิธีออก invoice ถูกกฎหมาย" | freelancer | เดือน 4+ | 30 signups/เดือน |
| TikTok demo video | freelancer 20–30 | เดือน 2+ | viral potential |

### Positioning Statement
> "Invoice ภาษีไทยที่คำนวณ WHT ถูกต้องอัตโนมัติ — ส่งลูกค้า รู้ว่าเปิดอ่านแล้ว"

### Free → Paid Conversion Triggers
- เมื่อ invoice ครบ 5 ใบ → modal "อัปเกรด Pro ออกได้ไม่จำกัด"
- เมื่อพยายามใช้ AI autofill → "feature นี้ใช้ได้ใน Pro"
- เมื่อพยายามเพิ่ม logo → "ใส่ logo ได้ใน Pro"

---

## 10. Risk Assessment

### ความเสี่ยงกลาง

**Excel ยังเป็น default ของตลาดไทย**
- ปัญหา: SME คุ้นกับ Excel, มี template อยู่แล้ว ไม่รู้สึก pain พอที่จะ switch
- แนวทาง: ชู WHT error เป็น pain point หลัก (คำนวณผิดโดน สรรพากร ปรับ), demo video แสดงความเร็ว vs Excel, free plan ไม่ต้องจ่ายเงินลอง

**Thai tax law เปลี่ยน**
- ปัญหา: ถ้า VAT หรือ WHT rate เปลี่ยน user จะ trust platform น้อยลงถ้าไม่อัปเดตเร็ว
- แนวทาง: config-driven tax rates (ไม่ hardcode), มี changelog ชัดเจน, email แจ้ง user ทันที

**Browsershot/Puppeteer resource หนัก**
- ปัญหา: Puppeteer ใช้ ~150MB RAM ต่อ instance ถ้า concurrent PDF เยอะ server ล่ม
- แนวทาง:
  - Queue `pdf` แยก จาก queue `default`
  - Max 3 concurrent PDF jobs
  - Cache PDF ไว้ใน R2 (ไม่ generate ซ้ำถ้าไม่มีการแก้ไข)
  - Timeout 30 วินาที ต่อ job

### ความเสี่ยงต่ำ

**DBD API unstable**
- แนวทาง: timeout 3 วินาที, graceful fallback ให้ user กรอกเอง, cache ผลลัพธ์ใน Redis 24h

**Competition จาก Zoho / FreshBooks**
- ข้อได้เปรียบ: ราคาถูกกว่า (฿199 vs ฿400+), WHT support, Thai-first UX
- ต่างชาติไม่มีแรงจูงใจสร้าง WHT feature เพราะ market เล็ก

**Churn จาก free tier**
- แนวทาง: email drip campaign เมื่อ free user ออก invoice ครบ 3 ใบ: แสดง value ของ Pro

---

## 11. Definition of Done

### MVP Launch Checklist

**Core Features**
- [x] สร้าง invoice พร้อม line items ได้
- [ ] คำนวณ VAT + WHT ถูกต้องทุก scenario (ผ่าน unit test)
- [ ] Download PDF: Thai font สมบูรณ์, layout ไม่แตก
- [ ] 5 templates ให้เลือก
- [ ] Share link สำหรับลูกค้า
- [ ] Email invoice พร้อม tracking "viewed"
- [ ] Invoice status flow: draft → sent → viewed → paid
- [ ] Client และ Product database พร้อม autofill
- [ ] DBD lookup จากเลขนิติบุคคล

**Tax Engine**
- [ ] VAT 7% คำนวณถูกต้อง
- [ ] WHT 0%, 1%, 3%, 5% คำนวณถูกต้อง
- [ ] Discount (percent + amount) ก่อนคำนวณ VAT
- [ ] WHT คำนวณบน subtotal ก่อน VAT (ถูกต้องตามกฎหมาย)
- [ ] Unit test pass 100% ทุก scenario

**PDF Engine**
- [ ] Thai font render ครบ ไม่มี tofu (□)
- [ ] A4 portrait, margins ถูกต้อง
- [ ] Page break ทำงานเมื่อ line items ยาว
- [ ] Logo, brand color แสดงถูกต้อง
- [ ] Bilingual (ไทย + อังกฤษ) อยู่ในใบเดียวกัน

**Business**
- [ ] Free plan enforce 5 invoice/เดือน ได้จริง
- [ ] Omise Pro subscription ทำงาน
- [ ] AI autofill ทำงานใน Pro plan เท่านั้น
- [ ] Dashboard แสดง revenue, outstanding ถูกต้อง

---

## หมายเหตุสำหรับ Developer

### Environment Variables

```bash
# Laravel (.env)
APP_URL=https://app.domain.com

DB_CONNECTION=mysql
DB_DATABASE=invoice_app
REDIS_HOST=redis

FILESYSTEM_DISK=r2
AWS_ENDPOINT=https://{account-id}.r2.cloudflarestorage.com
AWS_BUCKET=invoice-pdfs
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=

ANTHROPIC_API_KEY=

OMISE_PUBLIC_KEY=
OMISE_SECRET_KEY=

RESEND_API_KEY=
MAIL_FROM_ADDRESS=noreply@domain.com

DBD_API_KEY=                        # กรมพัฒนาธุรกิจการค้า
DBD_API_URL=https://openapi.dbd.go.th

SENTRY_LARAVEL_DSN=

# Puppeteer (ใน Docker)
BROWSERSHOT_NODE_BINARY=/usr/local/bin/node
BROWSERSHOT_NPM_BINARY=/usr/local/bin/npm
```

### Queue Configuration

```php
// config/queue.php connections
'redis' => [
    'driver' => 'redis',
    'queues' => ['default', 'pdf', 'emails'],
],

// Horizon workers
'environments' => [
    'production' => [
        'default-worker' => ['queue' => 'default', 'maxProcesses' => 5],
        'pdf-worker'     => ['queue' => 'pdf',     'maxProcesses' => 3],  // จำกัด 3 concurrent
        'email-worker'   => ['queue' => 'emails',  'maxProcesses' => 2],
    ],
],
```

### Key Design Decisions

| Decision | ทางเลือก | เหตุผล |
|---|---|---|
| Browsershot แทน DomPDF | DomPDF, mPDF | Thai font + CSS Grid/Flex รองรับ ครบ |
| R2 cache PDF | Generate ทุกครั้ง | ประหยัด Puppeteer RAM + เร็วขึ้น |
| base64 font inject | โหลด font จาก URL | Puppeteer ไม่ต้องรอ network request |
| Vue Draggable | Custom drag | มาตรฐาน, stable, ไม่ต้องเขียนเอง |
| WHT ก่อน VAT | คำนวณรวมกัน | ถูกกฎหมายภาษีไทย — WHT คิดจาก subtotal เสมอ |
| Invoice number auto-increment per company | UUID | ป้องกัน conflict, human-readable, audit trail ดี |

---

*review เอกสารนี้ทุก 2 สัปดาห์ และ update checklist ให้ตรงกับ implementation จริง*
