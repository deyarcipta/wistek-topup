<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class WhatsappService
{
    protected $enabled;

    protected $apiUrl;

    protected $apiToken;

    protected $sessionId;

    public function __construct()
    {
        $this->enabled = Setting::get('whatsapp_enabled', '0') === '1';
        $this->apiUrl = Setting::get('whatsapp_api_url', 'http://localhost:2785/api');
        $this->apiToken = Setting::get('whatsapp_api_token');
        $this->sessionId = Setting::get('whatsapp_session_id', 'default');
    }

    /**
     * Send message via open-wa session API
     */
    public function sendMessage(string $to, string $message): bool
    {
        if (! $this->enabled) {
            logger()->info("WhatsApp notification disabled, skipped sending to {$to}");

            return false;
        }

        if (empty($this->apiUrl)) {
            logger()->error('WhatsApp Gateway URL is empty.');

            return false;
        }

        $formattedTo = $this->formatNumber($to);
        $baseUrl = rtrim($this->apiUrl, '/');
        $sendUrl = "{$baseUrl}/sessions/{$this->sessionId}/messages/send-text";

        try {
            $payload = [
                'chatId' => $formattedTo,
                'text' => $message,
            ];

            $request = Http::asJson();

            if (! empty($this->apiToken)) {
                $request = $request->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiToken,
                    'X-API-Key' => $this->apiToken,
                ]);
            }

            $response = $request->post($sendUrl, $payload);

            if ($response->successful()) {
                logger()->info("WhatsApp message successfully sent to {$formattedTo} via session {$this->sessionId}");

                return true;
            }

            logger()->error("WhatsApp Gateway failed to send to {$formattedTo}. Status: {$response->status()}, Response: {$response->body()}");

            return false;
        } catch (Exception $e) {
            logger()->error('WhatsApp Gateway error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Helper to format phone number to international wa-automate format
     */
    protected function formatNumber(string $number): string
    {
        // Remove non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Strip leading '62' if followed by '0' (e.g. 620812... -> 0812...)
        if (str_starts_with($number, '620')) {
            $number = substr($number, 2);
        }

        // Replace leading 0 with 62
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        // If it starts with 8 (meaning they omitted leading 0 or 62), prepend 62
        if (str_starts_with($number, '8')) {
            $number = '62'.$number;
        }

        // If it already has 62 but no @c.us suffix, append it
        if (! str_contains($number, '@c.us')) {
            $number .= '@c.us';
        }

        return $number;
    }
}
