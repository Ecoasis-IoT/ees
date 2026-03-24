<?php
/**
 * Callback Bootstrap
 * Loads the central config.php so every callback site can call
 *   $pdo = getDB('site_key');
 * instead of hardcoding credentials.
 */
require_once __DIR__ . '/../../config.php';

/**
 * Optionally verify incoming webhook requests.
 *
 * Controlled by .env / config.php constants:
 *   WEBHOOK_REQUIRE_SIGNATURE  — master on/off switch (default: false)
 *   WEBHOOK_IP_WHITELIST       — comma-separated IP list; empty = any IP allowed
 *   WEBHOOK_SECRET             — HMAC-SHA256 secret; empty = skip signature check
 *
 * The raw POST body must be passed in so the HMAC is computed over the
 * original bytes (before any json_decode).
 *
 * Terminates with HTTP 403 if a check fails.
 */
function verifyWebhookRequest(string $rawBody = ''): void
{
    // Master switch — default off so existing deployments are unaffected
    if (!WEBHOOK_REQUIRE_SIGNATURE) {
        return;
    }

    // ── IP whitelist ───────────────────────────────────────────────────────
    $whitelist = trim(WEBHOOK_IP_WHITELIST);
    if ($whitelist !== '') {
        $allowed  = array_map('trim', explode(',', $whitelist));
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        // Support X-Forwarded-For if behind a trusted proxy (optional)
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if ($forwarded !== '') {
                $clientIp = $forwarded;
            }
        }

        if (!in_array($clientIp, $allowed, true)) {
            error_log("Webhook blocked: IP {$clientIp} not in whitelist");
            http_response_code(403);
            exit;
        }
    }

    // ── HMAC-SHA256 signature ──────────────────────────────────────────────
    $secret = WEBHOOK_SECRET;
    if ($secret === '') {
        // Signature verification is required but no secret is configured — reject all requests.
        error_log("Webhook blocked: WEBHOOK_REQUIRE_SIGNATURE is true but WEBHOOK_SECRET is empty");
        http_response_code(403);
        exit;
    }

    // ChirpStack / generic: header is "X-Signature" (hex-encoded SHA256)
    $receivedSig = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $expectedSig = hash_hmac('sha256', $rawBody, $secret);

    if (!hash_equals($expectedSig, strtolower($receivedSig))) {
        error_log("Webhook blocked: invalid HMAC signature");
        http_response_code(403);
        exit;
    }
}
