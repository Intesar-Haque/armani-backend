<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetaConversionsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Server-side relay for the optional click events (Section 4 of the handover).
 *
 * These have no form behind them, so the browser asks the server to report them.
 * The GTM Pixel tag reports the same click from the browser with the same
 * event_id, and Meta dedupes the pair.
 *
 * This endpoint writes into the ad account, so it is deliberately narrow:
 * only the whitelisted events below, and no user data is accepted from the
 * browser — identifiers are derived server-side from the request itself.
 */
class TrackController extends Controller
{
    /**
     * dataLayer event => Meta event name. Both sides must agree on the name or
     * Meta will not dedupe, so 'brochure_download' must match the custom event
     * name used by the GTM tag.
     */
    private const EVENTS = [
        'phone_click' => 'Contact',
        'whatsapp_click' => 'Contact',
        'brochure_download' => 'BrochureDownload',
    ];

    public function store(Request $request, MetaConversionsApi $meta): Response
    {
        $data = $request->validate([
            'event' => ['required', 'string', Rule::in(array_keys(self::EVENTS))],
            'project_name' => ['nullable', 'string', 'max:255'],
        ]);

        // No email or phone exists for a click, so only fbp/fbc/IP/user-agent are
        // sent. Match quality is lower than for a form — that is why these are optional.
        $meta->send($request, self::EVENTS[$data['event']], [
            'project_name' => $data['project_name'] ?? null,
        ]);

        return response()->noContent();
    }
}
