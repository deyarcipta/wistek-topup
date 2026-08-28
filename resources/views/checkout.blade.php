@extends('layouts.app')

@section('title', 'Detail Transaksi #' . $transaction->invoice . ' - Wistek Topup')

@section('content')
<div class="container">
    <div class="invoice-wrapper">
        <!-- Back Navigation -->
        <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            @auth
                <a href="{{ url('/dashboard/transactions') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='#e28743';" onmouseout="this.style.color='var(--text-secondary)';">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            @else
                <a href="{{ url('/') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='#e28743';" onmouseout="this.style.color='var(--text-secondary)';">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                </a>
            @endauth
        </div>

        <!-- Header Info -->
        <div class="invoice-header">
            <div class="invoice-title">
                <h2>Invoice #{{ $transaction->invoice }}</h2>
                <p>Tanggal Transaksi: {{ $transaction->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <!-- Payment Status Badge -->
                <span class="badge badge-{{ $transaction->payment_status === 'paid' ? 'success' : ($transaction->payment_status === 'unpaid' ? 'pending' : 'failed') }}">
                    Pembayaran: {{ $transaction->payment_status }}
                </span>
                <!-- Topup Status Badge -->
                <span class="badge badge-{{ $transaction->topup_status === 'success' ? 'success' : ($transaction->topup_status === 'pending' || $transaction->topup_status === 'processing' ? 'pending' : 'failed') }}">
                    Status Topup: {{ $transaction->topup_status }}
                </span>
            </div>
        </div>

        <div class="invoice-card">
            
            <!-- Details -->
            <div class="invoice-row">
                <span class="invoice-label">Kategori Produk</span>
                <span class="invoice-value">{{ $transaction->category_name }}</span>
            </div>
            <div class="invoice-row">
                <span class="invoice-label">Item Layanan</span>
                <span class="invoice-value">{{ $transaction->product_name }}</span>
            </div>
            <div class="invoice-row">
                <span class="invoice-label">Tujuan / Target ID</span>
                <span class="invoice-value" style="color: var(--accent-blue); font-weight: 700;">{{ $transaction->target_no }}</span>
            </div>
            <div class="invoice-row">
                <span class="invoice-label">Metode Pembayaran</span>
                <span class="invoice-value" style="text-transform: uppercase;">{{ $transaction->payment_method }}</span>
            </div>
            
            <!-- Dynamic Payment Details Box -->
            @if($transaction->payment_status === 'unpaid')
                <div class="payment-box" style="text-align: center; padding: 2rem; background: rgba(255, 255, 255, 0.01); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1.5rem;">
                    
                    @if(isset($transaction->payment_details['expired_time']))
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Selesaikan pembayaran sebelum:</p>
                        <div class="timer" id="countdownTimer" data-expiry="{{ $transaction->payment_details['expired_time'] }}" style="font-size: 2rem; font-weight: 800; color: var(--accent-blue); margin-bottom: 1.5rem;">00:00:00</div>
                    @endif

                    @php
                        $hasContent = false;
                    @endphp

                    <!-- 1. Display QR Code if qr_url or qr_string is present -->
                    @if(isset($transaction->payment_details['qr_url']) && !empty($transaction->payment_details['qr_url']))
                        @php $hasContent = true; @endphp
                        <p style="font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Scan QRIS di bawah ini:</p>
                        <img src="{{ $transaction->payment_details['qr_url'] }}" alt="QRIS Code" class="qr-code-img" style="margin: 1rem auto; max-width: 250px; border: 8px solid #fff; border-radius: 8px; display: block;">
                        <p style="font-size: 0.85rem; color: var(--text-secondary);">Mendukung GoPay, OVO, Dana, LinkAja, ShopeePay & M-Banking</p>
                    @elseif(isset($transaction->payment_details['qr_string']) && !empty($transaction->payment_details['qr_string']))
                        @php $hasContent = true; @endphp
                        <p style="font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Scan QRIS di bawah ini:</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($transaction->payment_details['qr_string']) }}" alt="QRIS Code" class="qr-code-img" style="margin: 1rem auto; max-width: 250px; border: 8px solid #fff; border-radius: 8px; display: block;">
                        <p style="font-size: 0.85rem; color: var(--text-secondary);">Mendukung GoPay, OVO, Dana, LinkAja, ShopeePay & M-Banking</p>
                    @endif

                    <!-- 2. Display Virtual Account / Payment Code if present -->
                    @if(isset($transaction->payment_details['pay_code']) && !empty($transaction->payment_details['pay_code']))
                        @php $hasContent = true; @endphp
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Nomor Virtual Account / Kode Bayar:</p>
                        <span style="font-size: 2rem; font-weight: 800; color: var(--text-primary); letter-spacing: 0.05em; display: block; margin-bottom: 1rem;">
                            {{ $transaction->payment_details['pay_code'] }}
                        </span>
                        <button class="copy-btn" onclick="copyText('{{ $transaction->payment_details['pay_code'] }}')" style="background: var(--accent-blue); color: #fff; border: none; padding: 0.5rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600; margin-bottom: 1rem;">
                            <i class="fa-solid fa-copy"></i> Salin Kode
                        </button>
                    @endif

                    <!-- 3. Display Payment URL redirect button if payment_url is present -->
                    @if(isset($transaction->payment_details['payment_url']) && !empty($transaction->payment_details['payment_url']))
                        @php $hasContent = true; @endphp
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--border-color);">
                            <p style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 1rem; font-weight: 600;">Klik tombol di bawah untuk membayar melalui portal Duitku:</p>
                            <a href="{{ $transaction->payment_details['payment_url'] }}" target="_blank" class="btn-checkout" style="display: inline-block; text-decoration: none; text-align: center; width: auto; padding: 0.75rem 2rem; background: #e28743; color: #fff; font-weight: 700; border-radius: 8px; font-size: 1rem; transition: background 0.2s;">
                                <i class="fa-solid fa-external-link" style="margin-right: 0.5rem;"></i> Bayar Sekarang (Duitku)
                            </a>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.75rem;">Anda akan diarahkan ke halaman pembayaran aman Duitku.</p>
                        </div>
                    @endif

                    @if(!$hasContent)
                        <p style="color: var(--text-secondary);">Instruksi pembayaran tidak ditemukan atau pembayaran kadaluarsa.</p>
                    @endif

                </div>
            @endif

            <!-- Price Breakdown -->
            @if(isset($transaction->payment_details['base_price']))
                <div class="invoice-row" style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <span class="invoice-label">Harga Produk</span>
                    <span class="invoice-value">Rp {{ number_format($transaction->payment_details['base_price'], 0, ',', '.') }}</span>
                </div>
                @if($transaction->discount_amount > 0)
                    <div class="invoice-row" style="font-size: 0.9rem; color: #10b981;">
                        <span class="invoice-label">Diskon Voucher ({{ $transaction->voucher_code }})</span>
                        <span class="invoice-value">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaction->points_used > 0)
                    <div class="invoice-row" style="font-size: 0.9rem; color: #8b5cf6;">
                        <span class="invoice-label">Potongan Poin Loyalti</span>
                        <span class="invoice-value">-Rp {{ number_format($transaction->points_used, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaction->points_earned > 0)
                    <div class="invoice-row" style="font-size: 0.9rem; color: #10b981;">
                        <span class="invoice-label">Estimasi Poin Diperoleh</span>
                        <span class="invoice-value">+{{ number_format($transaction->points_earned, 0, ',', '.') }} Pts</span>
                    </div>
                @endif
                <div class="invoice-row" style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    <span class="invoice-label">Biaya Admin</span>
                    <span class="invoice-value">Rp {{ number_format($transaction->payment_details['admin_fee'] ?? 0, 0, ',', '.') }}</span>
                </div>
            @endif

            <!-- Invoice Total -->
            <div class="invoice-row" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                <span class="invoice-label" style="font-weight: 700; font-size: 1.1rem;">Total Pembayaran</span>
                <span class="invoice-value highlight">Rp {{ number_format($transaction->price, 0, ',', '.') }}</span>
            </div>

            <!-- Delivery Note if Success -->
            @if($transaction->topup_status === 'success' && $transaction->note)
                <div style="margin-top: 2rem; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 1.5rem;">
                    <h4 style="color: var(--success); margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-check"></i> Pesanan Berhasil Dikirim</h4>
                    <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Serial Number (SN) / Bukti Pengiriman:</p>
                    <code style="display: block; font-family: monospace; font-size: 1.1rem; background: rgba(0,0,0,0.2); padding: 0.5rem 1rem; border-radius: 8px; color: var(--text-primary); text-align: center; border: 1px solid var(--border-color);">
                        {{ $transaction->note }}
                    </code>
                </div>
            @endif

            <!-- Payment Instructions Accordion -->
            @if($transaction->payment_status === 'unpaid' && isset($transaction->payment_details['instructions']))
                <div class="instruction-accordion">
                    <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;"><i class="fa-solid fa-list-check"></i> Cara Pembayaran</h3>
                    @foreach($transaction->payment_details['instructions'] as $instruction)
                        <div class="accordion-item">
                            <div class="accordion-title" onclick="toggleAccordion(this)">
                                {{ $instruction['title'] }}
                            </div>
                            <div class="accordion-content">
                                <ol style="padding-left: 1.25rem;">
                                    @foreach($instruction['steps'] as $step)
                                        <li style="margin-bottom: 0.5rem;">{!! $step !!}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    // Copy Text Helper
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Kode pembayaran berhasil disalin!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // Toggle Accordion Helper
    function toggleAccordion(element) {
        const item = element.parentElement;
        item.classList.toggle('open');
    }

    // Countdown Timer
    const timerElement = document.getElementById('countdownTimer');
    if (timerElement) {
        const expiryTimestamp = parseInt(timerElement.getAttribute('data-expiry'));
        
        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const distance = expiryTimestamp - now;
            
            if (distance < 0) {
                timerElement.innerHTML = "WAKTU HABIS / KADALUARSA";
                timerElement.style.color = "var(--danger)";
                clearInterval(timerInterval);
                return;
            }
            
            const hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
            const minutes = Math.floor((distance % (60 * 60)) / 60);
            const seconds = Math.floor(distance % 60);
            
            const pad = (num) => String(num).padStart(2, '0');
            timerElement.innerHTML = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
        
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    }

    // Real-time Status Polling (Every 5 seconds)
    const invoice = "{{ $transaction->invoice }}";
    
    function checkStatus() {
        fetch(`/api/transaction-status/${invoice}`)
            .then(response => response.json())
            .then(data => {
                if (data.payment_status !== "{{ $transaction->payment_status }}" || data.topup_status !== "{{ $transaction->topup_status }}") {
                    // Status changed, reload the page to update the UI
                    window.location.reload();
                }
            })
            .catch(err => console.error("Error polling transaction status:", err));
    }

    // Only poll if the transaction is not fully completed yet (either unpaid or topup pending/processing)
    const isCompleted = ("{{ $transaction->payment_status }}" === "paid" && "{{ $transaction->topup_status }}" === "success") 
        || "{{ $transaction->payment_status }}" === "failed" 
        || "{{ $transaction->payment_status }}" === "expired";
        
    if (!isCompleted) {
        setInterval(checkStatus, 5000);
    }
</script>
@endsection
