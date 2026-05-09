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
  color: #1f2937;
  line-height: 1.5;
}

.accent-bar {
  height: 12px;
  background: {{ $invoice->company->brand_color ?? '#1a56db' }};
}

.container {
  padding: 40px;
}

.header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 50px;
}

.invoice-branding {
  display: flex;
  align-items: center;
  gap: 20px;
}

.company-logo img {
  max-height: 80px;
  max-width: 200px;
}

.invoice-label-creative {
  font-size: 40pt;
  font-weight: 900;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
  opacity: 0.15;
  position: absolute;
  right: 40px;
  top: 40px;
  z-index: -1;
}

.columns {
  display: flex;
  justify-content: space-between;
  gap: 60px;
  margin-bottom: 40px;
}

.column-left {
  width: 60%;
}

.column-right {
  width: 35%;
}

.section-title {
  font-size: 9pt;
  font-weight: bold;
  text-transform: uppercase;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
  margin-bottom: 12px;
  letter-spacing: 1px;
}

.party-details {
  font-size: 10pt;
}

.party-name {
  font-size: 16pt;
  font-weight: bold;
  margin-bottom: 8px;
}

.meta-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f3f4f6;
}

.meta-label {
  color: #6b7280;
  font-size: 9pt;
}

.meta-value {
  font-weight: bold;
  text-align: right;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 40px;
}

table.items thead th {
  text-align: left;
  padding: 15px 10px;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
  font-size: 10pt;
  font-weight: bold;
  border-bottom: 2px solid {{ $invoice->company->brand_color ?? '#1a56db' }};
}

table.items thead th:last-child,
table.items thead th:nth-last-child(2) {
  text-align: right;
}

table.items tbody td {
  padding: 15px 10px;
  border-bottom: 1px solid #f3f4f6;
}

table.items tbody td:last-child,
table.items tbody td:nth-last-child(2) {
  text-align: right;
}

.summary-container {
  display: flex;
  justify-content: flex-end;
}

.summary-box {
  background: #f9fafb;
  padding: 25px;
  border-radius: 12px;
  width: 300px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 10pt;
}

.summary-row.total {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #e5e7eb;
  font-size: 14pt;
  font-weight: bold;
  color: {{ $invoice->company->brand_color ?? '#1a56db' }};
}

.footer-sections {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 40px;
}

.payment-notes {
  width: 50%;
}

.bank-card {
  border-left: 4px solid {{ $invoice->company->brand_color ?? '#1a56db' }};
  background: #f0f7ff;
  padding: 15px;
  border-radius: 0 8px 8px 0;
  font-size: 10pt;
}

.footer {
  margin-top: 80px;
  font-size: 9pt;
  color: #9ca3af;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand {
  font-weight: bold;
  color: #4b5563;
}
</style>
</head>
<body>

<div class="accent-bar"></div>

<div class="container">
  <div class="invoice-label-creative">
    @if($invoice->language === 'th') ใบแจ้งหนี้
    @elseif($invoice->language === 'en') INVOICE
    @else INVOICE
    @endif
  </div>

  <div class="header">
    <div class="invoice-branding">
      <div class="company-logo">
        @if($invoice->company->logo_url)
          <img src="{{ $invoice->company->logo_url }}" alt="{{ $invoice->company->name }}">
        @else
          <div style="font-size: 20pt; font-weight: 900; color: {{ $invoice->company->brand_color ?? '#1a56db' }}">
            {{ $invoice->company->name }}
          </div>
        @endif
      </div>
    </div>
    <div style="text-align: right;">
      <div style="font-size: 12pt; font-weight: bold; color: {{ $invoice->company->brand_color ?? '#1a56db' }}">
        {{ $invoice->invoice_number }}
      </div>
      <div style="color: #6b7280;">
        @if($invoice->language === 'th') วันที่ออก: {{ $invoice->issue_date->thaiDate() }}
        @elseif($invoice->language === 'en') Issued on {{ $invoice->issue_date->format('d F Y') }}
        @else Issued on / วันที่ออก: {{ $invoice->issue_date->thaiDate() }}
        @endif
      </div>
    </div>
  </div>

  <div class="columns">
    <div class="column-left">
      <div class="section-title">
        @if($invoice->language === 'th') ผู้รับบริการ
        @elseif($invoice->language === 'en') Bill To
        @else Bill To / ผู้รับบริการ
        @endif
      </div>
      <div class="party-name">{{ $invoice->client_name }}</div>
      @if($invoice->client_name_en)
        <div style="font-weight: bold; color: #4b5563; margin-top: -4px; margin-bottom: 8px;">{{ $invoice->client_name_en }}</div>
      @endif
      <div class="party-details">
        <div style="white-space: pre-line;">{{ $invoice->client_address }}</div>
        @if($invoice->client_tax_id)
          <div style="margin-top: 8px;">
            @if($invoice->language === 'th') เลขประจำตัวผู้เสียภาษี: {{ $invoice->client_tax_id }}
            @elseif($invoice->language === 'en') TAX ID: {{ $invoice->client_tax_id }}
            @else TAX ID / เลขภาษี: {{ $invoice->client_tax_id }}
            @endif
          </div>
        @endif
      </div>
    </div>

    <div class="column-right">
      <div class="section-title">
        @if($invoice->language === 'th') รายละเอียด
        @elseif($invoice->language === 'en') Invoice Details
        @else Invoice Details / รายละเอียด
        @endif
      </div>
      <div class="meta-item">
        <span class="meta-label">
            @if($invoice->language === 'th') วันที่ออก
            @elseif($invoice->language === 'en') Issue Date
            @else Issue Date
            @endif
        </span>
        <span class="meta-value">
            @if($invoice->language === 'en') {{ $invoice->issue_date->format('d/m/Y') }}
            @else {{ $invoice->issue_date->thaiDate() }}
            @endif
        </span>
      </div>
      <div class="meta-item">
        <span class="meta-label">
            @if($invoice->language === 'th') วันครบกำหนด
            @elseif($invoice->language === 'en') Due Date
            @else Due Date
            @endif
        </span>
        <span class="meta-value">
            @if($invoice->due_date)
                @if($invoice->language === 'en') {{ $invoice->due_date->format('d/m/Y') }}
                @else {{ $invoice->due_date->thaiDate() }}
                @endif
            @else - @endif
        </span>
      </div>
      @if($invoice->reference)
      <div class="meta-item">
        <span class="meta-label">
            @if($invoice->language === 'th') อ้างอิง
            @elseif($invoice->language === 'en') Reference
            @else Reference
            @endif
        </span>
        <span class="meta-value">{{ $invoice->reference }}</span>
      </div>
      @endif
      <div class="meta-item">
        <span class="meta-label">
            @if($invoice->language === 'th') สกุลเงิน
            @elseif($invoice->language === 'en') Currency
            @else Currency
            @endif
        </span>
        <span class="meta-value">{{ $invoice->currency }}</span>
      </div>
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th style="width: 50%">
            @if($invoice->language === 'th') รายการ
            @elseif($invoice->language === 'en') Project Item
            @else Item / รายการ
            @endif
        </th>
        <th style="width: 10%">
            @if($invoice->language === 'th') จำนวน
            @elseif($invoice->language === 'en') Qty
            @else Qty
            @endif
        </th>
        <th style="width: 20%">
            @if($invoice->language === 'th') ราคา
            @elseif($invoice->language === 'en') Price
            @else Price
            @endif
        </th>
        <th style="width: 20%">
            @if($invoice->language === 'th') จำนวนเงิน
            @elseif($invoice->language === 'en') Amount
            @else Amount
            @endif
        </th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
      <tr>
        <td>
          <div style="font-weight: bold;">{{ $item->name }}</div>
          @if($item->name_en)<div style="font-size: 9pt; color: #6b7280;">{{ $item->name_en }}</div>@endif
          @if($item->description)<div style="font-size: 9pt; color: #9ca3af; margin-top: 4px;">{{ $item->description }}</div>@endif
        </td>
        <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
        <td>{{ number_format($item->unit_price, 2) }}</td>
        <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer-sections">
    <div class="payment-notes">
      @if($invoice->company->bank_name)
        <div class="section-title">
          @if($invoice->language === 'th') ข้อมูลการชำระเงิน
          @elseif($invoice->language === 'en') Payment Info
          @else Payment Info / ชำระเงิน
          @endif
        </div>
        <div class="bank-card">
          <strong>{{ $invoice->company->bank_name }}</strong><br>
          {{ $invoice->company->bank_account_name }}<br>
          <span style="font-size: 12pt; font-weight: bold; letter-spacing: 1px;">{{ $invoice->company->bank_account }}</span>
        </div>
      @endif

      @if($invoice->notes)
        <div class="section-title" style="margin-top: 25px;">
          @if($invoice->language === 'th') หมายเหตุ
          @elseif($invoice->language === 'en') Notes
          @else Notes / หมายเหตุ
          @endif
        </div>
        <div style="font-size: 10pt; color: #4b5563;">{{ $invoice->notes }}</div>
      @endif
    </div>

    <div class="summary-container">
      <div class="summary-box">
        <div class="summary-row">
          <span>
              @if($invoice->language === 'th') รวมเงิน
              @elseif($invoice->language === 'en') Subtotal
              @else Subtotal
              @endif
          </span>
          <span>{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="summary-row">
          <span>
              @if($invoice->language === 'th') ส่วนลด
              @elseif($invoice->language === 'en') Discount
              @else Discount
              @endif
          </span>
          <span style="color: #ef4444;">-{{ number_format($invoice->discount_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->vat_rate > 0)
        <div class="summary-row">
          <span>VAT ({{ number_format($invoice->vat_rate, 0) }}%)</span>
          <span>{{ number_format($invoice->vat_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->wht_rate > 0)
        <div class="summary-row">
          <span>WHT ({{ number_format($invoice->wht_rate, 0) }}%)</span>
          <span style="color: #ef4444;">-{{ number_format($invoice->wht_amount, 2) }}</span>
        </div>
        @endif
        <div class="summary-row total">
          <span>
              @if($invoice->language === 'th') ยอดรวมสุทธิ
              @elseif($invoice->language === 'en') Total Due
              @else Total Due
              @endif
          </span>
          <span>{{ number_format($invoice->total, 2) }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="footer">
    <div>
      From <span class="footer-brand">{{ $invoice->company->name }}</span>
    </div>
    <div>
      {{ config('app.name') }} &middot; {{ now()->format('Y') }}
    </div>
  </div>
</div>

</body>
</html>
