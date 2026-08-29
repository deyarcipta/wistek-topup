<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidationController extends Controller
{
    /**
     * Validate Mobile Legends User ID and Zone ID
     * Returns the player nickname if valid.
     */
    public function checkMlbb(Request $request)
    {
        $userId = $request->query('id');
        $zoneId = $request->query('zone');

        if (empty($userId) || empty($zoneId)) {
            return response()->json([
                'success' => false,
                'message' => 'User ID dan Zone ID wajib diisi.',
            ], 400);
        }

        // Clean parameters (remove non-alphanumeric characters)
        $userId = preg_replace('/[^a-zA-Z0-9]/', '', $userId);
        $zoneId = preg_replace('/[^a-zA-Z0-9]/', '', $zoneId);

        try {
            // Call the free public endpoint from isan.eu.org with a 5-second timeout
            $response = Http::timeout(5)
                ->get('https://api.isan.eu.org/nickname/ml', [
                    'id' => $userId,
                    'zone' => $zoneId,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true && ! empty($data['name'])) {
                    // Try to decode URL-encoded name if applicable, or return directly
                    $nickname = urldecode($data['name']);

                    return response()->json([
                        'success' => true,
                        'nickname' => $nickname,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'ID atau Zone tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke server validasi nickname.',
            ], 502);
        } catch (Exception $e) {
            logger()->error('MLBB nickname validation failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memvalidasi: '.$e->getMessage(),
            ], 500);
        }
    }
}
