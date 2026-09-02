<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\DigiflazzService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidationController extends Controller
{
    /**
     * Legacy route for Mobile Legends validation
     */
    public function checkMlbb(Request $request)
    {
        $request->merge(['game' => 'mobile-legends']);

        return $this->checkNickname($request);
    }

    /**
     * Unified Check Nickname API for Game Accounts
     */
    public function checkNickname(Request $request)
    {
        $game = strtolower(trim($request->query('game', 'mobile-legends')));
        $userId = trim($request->query('id', ''));
        $zoneId = trim($request->query('zone', ''));

        // Map game slugs to API codes
        $gameMap = [
            'mobile-legends' => 'ml',
            'mobile-legends-bang-bang' => 'ml',
            'mlbb' => 'ml',
            'magic-chess' => 'ml',
            'magic-chess-go-go' => 'ml',
            'magic-chess-pass' => 'ml',
            'mc' => 'ml',
            'free-fire' => 'ff',
            'free-fire-max' => 'ff',
            'ff' => 'ff',
            'genshin-impact' => 'genshin',
            'honkai-star-rail' => 'hsr',
            'pubg-mobile' => 'pubg',
            'pubg' => 'pubg',
            'valorant' => 'val',
            'call-of-duty-mobile' => 'codm',
            'codm' => 'codm',
            'point-blank' => 'pb',
            'pb' => 'pb',
            'honor-of-kings' => 'hok',
            'hok' => 'hok',
        ];

        $apiCode = $gameMap[$game] ?? $game;

        if ($apiCode === 'ml' && (empty($userId) || empty($zoneId))) {
            return response()->json([
                'success' => false,
                'message' => 'User ID dan Zone ID wajib diisi.',
            ], 400);
        }

        if (empty($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'ID Akun wajib diisi.',
            ], 400);
        }

        // Validation rule check per game
        if (in_array($apiCode, ['ml', 'genshin', 'val']) && empty($zoneId)) {
            $zoneName = match ($apiCode) {
                'ml' => 'Zone ID',
                'genshin' => 'Server',
                'val' => 'Tagline (#)',
                default => 'Zone ID / Server'
            };

            return response()->json([
                'success' => false,
                'message' => "{$zoneName} wajib diisi.",
            ], 400);
        }

        // Clean parameters
        $cleanUserId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $userId);
        $cleanZoneId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $zoneId);
        $targetNo = $cleanUserId.$cleanZoneId;

        // Check Category settings from Admin Panel
        $category = Category::where('slug', $game)->first();
        if (! $category) {
            $category = Category::where('name', 'like', "%{$game}%")->first();
        }

        if ($category) {
            if (! $category->is_nickname_check_enabled || $category->nickname_check_provider === 'disabled') {
                return response()->json([
                    'success' => false,
                    'disabled' => true,
                    'message' => 'Pengecekan username untuk kategori ini sedang dinonaktifkan.',
                ]);
            }

            // Mode Digiflazz Inquiry SKU
            if ($category->nickname_check_provider === 'digiflazz' && ! empty($category->digiflazz_inquiry_sku)) {
                $digiflazz = new DigiflazzService;
                $res = $digiflazz->inquireAccountName($category->digiflazz_inquiry_sku, $targetNo);

                if ($res['success']) {
                    return response()->json([
                        'success' => true,
                        'game' => $game,
                        'nickname' => $res['nickname'],
                        'provider' => 'digiflazz',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $res['message'] ?? 'Data akun tidak ditemukan.',
                ], 404);
            }
        }

        // Mode Public Free API (Primary)
        try {
            $params = ['id' => $cleanUserId];
            if (! empty($cleanZoneId)) {
                $params['zone'] = $cleanZoneId;
            }

            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])
                ->get("https://api.isan.eu.org/nickname/{$apiCode}", $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true && (! empty($data['name']) || ! empty($data['nickname']))) {
                    $nickname = urldecode($data['name'] ?? $data['nickname']);

                    return response()->json([
                        'success' => true,
                        'game' => $game,
                        'nickname' => $nickname,
                        'provider' => 'public',
                    ]);
                }

                if (isset($data['message'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'],
                    ], 404);
                }
            }

            // Public API Offline -> Fallback to Digiflazz Inquiry SKU if configured
            if ($category && ! empty($category->digiflazz_inquiry_sku)) {
                $digiflazz = new DigiflazzService;
                $res = $digiflazz->inquireAccountName($category->digiflazz_inquiry_sku, $targetNo);

                if ($res['success']) {
                    return response()->json([
                        'success' => true,
                        'game' => $game,
                        'nickname' => $res['nickname'],
                        'provider' => 'digiflazz',
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'offline' => true,
                'message' => 'Server verifikasi nickname sedang offline/sibuk.',
            ], 200);
        } catch (Exception $e) {
            logger()->error("Nickname validation failed for {$game}: ".$e->getMessage());

            // Fallback to Digiflazz Inquiry SKU on exception
            if ($category && ! empty($category->digiflazz_inquiry_sku)) {
                $digiflazz = new DigiflazzService;
                $res = $digiflazz->inquireAccountName($category->digiflazz_inquiry_sku, $targetNo);

                if ($res['success']) {
                    return response()->json([
                        'success' => true,
                        'game' => $game,
                        'nickname' => $res['nickname'],
                        'provider' => 'digiflazz',
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'offline' => true,
                'message' => 'Server verifikasi nickname sedang offline/sibuk.',
            ], 200);
        }
    }
}
