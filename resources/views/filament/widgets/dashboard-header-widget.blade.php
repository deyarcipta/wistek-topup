<div class="rounded-xl border border-white/10 bg-gradient-to-r from-slate-900 via-indigo-950/80 to-slate-900 p-6 shadow-xl relative overflow-hidden">
    <!-- Decorative Glowing Background Effect -->
    <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <!-- Left: User Avatar & Greeting -->
        <div class="flex items-center gap-4">
            <div class="relative flex-shrink-0">
                <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-amber-500 to-indigo-600 p-0.5 shadow-lg flex items-center justify-center">
                    <div class="w-full h-full bg-slate-950 rounded-full flex items-center justify-center font-extrabold text-amber-400 text-lg tracking-wider">
                        {{ strtoupper(substr($user->name ?? 'Admin', 0, 2)) }}
                    </div>
                </div>
                <span class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-slate-950 rounded-full" title="Status Online"></span>
            </div>

            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                        Selamat Datang, <span class="text-amber-400 font-extrabold">{{ $user->name ?? 'Admin' }}</span> 👋
                    </h2>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">
                        {{ strtoupper($user->role ?? 'ADMIN') }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mt-1 flex items-center gap-2">
                    <span>Panel Kontrol Utama Wistek Topup</span>
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-300 font-medium">{{ $todayDate }}</span>
                </p>

                <!-- System Live Badges -->
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700 text-xs font-medium text-slate-200 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Aktif</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700 text-xs font-medium {{ $dfStatus['success'] ? 'text-emerald-300' : 'text-rose-400' }} shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Digiflazz: {{ $dfStatus['success'] ? 'Rp '.number_format($dfStatus['balance'], 0, ',', '.') : 'Offline' }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700 text-xs font-medium text-indigo-300 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Gateway: <strong>{{ $gatewayName }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Executive Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ url('/w1st3k/transactions') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-all shadow-md hover:shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Order Cash (Manual)</span>
            </a>

            <a href="{{ url('/w1st3k/manage-api-settings') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-medium transition-all">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Pengaturan API</span>
            </a>

            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-indigo-600/30 hover:bg-indigo-600/50 border border-indigo-500/40 text-indigo-200 text-xs font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                <span>Toko Web</span>
            </a>
        </div>
    </div>
</div>
