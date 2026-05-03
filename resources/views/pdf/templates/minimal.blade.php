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
  color: #333;
  line-height: 1.6;
}

.container {
  padding: 50px;
}

.header {
  margin-bottom: 60px;
}

.invoice-title {
  font-size: 32pt;
  font-weight: 300;
  color: #ccc;
  letter-spacing: 0.1em;
  margin-bottom: 20px;
}

.header-top {
  display: flex;
  justify-content: space-between;
}

.company-brand {
  font-size: 18pt;
  font-weight: bold;
}

.meta-grid {
  display: flex;
  gap: 40px;
  margin-top: 20px;
  font-size: 10pt;
  color: #777;
}

.parties {
  display: flex;
  gap: 100px;
  margin-bottom: 60px;
}

.party-label {
  font-size: 9pt;
  color: #aaa;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.party-content {
  font-size: 11pt;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 40px;
}

table.items th {
  text-align: left;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
  font-size: 9pt;
  color: #aaa;
  text-transform: uppercase;
}

table.items td {
  padding: 20px 0;
  border-bottom: 1px solid #f9f9f9;
}

.text-right { text-align: right; }

.summary {
  display: flex;
  justify-content: flex-end;
}

.summary-table {
  width: 250px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  font-size: 10pt;
}

.summary-row.total {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #000;
  font-size: 16pt;
  font-weight: bold;
  color: #000;
}

.footer {
  margin-top: 100px;
  font-size: 9pt;
  color: #bbb;
}

.bank-info {
  margin-top: 40px;
  font-size: 10pt;
  color: #666;
}
</style>
</head>
<body>

<div class="container">
  <div class="header">
    <div class="invoice-title">
      @if($invoice->language === 'th') ใบแจ้งหนี้
      @elseif($invoice->language === 'en') INVOICE
      @else INVOICE
      @endif
    </div>
    <div class="header-top">
      <div class="company-brand">
        @if($invoice->company->logo_url)
          <img src="{{ $invoice->company->logo_url }}" style="max-height: 40px;">
        @else
          {{ $invoice->company->name }}
        @endif
      </div>
      <div style="text-align: right;">
        <div style="font-size: 14pt; font-weight: bold;">#{{ $invoice->invoice_number }}</div>
        <div class="meta-grid">
          <div>
            <strong>DATE</strong><br>
            {{ $invoice->issue_date->format('d M Y') }}
          </div>
          @if($invoice->due_date)
          <div>
            <strong>DUE</strong><br>
            {{ $invoice->due_date->format('d M Y') }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="parties">
    <div style="flex: 1;">
      <div class="party-label">From</div>
      <div class="party-content">
        <strong>{{ $invoice->company->name }}</strong><br>
        {{ $invoice->company->address }}<br>
        @if($invoice->company->tax_id)Tax ID: {{ $invoice->company->tax_id }}@endif
      </div>
    </div>
    <div style="flex: 1;">
      <div class="party-label">To</div>
      <div class="party-content">
        <strong>{{ $invoice->client_name }}</strong><br>
        {{ $invoice->client_address }}<br>
        @if($invoice->client_tax_id)Tax ID: {{ $invoice->client_tax_id }}@endif
      </div>
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th style="width: 50%;">Description</th>
        <th class="text-right">Qty</th>
        <th class="text-right">Price</th>
        <th class="text-right">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
      <tr>
        <td>
          <div style="font-weight: bold;">{{ $item->name }}</div>
          @if($item->description)<div style="font-size: 9pt; color: #999;">{{ $item->description }}</div>@endif
        </td>
        <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
        <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="summary">
    <div class="summary-table">
      <div class="summary-row">
        <span>Subtotal</span>
        <span>{{ number_format($invoice->subtotal, 2) }}</span>
      </div>
      @if($invoice->vat_rate > 0)
      <div class="summary-row">
        <span>VAT {{ number_format($invoice->vat_rate, 0) }}%</span>
        <span>{{ number_format($invoice->vat_amount, 2) }}</span>
      </div>
      @endif
      <div class="summary-row total">
        <span>Total</span>
        <span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
      </div>
    </div>
  </div>

  @if($invoice->company->bank_name)
  <div class="bank-info">
    <strong>Payment:</strong> {{ $invoice->company->bank_name }} / {{ $invoice->company->bank_account }} / {{ $invoice->company->bank_account_name }}
  </div>
  @endif

  <div class="footer">
    {{ config('app.name') }} &mdash; {{ $invoice->invoice_number }}
  </div>
</div>

</body>
</html>
