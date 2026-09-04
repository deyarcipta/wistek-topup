@extends('layouts.app')

@section('title', 'Syarat & Ketentuan (Terms & Conditions) - Wistek Topup')

@section('content')
<div class="container" style="padding: 3.5rem 1rem 5rem;">
    <div style="max-width: 900px; margin: 0 auto; background: #0f1117; border: 1px solid var(--border-color); border-radius: 16px; padding: 2.5rem; color: var(--text-primary); font-family: 'Outfit', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
        
        <div style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <span style="background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); padding: 0.3rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; tracking: 0.05em;">
                Dokumen Legalitas Digital
            </span>
            <h1 style="font-size: 2rem; font-weight: 800; color: #ffffff; margin-top: 0.75rem; margin-bottom: 0.5rem;">
                Syarat & Ketentuan Penggunaan (Terms & Conditions)
            </h1>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">
                Terakhir diperbarui: {{ date('d F Y') }} &bull; Harap baca syarat & ketentuan ini sebelum menggunakan layanan kami.
            </p>
        </div>

        <div style="line-height: 1.8; font-size: 0.95rem; color: #d1d5db; display: flex; flex-direction: column; gap: 1.75rem;">
            
            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-file-contract"></i> 1. Penerimaan Syarat & Ketentuan
                </h3>
                <p>
                    Dengan mengakses, mendaftar, atau bertransaksi di situs web **Wistek Topup** (<code>topup.wistek.xyz</code>), Anda secara otomatis menyatakan telah membaca, memahami, dan menyetujui seluruh Syarat & Ketentuan ini. Jika Anda tidak menyetujui salah satu bagian dari ketentuan ini, Anda tidak diperkenankan menggunakan layanan kami.
                </p>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-shield"></i> 2. Layanan & Tanggung Jawab Pengguna
                </h3>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>Wistek Topup menyediakan platform penyedia layanan isi ulang (top-up) game online, voucher digital, serta pembayaran PPOB secara otomatis.</li>
                    <li>Pengguna bertanggung jawab penuh atas kebenaran dan akurasi data yang dimasukkan, termasuk User ID Game, Zone ID, Nomor Telepon, dan Email saat melakukan transaksi.</li>
                    <li>Pengguna dilarang keras melakukan tindakan kecurangan, penggunaan skrip otomatis ilegal, exploitasi celah keamanan, atau manipulasi pembayaran pada sistem Wistek Topup.</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-credit-card"></i> 3. Harga, Pembayaran & Transaksi
                </h3>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>Harga produk yang tertera di situs dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya, menyesuaikan dengan tarif resmi dari penyedia layanan/provider.</li>
                    <li>Pembayaran dapat dilakukan melalui saluran resmi payment gateway terintegrasi (QRIS, Virtual Account, E-Wallet, Retail, dll.) atau secara Cash melalui petugas resmi kami.</li>
                    <li>Setiap tagihan transaksi memiliki batas waktu pembayaran (expiry time). Transaksi yang dibayar melebihi batas waktu akan secara otomatis dinyatakan kedaluwarsa oleh sistem.</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-shield-halved"></i> 4. Pembatasan Tanggung Jawab (Limitation of Liability)
                </h3>
                <p>
                    Wistek Topup bekerja sebagai perantara penyedia jasa top-up. Kami tidak bertanggung jawab atas:
                </p>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>Gangguan teknis, maintenance internal, atau penutupan server yang dilakukan oleh pengembang/publisher game resmi.</li>
                    <li>Kerugian yang timbul akibat kelalaian pengguna dalam menjaga kerahasiaan kata sandi akun atau kesalahan pengisian ID tujuan.</li>
                    <li>Keadaan memaksa (<em>Force Majeure</em>) seperti bencana alam, gangguan jaringan internet nasional, atau kebijakan pemerintah yang menghambat operasi sistem.</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-scale-balanced"></i> 5. Hukum yang Berlaku
                </h3>
                <p>
                    Syarat & Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum dan perundang-undangan yang berlaku di Negara Republik Indonesia.
                </p>
            </section>

        </div>

    </div>
</div>
@endsection
