<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; background:#f5f6f8; margin:0; padding:24px; color:#101215;">
<table role="presentation" style="max-width:640px;margin:0 auto;background:#fff;border-radius:10px;border:1px solid #e6e8eb;padding:32px;">
    <tr><td>
        <div style="color:#c9a04a;font-weight:600;letter-spacing:1px;font-size:12px;text-transform:uppercase;">Armani Properties</div>
        <h2 style="margin:6px 0 20px;">Your booking is confirmed</h2>

        <p>Hello {{ $booking->client->name }},</p>
        <p>We're delighted to confirm your booking for <strong>{{ $booking->project->name }}</strong>@if($booking->unit_number) · Unit {{ $booking->unit_number }} @endif.</p>

        <table style="width:100%;margin-top:20px;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:#6b7280;">Booking reference</td><td style="padding:8px 0;text-align:right;"><strong>{{ $booking->reference }}</strong></td></tr>
            <tr><td style="padding:8px 0;color:#6b7280;">Project</td><td style="padding:8px 0;text-align:right;">{{ $booking->project->name }}</td></tr>
            @if($booking->unit_number)
            <tr><td style="padding:8px 0;color:#6b7280;">Unit</td><td style="padding:8px 0;text-align:right;">{{ $booking->unit_number }}</td></tr>
            @endif
            <tr><td style="padding:8px 0;color:#6b7280;">Booking date</td><td style="padding:8px 0;text-align:right;">{{ $booking->booking_date->format('d M Y') }}</td></tr>
            <tr><td style="padding:8px 0;color:#6b7280;">Total value</td><td style="padding:8px 0;text-align:right;">৳ {{ number_format((float)$booking->total_amount, 2) }}</td></tr>
            <tr><td style="padding:8px 0;color:#6b7280;border-top:1px solid #e6e8eb;">Booking amount paid</td><td style="padding:8px 0;text-align:right;border-top:1px solid #e6e8eb;"><strong>৳ {{ number_format((float)$booking->booking_amount, 2) }}</strong></td></tr>
        </table>

        <h4 style="margin-top:28px;">Money receipt</h4>
        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:8px;padding:16px;">
            <div><strong>Receipt for:</strong> Booking amount</div>
            <div><strong>Amount:</strong> ৳ {{ number_format((float)$booking->booking_amount, 2) }}</div>
            <div><strong>Date:</strong> {{ $booking->booking_date->format('d M Y') }}</div>
            <div><strong>Booking ref:</strong> {{ $booking->reference }}</div>
        </div>

        @if($plainPassword)
        <h4 style="margin-top:28px;">Access your client portal</h4>
        <p>We've created a portal account so you can view your booking, installments, and documents any time.</p>
        <table style="width:100%;background:#f9fafb;border-radius:8px;padding:16px;border:1px solid #e6e8eb;">
            <tr><td style="padding:6px 0;color:#6b7280;">Login URL</td><td style="padding:6px 0;text-align:right;"><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Email</td><td style="padding:6px 0;text-align:right;">{{ $booking->client->email }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Temporary password</td><td style="padding:6px 0;text-align:right;"><code>{{ $plainPassword }}</code></td></tr>
        </table>
        <p style="color:#6b7280;font-size:13px;margin-top:8px;">You'll be asked to change this password on first login.</p>
        @else
        <p style="margin-top:24px;">Log in to your portal at <a href="{{ $loginUrl }}">{{ $loginUrl }}</a> to view details and future installments.</p>
        @endif

        <p style="margin-top:32px;color:#6b7280;font-size:13px;">If you have any questions, simply reply to this email — we're happy to help.</p>
    </td></tr>
</table>
</body>
</html>
