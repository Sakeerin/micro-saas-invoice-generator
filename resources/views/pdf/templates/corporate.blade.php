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
  line-height: 1.5;
}

.container {
  padding: 0;
}

.header-strip {
  background: #333;
  color: #fff;
  padding: 30px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.invoice-title-large {
  font-size: 28pt;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.header-meta {
  text-align: right;
}

.content {
  padding: 40px;
}

.top-section {
  display: flex;
  justify-content: space-between;
  margin-bottom: 40px;
}

.company-info {
  width: 50%;
}

.company-name {
  font-size: 14pt;
  font-weight: bold;
  margin-bottom: 5px;
}

.client-info {
  width: 40%;
  text-align: right;
}

.section-label {
  font-size: 8pt;
  font-weight: bold;
  color: #666;
  text-transform: uppercase;
  border-bottom: 1px solid #ccc;
  margin-bottom: 8px;
  padding-bottom: 2px;
  display: inline-block;
}

.client-name {
  font-size: 12pt;
  font-weight: bold;
  margin-bottom: 5px;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 40px;
  border: 1px solid #000;
  padding: 15px;
}

.detail-item {
  text-align: center;
}

.detail-label {
  font-size: 8pt;
  color: #666;
  text-transform: uppercase;
  margin-bottom: 3px;
}

.detail-value {
  font-weight: bold;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
}

table.items thead th {
  border-top: 2px solid #000;
  border-bottom: 2px solid #000;
  padding: 10px 5px;
  font-size: 9pt;
  font-weight: bold;
  text-align: left;
  text-transform: uppercase;
}

table.items thead th:last-child,
table.items thead th:nth-last-child(2),
table.items thead th:nth-last-child(3) {
  text-align: right;
}

table.items tbody td {
  padding: 12px 5px;
  border-bottom: 1px solid #eee;
  vertical-align: top;
}

table.items tbody td:last-child,
table.items tbody td:nth-last-child(2),
table.items tbody td:nth-last-child(3) {
  text-align: right;
}

.bottom-section {
  display: flex;
  justify-content: space-between;
}

.payment-info {
  width: 55%;
}

.summary-table {
  width: 40%;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  border-bottom: 1px solid #eee;
}

.summary-row.total {
  font-size: 14pt;
  font-weight: bold;
  border-bottom: 3px double #000;
  margin-top: 10px;
  padding-top: 10px;
}

.bank-details {
  margin-top: 20px;
  font-size: 9pt;
}

.bank-title {
  font-weight: bold;
  text-decoration: underline;
  margin-bottom: 5px;
}

.footer {
  margin-top: 60px;
  padding-top: 20px;
  border-top: 1px solid #ccc;
  font-size: 8pt;
  color: #666;
  text-align: center;
}

.notes-section {
  margin-top: 20px;
  font-size: 9pt;
  color: #444;
}
</style>
</head>
<body>

<div class="container">
  <div class="header-strip">
    <div class="invoice-title-large">
      @if($invoice->language === 'th') ใบแจ้งหนี้
      @elseif($invoice->language === 'en') INVOICE
      @else ใบแจ้งหนี้ / INVOICE
      @endif
    </div>
    <div class="header-meta">
      <div style="font-size: 14pt; font-weight: bold;">#{{ $invoice->invoice_number }}</div>
      <div>
        @if($invoice->language === 'th') วันที่: {{ $invoice->issue_date->thaiDate() }}
        @elseif($invoice->language === 'en') Date: {{ $invoice->issue_date->format('d F Y') }}
        @else วันที่ / Date: {{ $invoice->issue_date->thaiDate() }}
        @endif
      </div>
    </div>
  </div>

  <div class="content">
    <div class="top-section">
      <div class="company-info">
        @if($invoice->company->logo_url)
          <img src="{{ $invoice->company->logo_url }}" alt="{{ $invoice->company->name }}" style="max-height: 50px; margin-bottom: 15px;">
        @endif
        <div class="company-name">{{ $invoice->company->name }}</div>
        @if($invoice->company->name_en)
          <div style="font-weight: bold;">{{ $invoice->company->name_en }}</div>
        @endif
        <div style="white-space: pre-line;">{{ $invoice->company->address }}</div>
        @if($invoice->company->tax_id)
          <div>
            @if($invoice->language === 'th') เลขประจำตัวผู้เสียภาษี: {{ $invoice->company->tax_id }}
            @elseif($invoice->language === 'en') TAX ID: {{ $invoice->company->tax_id }}
            @else เลขประจำตัวผู้เสียภาษี / TAX ID: {{ $invoice->company->tax_id }}
            @endif
          </div>
        @endif
      </div>

      <div class="client-info">
        <div class="section-label">
            @if($invoice->language === 'th') ผู้รับบริการ
            @elseif($invoice->language === 'en') Bill To
            @else Bill To / ผู้รับบริการ
            @endif
        </div>
        <div class="client-name">{{ $invoice->client_name }}</div>
        @if($invoice->client_name_en)
          <div style="font-weight: bold;">{{ $invoice->client_name_en }}</div>
        @endif
        <div style="white-space: pre-line;">{{ $invoice->client_address }}</div>
        @if($invoice->client_tax_id)
          <div>
            @if($invoice->language === 'th') เลขประจำตัวผู้เสียภาษี: {{ $invoice->client_tax_id }}
            @elseif($invoice->language === 'en') TAX ID: {{ $invoice->client_tax_id }}
            @else เลขประจำตัวผู้เสียภาษี / TAX ID: {{ $invoice->client_tax_id }}
            @endif
          </div>
        @endif
      </div>
    </div>

    <div class="details-grid">
      <div class="detail-item">
        <div class="detail-label">
            @if($invoice->language === 'th') วันที่ออก
            @elseif($invoice->language === 'en') Date
            @else Date / วันที่ออก
            @endif
        </div>
        <div class="detail-value">
            @if($invoice->language === 'en') {{ $invoice->issue_date->format('d/m/Y') }}
            @else {{ $invoice->issue_date->thaiDate() }}
            @endif
        </div>
      </div>
      <div class="detail-item">
        <div class="detail-label">
            @if($invoice->language === 'th') วันครบกำหนด
            @elseif($invoice->language === 'en') Due Date
            @else Due Date / ครบกำหนด
            @endif
        </div>
        <div class="detail-value">
            @if($invoice->due_date)
                @if($invoice->language === 'en') {{ $invoice->due_date->format('d/m/Y') }}
                @else {{ $invoice->due_date->thaiDate() }}
                @endif
            @else - @endif
        </div>
      </div>
      <div class="detail-item">
        <div class="detail-label">
            @if($invoice->language === 'th') อ้างอิง
            @elseif($invoice->language === 'en') Reference
            @else Reference / อ้างอิง
            @endif
        </div>
        <div class="detail-value">{{ $invoice->reference ?? '-' }}</div>
      </div>
      <div class="detail-item">
        <div class="detail-label">
            @if($invoice->language === 'th') สกุลเงิน
            @elseif($invoice->language === 'en') Currency
            @else Currency / สกุลเงิน
            @endif
        </div>
        <div class="detail-value">{{ $invoice->currency }}</div>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th style="width:45%">
            @if($invoice->language === 'th') รายการ
            @elseif($invoice->language === 'en') Description
            @else Description / รายการ
            @endif
          </th>
          <th style="width:10%">
            @if($invoice->language === 'th') จำนวน
            @elseif($invoice->language === 'en') Qty
            @else Qty
            @endif
          </th>
          <th style="width:15%">
            @if($invoice->language === 'th') ราคา/หน่วย
            @elseif($invoice->language === 'en') Unit Price
            @else Price
            @endif
          </th>
          <th style="width:10%">
            @if($invoice->language === 'th') ส่วนลด
            @elseif($invoice->language === 'en') Disc
            @else Disc
            @endif
          </th>
          <th style="width:20%">
            @if($invoice->language === 'th') จำนวนเงิน
            @elseif($invoice->language === 'en') Amount
            @else Amount / จำนวนเงิน
            @endif
          </th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $item)
        <tr>
          <td>
            <div style="font-weight: bold;">{{ $item->name }}</div>
            @if($item->name_en)<div style="font-size: 9pt; color: #444;">{{ $item->name_en }}</div>@endif
            @if($item->description)<div style="font-size: 8pt; color: #666; margin-top: 4px;">{{ $item->description }}</div>@endif
          </td>
          <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
          <td>{{ number_format($item->unit_price, 2) }}</td>
          <td>{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
          <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="bottom-section">
      <div class="payment-info">
        @if($invoice->company->bank_name)
          <div class="bank-details">
            <div class="bank-title">
                @if($invoice->language === 'th') ข้อมูลการชำระเงิน
                @elseif($invoice->language === 'en') PAYMENT INFORMATION
                @else PAYMENT INFORMATION / ข้อมูลการชำระเงิน
                @endif
            </div>
            <div><strong>
                @if($invoice->language === 'th') ธนาคาร:
                @elseif($invoice->language === 'en') Bank:
                @else Bank / ธนาคาร:
                @endif
            </strong> {{ $invoice->company->bank_name }}</div>
            <div><strong>
                @if($invoice->language === 'th') ชื่อบัญชี:
                @elseif($invoice->language === 'en') Account Name:
                @else Account Name / ชื่อบัญชี:
                @endif
            </strong> {{ $invoice->company->bank_account_name }}</div>
            <div><strong>
                @if($invoice->language === 'th') เลขบัญชี:
                @elseif($invoice->language === 'en') Account No:
                @else Account No / เลขบัญชี:
                @endif
            </strong> {{ $invoice->company->bank_account }}</div>
          </div>
        @endif

        @if($invoice->notes)
          <div class="notes-section">
            <strong>
                @if($invoice->language === 'th') หมายเหตุ:
                @elseif($invoice->language === 'en') Notes:
                @else Notes / หมายเหตุ:
                @endif
            </strong><br>
            {{ $invoice->notes }}
          </div>
        @endif
      </div>

      <div class="summary-table">
        <div class="summary-row">
          <span>
            @if($invoice->language === 'th') รวมเงิน
            @elseif($invoice->language === 'en') Subtotal
            @else Subtotal / รวมเงิน
            @endif
          </span>
          <span>{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="summary-row">
          <span>
            @if($invoice->language === 'th') ส่วนลด
            @elseif($invoice->language === 'en') Discount
            @else Discount / ส่วนลด
            @endif
          </span>
          <span>-{{ number_format($invoice->discount_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->vat_rate > 0)
        <div class="summary-row">
          <span>ภาษีมูลค่าเพิ่ม / VAT ({{ number_format($invoice->vat_rate, 0) }}%)</span>
          <span>{{ number_format($invoice->vat_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->wht_rate > 0)
        <div class="summary-row">
          <span>หัก ณ ที่จ่าย / WHT ({{ number_format($invoice->wht_rate, 0) }}%)</span>
          <span>-{{ number_format($invoice->wht_amount, 2) }}</span>
        </div>
        @endif
        <div class="summary-row total">
          <span>
            @if($invoice->language === 'th') ยอดรวมสุทธิ
            @elseif($invoice->language === 'en') TOTAL DUE
            @else TOTAL DUE / ยอดรวมสุทธิ
            @endif
          </span>
          <span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
        </div>
      </div>
    </div>

    <div class="footer">
      <div>
        @if($invoice->language === 'th') เอกสารฉบับนี้พิมพ์จากคอมพิวเตอร์ ไม่ต้องมีลายมือชื่อ
        @elseif($invoice->language === 'en') This is a computer generated invoice and no signature is required.
        @else This is a computer generated invoice and no signature is required. / เอกสารฉบับนี้พิมพ์จากคอมพิวเตอร์ ไม่ต้องมีลายมือชื่อ
        @endif
      </div>
      <div>Generated by {{ config('app.name') }} on {{ now()->format('d/m/Y H:i') }}</div>
    </div>
  </div>
</div>

</body>
</html>
