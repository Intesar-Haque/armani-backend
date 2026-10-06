<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends lead events to Meta's Conversions API.
 *
 * The browser reports the same action through the Meta Pixel (fired by a GTM tag);
 * both carry the same event_name + event_id so Meta dedupes them into one lead.
 * Never generate a new event_id here — always use the one the website sent.
 *
 * Failures are logged and swallowed: tracking must never affect the enquiry flow.
 */
class MetaConversionsApi
{
    /**
     * Tracking fields (event_id, fbp, fbc, event_source_url) are read off the request
     * and sanitised here rather than validated with the form — malformed tracking data
     * must never turn a real enquiry into a 422.
     *
     * @param  string  $eventName  'Lead' or 'Schedule'
     * @param  array<string,mixed>  $data  the validated form data (plaintext — hashed here)
     */
    public function send(Request $request, string $eventName, array $data): void
    {
        $pixelId = config('services.meta.pixel_id');
        $token = config('services.meta.capi_access_token');

        if (! $pixelId || ! $token) {
            return;
        }

        $email = $this->normaliseEmail($data['email'] ?? null);

        // Spec 3C.4: a duplicate resubmission is not a second lead. Fake leads damage
        // Meta's ad optimisation, so the same person + event is reported once per window.
        // ponytail: 10-minute cache window; use a lead-table lookup if that proves too blunt.
        if ($email && ! Cache::add('meta-capi:'.$eventName.':'.sha1($email), true, 600)) {
            return;
        }

        $eventId = $this->clean($request->input('event_id'), 64);

        $payload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_source_url' => $this->sourceUrl($request),
            'action_source' => 'website',
            'user_data' => array_filter([
                'em' => $this->hash($email),
                'ph' => $this->hash($this->normalisePhone($data['phone'] ?? null)),
                'client_ip_address' => $request->ip(),
                'client_user_agent' => $request->userAgent(),
                'fbp' => $this->clean($request->input('fbp'), 255),
                'fbc' => $this->clean($request->input('fbc'), 500),
            ]),
            'custom_data' => array_filter([
                'content_name' => $data['project_name'] ?? null,
                'content_category' => $this->clean($request->input('property_type'), 100),
            ]),
        ];

        // No event_id means the browser could not supply one; this event is then
        // server-only and Meta cannot match it to the Pixel's report.
        if ($eventId !== null) {
            $payload['event_id'] = $eventId;
        }

        $body = ['data' => [$payload], 'access_token' => $token];
        if ($code = config('services.meta.capi_test_event_code')) {
            $body['test_event_code'] = $code;
        }

        $version = config('services.meta.graph_api_version');
        $url = "https://graph.facebook.com/{$version}/{$pixelId}/events";

        try {
            $response = Http::timeout(3)->asJson()->post($url, $body);

            if ($response->failed()) {
                $this->logFailure($eventName, $eventId, $response->status(), $response->body());
            }
        } catch (\Throwable $e) {
            $this->logFailure($eventName, $eventId, 0, $e->getMessage());
        }
    }

    /** Only a well-formed absolute URL is worth sending; otherwise fall back to the referer. */
    private function sourceUrl(Request $request): ?string
    {
        $url = $this->clean($request->input('event_source_url'), 2000);

        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return $this->clean($request->headers->get('referer'), 2000);
    }

    /** Scalar, trimmed, length-capped — or null. Never throws, never rejects the form. */
    private function clean(mixed $value, int $max): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function logFailure(string $eventName, ?string $eventId, int $status, string $body): void
    {
        // Never log plaintext email / phone here.
        Log::warning('Meta CAPI event failed', [
            'event_name' => $eventName,
            'event_id' => $eventId,
            'status' => $status,
            'response' => mb_substr($body, 0, 1000),
        ]);
    }

    private function normaliseEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    /** Digits only, with country code; Bangladesh numbers get 880 for the leading 0. */
    private function normalisePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '880')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '880'.substr($digits, 1);
        }
        // Local mobile typed without the leading 0, e.g. 1712345678.
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '880'.$digits;
        }

        return $digits;
    }

    private function hash(?string $value): ?string
    {
        return $value === null ? null : hash('sha256', $value);
    }
}
