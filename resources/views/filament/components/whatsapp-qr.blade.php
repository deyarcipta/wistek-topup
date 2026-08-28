@php
    $statusData = $this->getWhatsappStatus();
    $connected = $statusData['connected'] ?? false;
    $status = $statusData['status'] ?? 'UNKNOWN';
    $qrCode = $statusData['qrCode'] ?? null;
    $message = $statusData['message'] ?? '';
    $success = $statusData['success'] ?? false;

    // Format QR code image source if it exists
    $qrSrc = null;
    if ($qrCode) {
        $qrSrc = str_starts_with($qrCode, 'data:') ? $qrCode : 'data:image/png;base64,' . $qrCode;
    }
@endphp

<div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center; margin-top: 0.5rem; max-width: 500px; margin-left: auto; margin-right: auto;">
    @if($connected)
        <div style="display: inline-flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.5rem 1.5rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <span style="display: inline-block; width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; margin-right: 0.5rem; animation: pulse 2s infinite;"></span>
            CONNECTED / TERHUBUNG
        </div>
        <p style="font-size: 0.85rem; color: #a1a1aa; line-height: 1.5; margin-bottom: 0;">Sesi WhatsApp terhubung. Sistem top-up siap mengirimkan notifikasi secara otomatis.</p>

    @elseif($status === 'NOT_STARTED')
        <div style="display: inline-flex; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.5rem 1.5rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
            SESI BELUM DIMULAI
        </div>
        <p style="font-size: 0.85rem; color: #a1a1aa; line-height: 1.5; margin-bottom: 1.25rem;">Sesi WhatsApp belum didaftarkan atau dijalankan di server OpenWA.</p>
        <button type="button" wire:click="startWhatsappSession" style="background: #e28743; hover:background: #d97724; border: none; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.5rem; border-radius: 6px; cursor: pointer; transition: background 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
            <i class="fa-solid fa-play" style="margin-right: 0.35rem;"></i> Mulai Sesi Sekarang
        </button>

    @elseif($qrSrc)
        <div style="display: inline-flex; align-items: center; justify-content: center; background: rgba(226, 135, 67, 0.1); border: 1px solid rgba(226, 135, 67, 0.2); color: #e28743; padding: 0.5rem 1.5rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
            SCAN QR CODE SESI
        </div>
        <div style="margin: 1.25rem auto; display: inline-block; padding: 0.75rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <img src="{{ $qrSrc }}" alt="Scan QR Code" style="max-width: 200px; display: block; border: none; height: auto;">
        </div>
        <p style="font-size: 0.85rem; color: #a1a1aa; line-height: 1.5; margin-bottom: 0;">Silakan scan QR code di atas menggunakan aplikasi WhatsApp Anda:<br><strong>WhatsApp -> Pengaturan -> Perangkat Tertaut -> Tautkan Perangkat</strong></p>

    @else
        <div style="display: inline-flex; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.5rem 1.5rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
            TIDAK TERHUBUNG
        </div>
        <p style="font-size: 0.85rem; color: #a1a1aa; line-height: 1.5; margin-bottom: 1.25rem;">{{ $message ?: 'Silakan aktifkan fitur notifikasi WhatsApp dan pastikan konfigurasi API sudah benar.' }}</p>
        
        @if($status === 'ERROR' || $status === 'UNKNOWN')
            <button type="button" wire:click="startWhatsappSession" style="background: #e28743; hover:background: #d97724; border: none; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.5rem; border-radius: 6px; cursor: pointer; transition: background 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                <i class="fa-solid fa-play" style="margin-right: 0.35rem;"></i> Daftarkan & Mulai Sesi Baru
            </button>
        @endif
    @endif

    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed rgba(255,255,255,0.08); display: flex; justify-content: center; gap: 0.75rem;">
        <button type="button" wire:click="$refresh" style="background: rgba(255,255,255,0.05); hover:background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); color: #e4e4e7; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 1.2rem; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
            <i class="fa-solid fa-rotate" style="margin-right: 0.35rem;"></i> Cek Ulang Status Koneksi
        </button>
    </div>
</div>
