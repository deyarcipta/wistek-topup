@extends('layouts.app')

@section('title', 'Kebijakan Privasi (Privacy Policy) - Wistek Topup')

@section('content')
<div class="container" style="padding: 3.5rem 1rem 5rem;">
    <div style="max-width: 900px; margin: 0 auto; background: #0f1117; border: 1px solid var(--border-color); border-radius: 16px; padding: 2.5rem; color: var(--text-primary); font-family: 'Outfit', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
        
        <div style="border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.3rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; tracking: 0.05em;">
                Dokumen Legalitas Digital
            </span>
            <h1 style="font-size: 2rem; font-weight: 800; color: #ffffff; margin-top: 0.75rem; margin-bottom: 0.5rem;">
                Kebijakan Privasi (Privacy Policy)
            </h1>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">
                Terakhir diperbarui: {{ date('d F Y') }} &bull; Komitmen Wistek Topup dalam melindungi data pribadi Anda.
            </p>
        </div>

        <div style="line-height: 1.8; font-size: 0.95rem; color: #d1d5db; display: flex; flex-direction: column; gap: 1.75rem;">
            
            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-lock"></i> 1. Komitmen Privasi Kami
                </h3>
                <p>
                    Wistek Topup berkomitmen penuh untuk menghormati dan melindungi privasi serta keamanan data pribadi seluruh pengunjung dan pelanggan kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat menggunakan platform kami.
                </p>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-database"></i> 2. Informasi Data yang Kami Kumpulkan
                </h3>
                <p>Kami mengumpulkan beberapa data terbatas yang diperlukan untuk memproses layanan transaksi top-up secara efisien:</p>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><strong>Informasi Identifikasi Target:</strong> User ID Game, Server / Zone ID, atau Nomor Telepon seluler tujuan top-up.</li>
                    <li><strong>Informasi Kontak:</strong> Nomor WhatsApp dan alamat e-mail untuk pengiriman notifikasi resi/invoice serta koordinasi bantuan pelanggan.</li>
                    <li><strong>Informasi Transaksi:</strong> Rincian tanggal transaksi, nama produk pilihan, metode pembayaran yang digunakan, dan status pesanan.</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-gears"></i> 3. Penggunaan Informasi Data
                </h3>
                <p>Data pribadi yang dikumpulkan digunakan secara terbatas untuk tujuan-tujuan berikut:</p>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li>Memproses pesanan dan memverifikasi pembayaran Anda secara otomatis melalui payment gateway dan provider resmi.</li>
                    <li>Mengirimkan notifikasi bukti transaksi, tagihan pembayaran, dan update status pengiriman pesanan via WhatsApp/Email.</li>
                    <li>Memberikan layanan bantuan pelanggan (*Customer Support*) dan memproses klaim/bantuan jika terjadi kendala pesanan.</li>
                    <li>Mencegah aktivitas kecurangan, penipuan, atau pencucian uang (*Anti-Fraud Compliance*).</li>
                </ul>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-lock"></i> 4. Keamanan & Kerahasiaan Data
                </h3>
                <p>
                    Kami menerapkan standar keamanan enkripsi digital tinggi untuk melindungi data Anda dari akses tanpa izin, manipulasi, atau kebocoran. 
                    <strong>Wistek Topup menjamin tidak akan pernah menjual, menyewakan, atau mendistribusikan data pribadi Anda kepada pihak ketiga manapun</strong> untuk kepentingan komersial tanpa persetujuan Anda, kecuali apabila diwajibkan oleh ketentuan hukum dan pihak berwenang Republik Indonesia.
                </p>
            </section>

            <section>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #f59e0b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-cookie-bite"></i> 5. Penggunaan Cookie & Sesi
                </h3>
                <p>
                    Platform kami menggunakan *Cookie* dan teknologi penyimpanan sesi lokal browser (*Local Storage*) secara terbatas untuk menyimpan preferensi pengguna, mempertahankan sesi login member yang aman, dan meningkatkan kinerja kecepatan loading situs web.
                </p>
            </section>

        </div>

    </div>
</div>
@endsection
