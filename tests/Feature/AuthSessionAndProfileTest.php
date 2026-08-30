<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSessionAndProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that expired session or unauthenticated access to member pages cleanly redirects to /login
     */
    public function test_expired_session_or_guest_accessing_member_dashboard_redirects_to_login(): void
    {
        $endpoints = [
            '/dashboard',
            '/dashboard/transactions',
            '/dashboard/points',
            '/dashboard/profile',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test that guest accessing admin panel redirects to /admin/login
     */
    public function test_guest_accessing_admin_redirects_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');

        $responseProfile = $this->get('/admin/profile');
        $responseProfile->assertRedirect('/admin/login');
    }

    /**
     * Test that admin can access Filament profile page
     */
    public function test_admin_can_access_filament_profile_page(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'admin@wistek.xyz',
            'phone' => '081111111111',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/profile');
        $response->assertStatus(200);
        $response->assertSee('Edit Profil Akun');
    }

    /**
     * Test that petugas (cashier) can access Filament profile page
     */
    public function test_cashier_petugas_can_access_filament_profile_page(): void
    {
        $cashier = User::create([
            'name' => 'Petugas Toko',
            'username' => 'petugas1',
            'email' => 'petugas@wistek.xyz',
            'phone' => '082222222222',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier);

        $response = $this->get('/admin/profile');
        $response->assertStatus(200);
        $response->assertSee('Edit Profil Akun');
    }

    /**
     * Test that regular member cannot access Filament admin profile page
     */
    public function test_member_cannot_access_filament_profile_page(): void
    {
        $member = User::create([
            'name' => 'Member Biasa',
            'username' => 'member1',
            'email' => 'member@example.com',
            'phone' => '083333333333',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $this->actingAs($member);

        $response = $this->get('/admin/profile');
        // Member receives 403 or redirect to member dashboard
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
