<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment received</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; background:#f5f6f8; margin:0; padding:24px; color:#101215;">
<table role="presentation" style="max-width:640px;margin:0 auto;background:#fff;border-radius:10px;border:1px solid #e6e8eb;padding:32px;">
    <tr><td>
        <div style="color:#c9a04a;font-weight:600;letter-spacing:1px;font-size:12px;text-transform:uppercase;">Armani Properties</div>
        <h2 style="margin:6px 0 20px;">Payment received</h2>

        <p>Hello {{ $booking->client->name }},</p>
        <p>We've received your payment for <strong>{{ $installment->label }}</strong> against booking <strong>{{ $booking->reference }}</strong>.</p>

        <h4 style="margin-top:24px;">Money receipt</h4>
        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:8px;padding:16px;">
            <div><strong>Booking ref:</strong> {{ $booking->reference }}</div>
            <div><strong>Project:</strong> {{ $booking->project->name }}</div>
            <div><strong>For:</strong> {{ $installment->label }}</div>
            <div><strong>Amount:</strong> ৳ {{ number_format((float) $installment->paid_amount, 2) }}</div>
            <div><strong>Date:</strong> {{ optional($installment->paid_at)->format('d M Y') }}</div>
            @if($installment->payment_method)<div><strong>Method:</strong> {{ $installment->payment_method }}</div>@endif
            @if($installment->payment_reference)<div><strong>Reference:</strong> {{ $installment->payment_reference }}</div>@endif
        </div>

        <p style="margin-top:24px;">View your full payment history at <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>.</p>
    </td></tr>
</table>
</body>
</html>
