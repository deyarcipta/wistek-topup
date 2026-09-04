<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\DuitkuService;
use App\Services\MidtransService;
use App\Services\TripayService;
use App\Services\WhatsappService;
use App\Services\XenditService;
use Exception;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    /**
     * Handle webhook callback from Duitku
     */
    public function duitkuCallback(Request $request, DuitkuService $duitku, DigiflazzService $digiflazz)
    {
        $merchantCode = $request->input('merchantCode') ?? '';
        $amount = $request->input('amount') ?? '';
        $merchantOrderId = $request->input('merchantOrderId') ?? '';
        $signature = $request->input('signature') ?? '';
        $resultCode = $request->input('resultCode') ?? '';
        $reference = $request->input('reference') ?? '';

        // 1. Verify Callback Signature
        $isValid = $duitku->validateCallbackSignature($merchantCode, $amount, $merchantOrderId, $signature);
        if (! $isValid) {
            return response('Invalid signature', 400)->header('Content-Type', 'text/plain');
        }

        // 2. Find transaction
        $transaction = Transaction::where('invoice', $merchantOrderId)->first();
        if (! $transaction) {
            return response('Transaction not found', 404)->header('Content-Type', 'text/plain');
        }

        // 3. Process payment status update based on resultCode
        if ($resultCode === '00') {
            $this->fulfillPaidTransaction($transaction, $reference, $digiflazz);
        } else {
            $transaction->payment_status = 'failed';
            $transaction->topup_status = 'failed';
            $transaction->note = 'Pembayaran gagal (Duitku Result Code: '.$resultCode.')';
            $transaction->save();

            try {
                if ($transaction->customer_phone) {
                    $whatsapp = new WhatsappService;
                    $whatsapp->sendMessage(
                        $transaction->customer_phone,
                        "Pembayaran untuk Invoice *{$transaction->invoice}* GAGAL atau kedaluwarsa. Silakan lakukan pemesanan ulang jika ingin bertransaksi."
                    );
                }
            } catch (Exception $e) {
                logger()->error('WhatsApp payment failed notification failed: '.$e->getMessage());
            }
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Handle webhook callback from Midtrans
     */
    public function midtransCallback(Request $request, MidtransService $midtrans, DigiflazzService $digiflazz)
    {
        $orderId = $request->input('order_id') ?? '';
        $statusCode = $request->input('status_code') ?? '';
        $grossAmount = $request->input('gross_amount') ?? '';
        $signatureKey = $request->input('signature_key') ?? '';
        $transactionStatus = $request->input('transaction_status') ?? '';
        $fraudStatus = $request->input('fraud_status') ?? '';

        if (! $midtrans->validateCallbackSignature($orderId, (string) $statusCode, (string) $grossAmount, $signatureKey)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $transaction = Transaction::where('invoice', $orderId)->first();
        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                $transaction->payment_status = 'unpaid';
                $transaction->note = 'Fraud challenge by Midtrans';
                $transaction->save();
            } else {
                $this->fulfillPaidTransaction($transaction, $request->input('transaction_id'), $digiflazz);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $transaction->payment_status = 'failed';
            $transaction->topup_status = 'failed';
            $transaction->note = 'Pembayaran dibatalkan/kedaluwarsa (Midtrans: '.$transactionStatus.')';
            $transaction->save();
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Handle webhook callback from Xendit
     */
    public function xenditCallback(Request $request, XenditService $xendit, DigiflazzService $digiflazz)
    {
        $tokenHeader = $request->header('x-callback-token');
        if (! $xendit->validateCallbackToken($tokenHeader)) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $externalId = $request->input('external_id') ?? '';
        $status = strtoupper((string) ($request->input('status') ?? ''));

        $transaction = Transaction::where('invoice', $externalId)->first();
        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            $this->fulfillPaidTransaction($transaction, $request->input('id'), $digiflazz);
        } elseif ($status === 'EXPIRED') {
            $transaction->payment_status = 'failed';
            $transaction->topup_status = 'failed';
            $transaction->note = 'Invoice Xendit kedaluwarsa';
            $transaction->save();
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Handle webhook callback from Tripay
     */
    public function tripayCallback(Request $request, TripayService $tripay, DigiflazzService $digiflazz)
    {
        $jsonPayload = $request->getContent();
        $signatureHeader = $request->header('X-Callback-Signature');

        if (! $tripay->validateCallbackSignature($jsonPayload, $signatureHeader)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $data = json_decode($jsonPayload, true);
        $merchantRef = $data['merchant_ref'] ?? '';
        $status = strtoupper((string) ($data['status'] ?? ''));

        $transaction = Transaction::where('invoice', $merchantRef)->first();
        if (! $transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($status === 'PAID') {
            $this->fulfillPaidTransaction($transaction, $data['reference'] ?? null, $digiflazz);
        } elseif (in_array($status, ['EXPIRED', 'FAILED', 'REFUND'])) {
            $transaction->payment_status = 'failed';
            $transaction->topup_status = 'failed';
            $transaction->note = 'Pembayaran Tripay '.$status;
            $transaction->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Fulfill a paid transaction and trigger automatic Digiflazz topup
     */
    private function fulfillPaidTransaction(Transaction $transaction, ?string $reference, DigiflazzService $digiflazz): void
    {
        if ($transaction->payment_status === 'paid') {
            return;
        }

        $transaction->payment_status = 'paid';
        $transaction->topup_status = 'processing';
        if ($reference) {
            $transaction->reference = $reference;
        }
        $transaction->save();

        try {
            $targetNo = str_replace([' ', '(', ')', '-'], '', $transaction->target_no);

            $dfResponse = $digiflazz->orderTopup(
                $transaction->invoice,
                $transaction->sku,
                $targetNo
            );

            if ($dfResponse['success']) {
                $dfData = $dfResponse['data'];
                $dfStatus = strtolower($dfData['status'] ?? 'pending');

                if ($dfStatus === 'sukses') {
                    $transaction->topup_status = 'success';
                    $transaction->note = $dfData['sn'] ?? 'Top-up sukses';
                    $transaction->save();
                    $this->creditPointsForSuccessfulTransaction($transaction);

                    try {
                        if ($transaction->customer_phone) {
                            $whatsapp = new WhatsappService;
                            $whatsapp->sendMessage(
                                $transaction->customer_phone,
                                "Top-up BERHASIL dikirim! 🎉\n\n*Invoice*: {$transaction->invoice}\n*Produk*: {$transaction->category_name} - {$transaction->product_name}\n*Target*: {$transaction->target_no}\n*Serial Number (SN)*: {$transaction->note}\n\nTerima kasih telah berbelanja di Wistek Topup!"
                            );
                        }
                    } catch (Exception $ex) {
                        logger()->error('WhatsApp topup success notification failed: '.$ex->getMessage());
                    }
                } elseif ($dfStatus === 'gagal') {
                    $transaction->topup_status = 'failed';
                    $transaction->note = $dfData['message'] ?? 'Gagal dari provider';

                    try {
                        if ($transaction->customer_phone) {
                            $whatsapp = new WhatsappService;
                            $whatsapp->sendMessage(
                                $transaction->customer_phone,
                                "Mohon maaf, transaksi top-up untuk Invoice *{$transaction->invoice}* GAGAL diproses oleh provider.\nDetail: {$transaction->note}\n\nSilakan hubungi Customer Service kami untuk bantuan pengembalian dana."
                            );
                        }
                    } catch (Exception $ex) {
                        logger()->error('WhatsApp topup failed notification failed: '.$ex->getMessage());
                    }
                } else {
                    $transaction->topup_status = 'processing';
                    $transaction->note = 'Sedang diproses oleh provider';
                }
            } else {
                $transaction->topup_status = 'processing';
                $transaction->note = $dfResponse['message'] ?? 'Gagal menempatkan pesanan';
            }
        } catch (Exception $e) {
            logger()->error('Auto topup trigger failed for '.$transaction->invoice.': '.$e->getMessage());
            $transaction->topup_status = 'processing';
            $transaction->note = 'Eror pemicu otomatis: '.$e->getMessage();
        }

        $transaction->save();
    }

    /**
     * Handle webhook callback from Digiflazz
     */
    public function digiflazzCallback(Request $request, DigiflazzService $digiflazz)
    {
        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'message' => 'Digiflazz Webhook Endpoint is Active.',
            ]);
        }

        $signature = $request->header('X-Digiflazz-Delivery-Signature');
        $rawJson = $request->getContent();

        // 1. Verify Signature
        if ($signature && ! $digiflazz->validateCallback($rawJson, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 400);
        }

        $body = json_decode($rawJson, true);
        $data = $body['data'] ?? [];

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Empty data payload',
            ], 400);
        }

        $refId = $data['ref_id'] ?? '';
        $status = strtolower($data['status'] ?? '');
        $sn = $data['sn'] ?? '';
        $message = $data['message'] ?? '';

        // 2. Find transaction
        $transaction = Transaction::where('invoice', $refId)->first();
        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $statusBefore = $transaction->topup_status;

        // 3. Update status based on Digiflazz report
        if ($status === 'sukses') {
            $transaction->topup_status = 'success';
            $transaction->note = $sn ?: 'Sukses';
            $transaction->save();
            $this->creditPointsForSuccessfulTransaction($transaction);
        } elseif ($status === 'gagal') {
            $transaction->topup_status = 'failed';
            $transaction->note = $message ?: 'Ditolak oleh provider';
        } elseif ($status === 'pending') {
            $transaction->topup_status = 'processing';
            $transaction->note = 'Masih diproses';
        }

        $transaction->save();

        // 4. Send WhatsApp Notification for status updates (if status actually changed)
        if ($statusBefore !== $transaction->topup_status && $transaction->customer_phone) {
            try {
                $whatsapp = new WhatsappService;
                if ($transaction->topup_status === 'success') {
                    $whatsapp->sendMessage(
                        $transaction->customer_phone,
                        "Top-up BERHASIL dikirim! 🎉\n\n*Invoice*: {$transaction->invoice}\n*Produk*: {$transaction->category_name} - {$transaction->product_name}\n*Target*: {$transaction->target_no}\n*Serial Number (SN)*: {$transaction->note}\n\nTerima kasih telah berbelanja di Wistek Topup!"
                    );
                } elseif ($transaction->topup_status === 'failed') {
                    $whatsapp->sendMessage(
                        $transaction->customer_phone,
                        "Mohon maaf, transaksi top-up untuk Invoice *{$transaction->invoice}* GAGAL diproses oleh provider.\nDetail: {$transaction->note}\n\nSilakan hubungi Customer Service kami untuk bantuan pengembalian dana."
                    );
                }
            } catch (Exception $e) {
                logger()->error('Failed to send Digiflazz status WhatsApp notification: '.$e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Simulate a successful payment callback locally for testing
     */
    public function simulatePaid($invoice, DigiflazzService $digiflazz)
    {
        $transaction = Transaction::where('invoice', $invoice)->firstOrFail();

        if ($transaction->payment_status === 'paid') {
            return 'Transaksi ini sudah ditandai PAID sebelumnya.';
        }

        $transaction->payment_status = 'paid';
        $transaction->topup_status = 'processing';
        $transaction->save();

        // Trigger Digiflazz order
        try {
            $targetNo = str_replace([' ', '(', ')', '-'], '', $transaction->target_no);

            // Check if Digiflazz credentials are not set (default values)
            $apiKey = Setting::get('digiflazz_api_key', env('DIGIFLAZZ_API_KEY'));
            $isUnconfigured = $apiKey === 'YOUR_DIGIFLAZZ_API_KEY' || empty($apiKey);

            if ($isUnconfigured) {
                // Mock success for local testing
                $transaction->topup_status = 'success';
                $transaction->note = 'SN-MOCK-'.rand(10000000, 99999999);
                $transaction->save();
                $this->creditPointsForSuccessfulTransaction($transaction);

                // Send WhatsApp notification for simulated topup success (mock)
                try {
                    if ($transaction->customer_phone) {
                        $whatsapp = new WhatsappService;
                        $whatsapp->sendMessage(
                            $transaction->customer_phone,
                            "Top-up BERHASIL dikirim! 🎉\n\n*Invoice*: {$transaction->invoice}\n*Produk*: {$transaction->category_name} - {$transaction->product_name}\n*Target*: {$transaction->target_no}\n*Serial Number (SN)*: {$transaction->note}\n\nTerima kasih telah berbelanja di Wistek Topup! [SIMULASI]"
                        );
                    }
                } catch (Exception $ex) {
                    logger()->error('WhatsApp simulation topup mock success notification failed: '.$ex->getMessage());
                }

                return 'Simulasi Pembayaran Berhasil!<br>Kredensial Digiflazz belum dikonfigurasi, sistem mensimulasikan order sukses dengan serial number buatan: <strong>'.$transaction->note.'</strong><br><br>Kembali ke halaman transaksi untuk melihat pembaruan otomatis.';
            }

            $dfResponse = $digiflazz->orderTopup(
                $transaction->invoice,
                $transaction->sku,
                $targetNo
            );

            if ($dfResponse['success']) {
                $dfData = $dfResponse['data'];
                $dfStatus = strtolower($dfData['status'] ?? 'pending');

                if ($dfStatus === 'sukses') {
                    $transaction->topup_status = 'success';
                    $transaction->note = $dfData['sn'] ?? 'Simulasi sukses';
                    $transaction->save();
                    $this->creditPointsForSuccessfulTransaction($transaction);

                    // Send WhatsApp notification for simulated topup success (live)
                    try {
                        if ($transaction->customer_phone) {
                            $whatsapp = new WhatsappService;
                            $whatsapp->sendMessage(
                                $transaction->customer_phone,
                                "Top-up BERHASIL dikirim! 🎉\n\n*Invoice*: {$transaction->invoice}\n*Produk*: {$transaction->category_name} - {$transaction->product_name}\n*Target*: {$transaction->target_no}\n*Serial Number (SN)*: {$transaction->note}\n\nTerima kasih telah berbelanja di Wistek Topup! [SIMULASI]"
                            );
                        }
                    } catch (Exception $ex) {
                        logger()->error('WhatsApp simulation topup success notification failed: '.$ex->getMessage());
                    }
                } elseif ($dfStatus === 'gagal') {
                    $transaction->topup_status = 'failed';
                    $transaction->note = $dfData['message'] ?? 'Gagal dari provider';

                    // Send WhatsApp notification for simulated topup failed
                    try {
                        if ($transaction->customer_phone) {
                            $whatsapp = new WhatsappService;
                            $whatsapp->sendMessage(
                                $transaction->customer_phone,
                                "Mohon maaf, transaksi top-up untuk Invoice *{$transaction->invoice}* GAGAL diproses oleh provider.\nDetail: {$transaction->note}\n\nSilakan hubungi Customer Service kami untuk bantuan pengembalian dana. [SIMULASI]"
                            );
                        }
                    } catch (Exception $ex) {
                        logger()->error('WhatsApp simulation topup failed notification failed: '.$ex->getMessage());
                    }
                } else {
                    $transaction->topup_status = 'processing';
                    $transaction->note = 'Sedang diproses oleh provider (simulasi)';
                }
            } else {
                $transaction->topup_status = 'failed';
                $transaction->note = $dfResponse['message'] ?? 'Gagal menghubungi provider';

                // Send WhatsApp notification for simulated topup failed (failed to reach provider)
                try {
                    if ($transaction->customer_phone) {
                        $whatsapp = new WhatsappService;
                        $whatsapp->sendMessage(
                            $transaction->customer_phone,
                            "Mohon maaf, transaksi top-up untuk Invoice *{$transaction->invoice}* GAGAL diproses oleh provider.\nDetail: {$transaction->note}\n\nSilakan hubungi Customer Service kami untuk bantuan pengembalian dana. [SIMULASI]"
                        );
                    }
                } catch (Exception $ex) {
                    logger()->error('WhatsApp simulation topup failed provider notification failed: '.$ex->getMessage());
                }
            }
        } catch (Exception $e) {
            $transaction->topup_status = 'failed';
            $transaction->note = 'Eror simulasi: '.$e->getMessage();
        }

        $transaction->save();

        return 'Simulasi Pembayaran Berhasil!<br>Invoice: <strong>'.$invoice.'</strong><br>Status top-up: <strong>'.$transaction->topup_status.'</strong> (SN: '.$transaction->note.')<br><br>Periksa halaman transaksi untuk melihat pembaruan otomatis.';
    }

    /**
     * Credit points to the customer and their referrer upon successful transaction (paid + success)
     */
    private function creditPointsForSuccessfulTransaction(Transaction $transaction): void
    {
        $transaction->creditPointsIfEligible();
    }
}
