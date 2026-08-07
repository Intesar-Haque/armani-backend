<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Installment payment reminder</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; background:#f5f6f8; margin:0; padding:24px; color:#101215;">
<table role="presentation" style="max-width:640px;margin:0 auto;background:#fff;border-radius:10px;border:1px solid #e6e8eb;padding:32px;">
    <tr><td>
        <div style="color:#c9a04a;font-weight:600;letter-spacing:1px;font-size:12px;text-transform:uppercase;">Armani Properties</div>
        <h2 style="margin:6px 0 20px;">Upcoming payment reminder</h2>

        <p>Hello {{ $booking->client->name }},</p>
        <p>
            This is a friendly reminder that your payment for
            <strong>{{ $installment->label }}</strong> against booking
            <strong>{{ $booking->reference }}</strong>
            ({{ $booking->project->name }}@if($booking->unit_number), Apartment {{ $booking->unit_number }}@endif)
            is due on <strong>{{ $installment->due_date->format('d M Y') }}</strong>.
        </p>

        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:8px;padding:16px;">
            <div><strong>Booking ref:</strong> {{ $booking->reference }}</div>
            <div><strong>Installment:</strong> {{ $installment->label }}</div>
            <div><strong>Amount due:</strong> ৳ {{ number_format((float) $installment->amount_due, 2) }}</div>
            <div><strong>Due date:</strong> {{ $installment->due_date->format('d M Y') }}</div>
        </div>

        <p style="margin-top:24px;">
            Please arrange your payment before the due date. Once we receive it, a money receipt will be emailed to you.
        </p>
        <p>View your full installment schedule at <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>.</p>
    </td></tr>
</table>
</body>
</html>
