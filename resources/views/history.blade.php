@extends('layouts.app')

@section('title', 'Cek Status Transaksi - Wistek Topup')

@section('content')
<div class="container" style="max-width: 600px; padding: 4rem 1.5rem;">
    
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2.5rem; text-align: center;">
        <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1.5rem;"></i>
        <h2 style="font-weight: 700; margin-bottom: 0.5rem;">Lacak Pembayaran</h2>
        <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 0.95rem;">Masukkan ID invoice Anda (contoh: INV-20260826-XXXX) untuk memeriksa status pembayaran & status topup Anda.</p>

        <form action="{{ url('/history') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <input type="text" name="invoice" class="form-control" placeholder="Masukkan ID Invoice Anda..." style="text-align: center; font-size: 1.1rem; text-transform: uppercase;" required>
            </div>
            


            <button type="submit" class="btn-checkout">
                <i class="fa-solid fa-search"></i> Periksa Transaksi
            </button>
        </form>
    </div>

</div>
@endsection
