<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt · {{ $booking->reference }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f6f8; padding: 40px; color: #101215; }
        .receipt { max-width: 720px; margin: 0 auto; background: #fff; padding: 40px; border: 1px solid #e6e8eb; border-radius: 12px; }
        .brand { color:#c9a04a; font-weight:600; letter-spacing:1px; font-size:12px; text-transform:uppercase; }
        h1 { margin: 4px 0 24px; font-size: 22px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 0; border-bottom: 1px solid #e6e8eb; }
        th { color: #6b7280; font-weight: 500; width: 40%; }
        .amount { font-size: 28px; font-weight: 600; margin: 20px 0; }
        .footer { margin-top: 40px; font-size: 12px; color: #6b7280; }
        @media print { body { padding: 0; background: #fff; } .no-print { display: none; } }
    </style>
</head>
<body>
<div class="receipt">
    <div class="brand">Armani Properties</div>
    <h1>Money Receipt</h1>

    <table>
        <tr><th>Receipt for</th><td>{{ $document->title }}</td></tr>
        <tr><th>Booking reference</th><td><code>{{ $booking->reference }}</code></td></tr>
        <tr><th>Client</th><td>{{ $booking->client->name }}<br><small>{{ $booking->client->email }}</small></td></tr>
        <tr><th>Project</th><td>{{ $booking->project->name }}@if($booking->unit_number) · Unit {{ $booking->unit_number }}@endif</td></tr>
        @if ($installment)
            <tr><th>Installment</th><td>{{ $installment->label }}</td></tr>
            @if ($installment->payment_method)<tr><th>Payment method</th><td>{{ $installment->payment_method }}</td></tr>@endif
            @if ($installment->payment_reference)<tr><th>Payment reference</th><td>{{ $installment->payment_reference }}</td></tr>@endif
        @endif
        <tr><th>Date</th><td>{{ ($document->issued_at ?? $document->created_at)->format('d M Y') }}</td></tr>
    </table>

    <div class="amount">৳ {{ number_format((float) $document->amount, 2) }}</div>

    <div class="footer">
        This is a system-generated receipt. If you notice any discrepancy, please contact our accounts team.
    </div>

    <div class="no-print" style="margin-top: 30px;">
        <button onclick="window.print()" style="background:#101215;color:#fff;border:0;padding:10px 20px;border-radius:6px;cursor:pointer;">Print</button>
    </div>
</div>
</body>
</html>
