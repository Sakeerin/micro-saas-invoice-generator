<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<style>
{!! $fontCss !!}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Sarabun', 'NotoSansThai', sans-serif;
  font-size: 10pt;
  color: #000;
  line-height: 1.4;
}

.container {
  padding: 30px;
}

.header-table {
  width: 100%;
  margin-bottom: 20px;
  border-bottom: 3px solid #333;
  padding-bottom: 10px;
}

.invoice-title {
  font-size: 20pt;
  font-weight: bold;
  text-transform: uppercase;
  text-align: right;
}

.company-info {
  width: 60%;
}

.company-name {
  font-size: 14pt;
  font-weight: bold;
  margin-bottom: 5px;
}

.parties-table {
  width: 100%;
  margin-bottom: 30px;
}

.party-box {
  width: 50%;
  vertical-align: top;
}

.party-header {
  background: #eee;
  padding: 5px 10px;
  font-weight: bold;
  border: 1px solid #ccc;
  font-size: 9pt;
  margin-bottom: 5px;
}

.party-details {
  padding: 5px 10px;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

table.items th {
  background: #333;
  color: #fff;
  border: 1px solid #333;
  padding: 8px 5px;
  font-size: 9pt;
  text-align: center;
}

table.items td {
  border: 1px solid #ccc;
  padding: 8px 5px;
  vertical-align: top;
}

.text-right { text-align: right; }
.text-center { text-align: center; }

.summary-container {
  width: 100%;
}

.summary-table {
  width: 300px;
  float: right;
  border-collapse: collapse;
}

.summary-table td {
  padding: 5px;
  border: 1px solid #ccc;
}

.summary-table .label {
  background: #f9f9f9;
  font-weight: bold;
  width: 60%;
}

.summary-table .total-row td {
  background: #eee;
  font-weight: bold;
  font-size: 12pt;
}

.bank-section {
  clear: both;
  margin-top: 20px;
  border: 1px solid #ccc;
  padding: 10px;
}

.notes-section {
  margin-top: 20px;
  font-size: 9pt;
}

.footer {
  margin-top: 50px;
  text-align: center;
  font-size: 8pt;
  color: #777;
  border-top: 1px solid #eee;
  padding-top: 10px;
}

.clearfix::after {
  content: "";
  clear: both;
  display: table;
}
</style>
</head>
<body>

<div class="container">
  <table class="header-table">
    <tr>
      <td class="company-info">
        @if($invoice->company->logo_url)
          <img src="{{ $invoice->company->logo_url }}" style="max-height: 50px; margin-bottom: 10px;">
        @endif
        <div class="company-name">{{ $invoice->company->name }}</div>
        @if($invoice->company->name_en)<div>{{ $invoice->company->name_en }}</div>@endif
        <div style="font-size: 9pt; color: #444;">
          {{ $invoice->company->address }}<br>
          @if($invoice->company->tax_id)เลขประจำตัวผู้เสียภาษี: {{ $invoice->company->tax_id }}@endif
        </div>
      </td>
      <td style="vertical-align: top;">
        <div class="invoice-title">
          @if($invoice->language === 'th') ใบแจ้งหนี้
          @elseif($invoice->language === 'en') INVOICE
          @else ใบแจ้งหนี้ / INVOICE
          @endif
        </div>
        <div style="text-align: right; margin-top: 10px;">
          <strong>เลขที่ / No:</strong> {{ $invoice->invoice_number }}<br>
          <strong>วันที่ / Date:</strong> {{ $invoice->issue_date->thaiDate() }}<br>
          @if($invoice->due_date)
            <strong>ครบกำหนด / Due:</strong> {{ $invoice->due_date->thaiDate() }}
          @endif
        </div>
      </td>
    </tr>
  </table>

  <table class="parties-table">
    <tr>
      <td class="party-box" style="padding-right: 15px;">
        <div class="party-header">ผู้ออกใบแจ้งหนี้ / FROM</div>
        <div class="party-details">
          <strong>{{ $invoice->company->name }}</strong><br>
          @if($invoice->company->name_en){{ $invoice->company->name_en }}<br>@endif
          <span style="font-size: 9pt;">{{ $invoice->company->address }}</span><br>
          @if($invoice->company->tax_id)เลขภาษี: {{ $invoice->company->tax_id }}@endif
        </div>
      </td>
      <td class="party-box">
        <div class="party-header">ผู้รับใบแจ้งหนี้ / BILL TO</div>
        <div class="party-details">
          <strong>{{ $invoice->client_name }}</strong><br>
          @if($invoice->client_name_en){{ $invoice->client_name_en }}<br>@endif
          <span style="font-size: 9pt;">{{ $invoice->client_address }}</span><br>
          @if($invoice->client_tax_id)เลขภาษี: {{ $invoice->client_tax_id }}@endif
        </div>
      </td>
    </tr>
  </table>

  <table class="items">
    <thead>
      <tr>
        <th>ลำดับ<br>No.</th>
        <th>รายการ<br>Description</th>
        <th>จำนวน<br>Qty</th>
        <th>หน่วย<br>Unit</th>
        <th>ราคา/หน่วย<br>Price</th>
        <th>ส่วนลด<br>Disc</th>
        <th>รวม<br>Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $index => $item)
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>
          <strong>{{ $item->name }}</strong>
          @if($item->name_en)<br><small style="color: #666;">{{ $item->name_en }}</small>@endif
          @if($item->description)<br><small style="color: #888;">{{ $item->description }}</small>@endif
        </td>
        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
        <td class="text-center">{{ $item->unit }}</td>
        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
        <td class="text-right">{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
        <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="clearfix">
    <table class="summary-table">
      <tr>
        <td class="label">ยอดรวม / Subtotal</td>
        <td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td>
      </tr>
      @if($invoice->discount_amount > 0)
      <tr>
        <td class="label">ส่วนลด / Discount</td>
        <td class="text-right">-{{ number_format($invoice->discount_amount, 2) }}</td>
      </tr>
      @endif
      @if($invoice->vat_rate > 0)
      <tr>
        <td class="label">VAT {{ number_format($invoice->vat_rate, 0) }}%</td>
        <td class="text-right">{{ number_format($invoice->vat_amount, 2) }}</td>
      </tr>
      @endif
      @if($invoice->wht_rate > 0)
      <tr>
        <td class="label">หัก ณ ที่จ่าย {{ number_format($invoice->wht_rate, 0) }}%</td>
        <td class="text-right">-{{ number_format($invoice->wht_amount, 2) }}</td>
      </tr>
      @endif
      <tr class="total-row">
        <td class="label">ยอดสุทธิ / Total Due</td>
        <td class="text-right">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
      </tr>
    </table>
  </div>

  @if($invoice->company->bank_name)
  <div class="bank-section">
    <strong>ข้อมูลการชำระเงิน / Payment Details:</strong><br>
    ธนาคาร: {{ $invoice->company->bank_name }} | 
    เลขบัญชี: {{ $invoice->company->bank_account }} | 
    ชื่อบัญชี: {{ $invoice->company->bank_account_name }}
  </div>
  @endif

  @if($invoice->notes)
  <div class="notes-section">
    <strong>หมายเหตุ / Notes:</strong><br>
    {{ $invoice->notes }}
  </div>
  @endif

  <div class="footer">
    สร้างโดย {{ config('app.name') }} | {{ $invoice->invoice_number }} | {{ now()->format('d/m/Y H:i') }}
  </div>
</div>

</body>
</html>
