<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Services\DuitkuService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    /**
     * Display landing page with categories
     */
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // Calculate popular categories based on transaction count
        $popularCategoryNames = Transaction::select('category_name')
            ->selectRaw('count(*) as count')
            ->groupBy('category_name')
            ->orderByDesc('count')
            ->limit(4)
            ->pluck('category_name')
            ->toArray();

        $popularCategories = Category::where('status', true)
            ->whereIn('name', $popularCategoryNames)
            ->get()
            ->sortBy(fn ($c) => array_search($c->name, $popularCategoryNames))
            ->values();

        // Fallback to first 4 categories if no transactions exist yet
        if ($popularCategories->isEmpty()) {
            $popularCategories = Category::where('status', true)->limit(4)->get();
        }

        $categories = Category::where('status', true)->get();

        return view('home', compact('banners', 'popularCategories', 'categories'));
    }

    /**
     * Display a specific category's detail / products form
     */
    public function showCategory($slug, DuitkuService $duitku)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $products = Product::with('subCategory')
            ->where('category_id', $category->id)
            ->where('status', true)
            ->orderBy('price_sell', 'asc')
            ->get();

        $paymentChannels = $duitku->getPaymentChannels();

        return view('product', compact('category', 'products', 'paymentChannels'));
    }

    /**
     * Handle topup form checkout and request Duitku billing
     */
    public function checkout(Request $request, DuitkuService $duitku)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|string',
            'target_id' => 'required|string',
            'zone_id' => 'nullable|string',
            'customer_phone' => 'required|string',
            'voucher_code' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        // Check if product is active; if not, reject the checkout
        if ($product->status !== 1) {
            return back()->withErrors(['error' => 'Produk ini sedang tidak aktif / tidak tersedia di Digiflazz.']);
        }
        $category = Category::findOrFail($request->category_id);

        // Calculate dynamic fee from payment methods
        $paymentChannels = $duitku->getPaymentChannels();
        $feeFlat = 0;
        $feePercent = 0;

        foreach ($paymentChannels as $channel) {
            if ($channel['code'] === $request->payment_method) {
                $feeFlat = (int) ($channel['fee_flat'] ?? 0);
                $feePercent = (float) ($channel['fee_percent'] ?? 0);
                break;
            }
        }

        // Add fallback manual calculations for local fallback channels
        if ($request->payment_method === 'QRIS' && count($paymentChannels) === 0) {
            $feePercent = 0.7;
        } elseif (in_array($request->payment_method, ['BCAVA', 'MANDIRIVA']) && count($paymentChannels) === 0) {
            $feeFlat = 1500;
        }

        $basePrice = (int) $product->price_sell;

        // Apply voucher discount if valid
        $discountAmount = 0;
        $voucherCode = null;

        if ($request->filled('voucher_code')) {
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            if ($voucher && $voucher->isValid()) {
                $discountAmount = (int) $voucher->calculateDiscount($basePrice);
                $voucherCode = $voucher->code;
            }
        }

        $calculatedFee = $feeFlat;
        if ($feePercent > 0) {
            $calculatedFee += (int) round((max(0, $basePrice - $discountAmount) * $feePercent) / 100);
        }

        $totalPrice = max(0, $basePrice - $discountAmount) + $calculatedFee;

        // Points deduction and reward calculations
        $pointsToUse = 0;
        $pointsEarned = 0;
        $userId = null;

        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $pointsEarned = (int) ($basePrice * 0.01); // 1% points reward

            if ($request->boolean('use_points') && $user->points_balance > 0) {
                // Keep at least Rp 1,000 for payment gateway processing
                $pointsToUse = min($user->points_balance, max(0, $totalPrice - 1000));
                $totalPrice = $totalPrice - $pointsToUse;
            }
        } else {
            // Guest checkout: check if customer phone is registered as a member
            $digits = preg_replace('/[^0-9]/', '', $request->customer_phone);
            if (! empty($digits)) {
                if (str_starts_with($digits, '620')) {
                    $digits = substr($digits, 2);
                }
                if (str_starts_with($digits, '62')) {
                    $digits = substr($digits, 2);
                }
                if (str_starts_with($digits, '0')) {
                    $digits = substr($digits, 1);
                }

                $basePhone = $digits;
                $variants = [
                    $basePhone,
                    '0'.$basePhone,
                    '62'.$basePhone,
                ];

                $existingUser = User::whereIn('phone', $variants)->first();
                if ($existingUser) {
                    $userId = $existingUser->id;
                    $pointsEarned = (int) ($basePrice * 0.01);
                }
            }
        }

        // Generate unique Invoice ID
        $invoice = 'INV-'.date('YmdHis').rand(100, 999);

        // Format target user ID (e.g. for Game: ID + Zone ID)
        $target = $request->target_id;
        if ($request->filled('zone_id')) {
            $target .= ' ('.$request->zone_id.')';
        }

        // Send payment request to Duitku with total price
        $productName = $category->name.' - '.$product->name;

        $duitkuResponse = $duitku->createTransaction(
            $invoice,
            $productName,
            $totalPrice,
            $request->payment_method,
            $request->customer_phone
        );

        if (! $duitkuResponse['success']) {
            return back()->withErrors(['error' => $duitkuResponse['message']]);
        }

        $duitkuData = $duitkuResponse['data'];
        $duitkuData['base_price'] = $basePrice;
        $duitkuData['admin_fee'] = $calculatedFee;
        $duitkuData['discount_amount'] = $discountAmount;
        $duitkuData['voucher_code'] = $voucherCode;
        $duitkuData['points_used'] = $pointsToUse;

        // Save transaction to DB
        $transaction = Transaction::create([
            'user_id' => $userId,
            'invoice' => $invoice,
            'reference' => $duitkuData['reference'] ?? null,
            'category_name' => $category->name,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'target_no' => $target,
            'customer_phone' => $request->customer_phone,
            'price' => $totalPrice,
            'voucher_code' => $voucherCode,
            'discount_amount' => $discountAmount,
            'points_used' => $pointsToUse,
            'points_earned' => $pointsEarned,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
            'payment_details' => $duitkuData,
        ]);

        // Deduct points balance from member account
        if ($pointsToUse > 0 && Auth::check()) {
            Auth::user()->decrementPoints($pointsToUse, "Belanja Potong Poin: {$invoice}", $transaction->id);
        }

        // Increment voucher usage
        if ($voucherCode) {
            Voucher::where('code', $voucherCode)->increment('used_count');
        }

        // Trigger WhatsApp Notification for Pending Payment
        try {
            $whatsapp = new WhatsappService;
            $payCodeText = isset($duitkuData['pay_code']) ? "\n*Kode VA / Bayar*: {$duitkuData['pay_code']}" : '';
            $transactionUrl = url('/transaction/'.$invoice);
            $paymentUrlText = "\n*Link Pembayaran*: {$transactionUrl}";

            $message = "Halo, terima kasih telah melakukan pemesanan di Wistek Topup.

*Detail Transaksi*:
*Invoice*: {$invoice}
*Produk*: {$category->name} - {$product->name}
*Target*: {$target}
*Total Bayar*: Rp ".number_format($totalPrice, 0, ',', '.')."
*Metode*: {$request->payment_method}{$payCodeText}{$paymentUrlText}

Silakan lakukan pembayaran sebelum waktu habis.
Terima kasih!";

            $whatsapp->sendMessage($request->customer_phone, $message);
        } catch (\Exception $e) {
            logger()->error('Failed to send WhatsApp pending notification: '.$e->getMessage());
        }

        return redirect('/transaction/'.$invoice);
    }

    /**
     * Display a specific transaction status / details
     */
    public function showTransaction($invoice)
    {
        $transaction = Transaction::where('invoice', $invoice)->firstOrFail();

        return view('checkout', compact('transaction'));
    }

    /**
     * REST API for Frontend dynamic status polling
     */
    public function apiStatus($invoice)
    {
        $transaction = Transaction::where('invoice', $invoice)->first();

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'invoice' => $transaction->invoice,
            'payment_status' => $transaction->payment_status,
            'topup_status' => $transaction->topup_status,
        ]);
    }

    /**
     * Show search transaction page
     */
    public function showHistoryForm()
    {
        return view('history');
    }

    /**
     * Handle search transaction submit
     */
    public function checkHistory(Request $request)
    {
        $invoice = trim($request->invoice);
        $transaction = Transaction::where('invoice', $invoice)->first();

        if (! $transaction) {
            return back()->with('error', 'Kode invoice tidak ditemukan! Silakan periksa kembali.');
        }

        return redirect('/transaction/'.$transaction->invoice);
    }

    /**
     * Validate voucher code and calculate discount
     */
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'product_id' => 'required|exists:products,id',
        ]);

        $voucher = Voucher::where('code', $request->code)->first();

        if (! $voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        if (! $voucher->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher sudah tidak berlaku atau kuota habis.',
            ]);
        }

        $product = Product::findOrFail($request->product_id);
        $discount = $voucher->calculateDiscount($product->price_sell);

        return response()->json([
            'success' => true,
            'code' => $voucher->code,
            'discount' => $discount,
            'formatted_discount' => 'Rp '.number_format($discount, 0, ',', '.'),
        ]);
    }
}
