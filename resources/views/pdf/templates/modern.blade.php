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

.container {
  padding: 40px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 2px solid {{ $invoice->company->brand_color ?? '#1a56db' }};
}

.company-logo {
  max-width: 50%;
}

.company-logo img {
  max-height: 64px;
  max-width: 180px;
  margin-bottom: 8px;
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
  display: flex;
  justify-content: space-between;
  gap: 32px;
  margin-bottom: 28px;
}

.party {
  width: 48%;
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

.summary-container {
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

<div class="container">
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
        <strong>เลขที่ / No:</strong> {{ $invoice->invoice_number }}<br>
        <strong>วันที่ / Date:</strong> {{ $invoice->issue_date->thaiDate() }}<br>
        @if($invoice->due_date)
          <strong>ครบกำหนด / Due:</strong> {{ $invoice->due_date->thaiDate() }}
        @endif
      </div>
    </div>
  </div>

  <div class="parties">
    <div class="party">
      <div class="party-label">ผู้ออกใบแจ้งหนี้ / From</div>
      <div class="party-name">{{ $invoice->company->name }}</div>
      @if($invoice->company->name_en)
        <div style="color:#555">{{ $invoice->company->name_en }}</div>
      @endif
      <div style="color:#555;font-size:10pt;white-space: pre-line;">{{ $invoice->company->address }}</div>
      @if($invoice->company->tax_id)
        <div style="color:#555;font-size:10pt">เลขภาษี: {{ $invoice->company->tax_id }}</div>
      @endif
    </div>
    <div class="party">
      <div class="party-label">ผู้รับใบแจ้งหนี้ / Bill To</div>
      <div class="party-name">{{ $invoice->client_name }}</div>
      @if($invoice->client_name_en)
        <div style="color:#555">{{ $invoice->client_name_en }}</div>
      @endif
      <div style="color:#555;font-size:10pt;white-space: pre-line;">{{ $invoice->client_address }}</div>
      @if($invoice->client_tax_id)
        <div style="color:#555;font-size:10pt">เลขภาษี: {{ $invoice->client_tax_id }}</div>
      @endif
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th style="width:40%">รายการ / Description</th>
        <th style="width:10%">จำนวน / Qty</th>
        <th style="width:10%">หน่วย / Unit</th>
        <th style="width:15%">ราคา/หน่วย / Price</th>
        <th style="width:10%">ส่วนลด / Disc</th>
        <th style="width:15%">รวม / Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
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

  <div class="summary-container">
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
        <span>VAT {{ number_format($invoice->vat_rate, 0) }}%</span>
        <span>{{ number_format($invoice->vat_amount, 2) }}</span>
      </div>
      @endif
      @if($invoice->wht_rate > 0)
      <div class="summary-row">
        <span>หัก ณ ที่จ่าย {{ number_format($invoice->wht_rate, 0) }}% / WHT</span>
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
      <span>สร้างโดย {{ config('app.name') }}</span>
      <span>{{ $invoice->invoice_number }} · {{ now()->format('d/m/Y H:i') }}</span>
    </div>
  </div>
</div>

</body>
</html>
