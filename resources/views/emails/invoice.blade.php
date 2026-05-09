<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #374151; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header img { max-height: 50px; }
        .content { margin-bottom: 30px; }
        .invoice-summary { background: #f9fafb; padding: 20px; border-radius: 6px; margin-bottom: 25px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .summary-row .label { color: #6b7280; }
        .summary-row .value { font-weight: bold; }
        .total-row { border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 10px; font-size: 18px; color: #111827; }
        .btn-container { text-align: center; }
        .btn { background: {{ $invoice->company->brand_color ?? '#1a56db' }}; color: white; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($invoice->company->logo_url)
                <img src="{{ $invoice->company->logo_url }}" alt="{{ $invoice->company->name }}">
            @else
                <h2 style="margin: 0;">{{ $invoice->company->name }}</h2>
            @endif
        </div>

        <div class="content">
            @if($invoice->language === 'en')
                <p>Dear {{ $invoice->client_name }},</p>
                <p>We have issued a new invoice for you. You can view the details and download the PDF by clicking the button below.</p>
            @else
                <p>เรียน คุณ{{ $invoice->client_name }},</p>
                <p>เราได้ออกใบแจ้งหนี้ฉบับใหม่ให้ท่านแล้ว ท่านสามารถดูรายละเอียดและดาวน์โหลดไฟล์ PDF ได้โดยคลิกที่ปุ่มด้านล่างนี้</p>
            @endif

            <div class="invoice-summary">
                <div class="summary-row">
                    <span class="label">{{ $invoice->language === 'en' ? 'Invoice Number' : 'เลขที่ใบแจ้งหนี้' }}</span>
                    <span class="value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">{{ $invoice->language === 'en' ? 'Issue Date' : 'วันที่ออก' }}</span>
                    <span class="value">{{ $invoice->issue_date->format('d/m/Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">{{ $invoice->language === 'en' ? 'Due Date' : 'วันครบกำหนด' }}</span>
                    <span class="value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="summary-row total-row">
                    <span class="label">{{ $invoice->language === 'en' ? 'Total Due' : 'ยอดรวมสุทธิ' }}</span>
                    <span class="value">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                </div>
            </div>

            <div class="btn-container">
                <a href="{{ route('invoices.show_public', $invoice->share_token) }}" class="btn">
                    {{ $invoice->language === 'en' ? 'View Invoice' : 'ดูใบแจ้งหนี้' }}
                </a>
            </div>
        </div>

        <div class="footer">
            <p>{{ config('app.name') }} &copy; {{ now()->year }}</p>
        </div>
    </div>

    <!-- Tracking Pixel -->
    <img src="{{ route('tracking.pixel', $invoice->share_token) }}" width="1" height="1" style="display:none !important;" />
</body>
</html>
