@extends('layouts.app')

@section('title', 'Kebijakan Pengembalian Dana (Refund & Cancellation Policy) - Wistek Topup')

@section('content')
<div class="container" style="padding: 3.5rem 1rem 5rem;">
    <div style="max-width: 900px; margin: 0 auto; background: #0f1117; border: 1px solid var(--border-color); border-radius: 16px; padding: 2.5rem; color: var(--text-primary); font-family: 'Outfit', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
        
        <div style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <span style="background: rgba(226, 135, 67, 0.15); color: #e28743; border: 1px solid rgba(226, 135, 67, 0.3); padding: 0.3rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; tracking: 0.05em;">
                Dokumen Legalitas Digital
            </span>
            <h1 style="font-size: 2rem; font-weight: 800; color: #ffffff; margin-top: 0.75rem; margin-bottom: 0.5rem;">
                Kebijakan Pengembalian Dana (Refund & Cancellation Policy)
            </h1>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">
                Terakhir diperbarui: {{ date('d F Y') }} &bull; Berlaku untuk seluruh transaksi di Wistek Topup
            </p>
        </div>

        <div style="line-height: 1.8; font-size: 0.95rem; color: #d1d5db; display: flex; flex-direction: column; gap: 1.75rem;">
            
            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-info"></i> 1. Ketentuan Umum Transaksi
                </h3>
                <p>
                    Wistek Topup menyediakan layanan penjualan produk digital (Top-up Game, Voucher, dan PPOB) secara otomatis 24 jam nonstop. Karena sifat produk digital yang langsung diproses dan dikirimkan secara instan ke ID Akun / Nomor Target setelah pembayaran terkonfirmasi, maka seluruh transaksi yang telah berhasil diproses oleh sistem **tidak dapat dibatalkan secara sepihak oleh pengguna**.
                </p>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-rotate-left"></i> 2. Kondisi yang Berhak Mendapatkan Pengembalian Dana (Refund)
                </h3>
                <p>Pengembalian dana (Refund) **HANYA** dapat diajukan dan diproses apabila memenuhi salah satu dari kondisi berikut:</p>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><strong>Kegagalan Sistem / Provider:</strong> Pembayaran telah dinyatakan Lunas (*Paid*), namun produk gagal terkirim oleh provider dan status transaksi dinyatakan <em>FAILED / GAGAL</em> oleh sistem Wistek Topup.</li>
                    <li><strong>Stok Produk Habis:</strong> Pembayaran berhasil diproses tetapi stok nominal/voucher di provider mengalami kekosongan permanen yang tidak dapat dipenuhi dalam kurun waktu 1x24 jam.</li>
                    <li><strong>Kelebihan Pembayaran (Overpayment):</strong> Terjadi kesalahan sistem pembayaran otomatis yang mengakibatkan tagihan terpotong lebih dari nominal yang seharusnya.</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #ef4444; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> 3. Kondisi yang TIDAK Berhak Mendapatkan Refund
                </h3>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><strong>Kesalahan Input oleh Pelanggan:</strong> Pembeli salah memasukkan User ID, Zone ID, Nomor HP, atau data target. Produk yang telah sukses terkirim ke ID yang salah akibat kelalaian pembeli tidak dapat ditarik kembali atau di-refund.</li>
                    <li><strong>Perubahan Keputusan Pembeli:</strong> Pembeli mengubah pikiran setelah pembayaran terkonfirmasi lunas oleh gateway.</li>
                    <li><strong>Akun Game Dibanned / Bermasalah:</strong> Akun game tujuan pembeli sedang mengalami error, suspend, atau dibanned oleh pihak pengembang game (Publisher).</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-clock-rotate-left"></i> 4. Prosedur & Jangka Waktu Proses Refund
                </h3>
                <ol style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>Pelanggan wajib menghubungi Customer Service resmi kami melalui WhatsApp dengan melampirkan Kode Invoice (contoh: <code>INV-XXXXXXXXX</code>) dan bukti transfer/pembayaran yang sah.</li>
                    <li>Tim Customer Service akan melakukan verifikasi status transaksi ke sistem provider dan payment gateway dalam waktu <strong>1x24 jam</strong>.</li>
                    <li>Jika klaim refund disetujui, pengembalian dana akan dikirimkan ke rekening bank / e-wallet asal pelanggan atau dalam bentuk pengembalian Poin/Saldo Akun dalam waktu maksimal <strong>1-3 hari kerja</strong> (tergantung metode pembayaran yang digunakan).</li>
                </ol>
            </section>

            <section style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1rem;">
                <h4 style="font-size: 1.1rem; font-weight: 700; color: #ffffff; margin-top: 0; margin-bottom: 0.5rem;">
                    Butuh Bantuan Lebih Lanjut?
                </h4>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    Jika Anda memiliki pertanyaan mengenai kebijakan refund ini atau butuh verifikasi pesanan, tim kami siap membantu Anda.
                </p>
                @php
                    $csWhatsappUrl = \App\Models\Setting::get('cs_whatsapp_url', 'https://wa.me/6281234567890');
                @endphp
                <a href="{{ $csWhatsappUrl }}" target="_blank" class="btn" style="background: #25d366; color: #fff; text-decoration: none; padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i> Hubungi Layanan Pelanggan
                </a>
            </section>

        </div>

    </div>
</div>
@endsection
