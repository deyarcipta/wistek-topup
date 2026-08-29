<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrandOpeningBonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_member_gets_2000_points_when_grand_opening_promo_is_active(): void
    {
        Setting::set('promo_grand_opening_active', '1');
        Setting::set('promo_grand_opening_points', '2000');
        Setting::set('promo_grand_opening_quota', '100');

        $response = $this->post('/register', [
            'name' => 'Promo Member',
            'username' => 'promomember',
            'email' => 'promo@example.com',
            'phone' => '081299990001',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register/verify');
        $otp = session('pending_registration.otp');

        $verifyResponse = $this->post('/register/verify', [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect('/dashboard');

        $user = User::where('username', 'promomember')->first();
        $this->assertNotNull($user);
        $this->assertEquals(2000, $user->points_balance);
        $this->assertDatabaseHas('point_logs', [
            'user_id' => $user->id,
            'amount' => 2000,
            'type' => 'welcome_bonus',
        ]);
    }

    public function test_new_member_does_not_get_points_when_promo_is_inactive(): void
    {
        Setting::set('promo_grand_opening_active', '0');
        Setting::set('promo_grand_opening_points', '2000');
        Setting::set('promo_grand_opening_quota', '100');

        $response = $this->post('/register', [
            'name' => 'Normal Member',
            'username' => 'normalmember',
            'email' => 'normal@example.com',
            'phone' => '081299990002',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $otp = session('pending_registration.otp');
        $this->post('/register/verify', ['otp' => $otp]);

        $user = User::where('username', 'normalmember')->first();
        $this->assertNotNull($user);
        $this->assertEquals(0, $user->points_balance);
        $this->assertDatabaseMissing('point_logs', [
            'user_id' => $user->id,
            'type' => 'welcome_bonus',
        ]);
    }

    public function test_new_member_does_not_get_points_when_quota_is_exhausted(): void
    {
        // Set quota to only 1 member
        Setting::set('promo_grand_opening_active', '1');
        Setting::set('promo_grand_opening_points', '2000');
        Setting::set('promo_grand_opening_quota', '1');

        // First user (gets 2000 points)
        $this->post('/register', [
            'name' => 'User One',
            'username' => 'userone',
            'email' => 'user1@example.com',
            'phone' => '081299990011',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $otp1 = session('pending_registration.otp');
        $this->post('/register/verify', ['otp' => $otp1]);

        $user1 = User::where('username', 'userone')->first();
        $this->assertNotNull($user1);
        $this->assertEquals(2000, $user1->points_balance);

        auth()->logout();

        // Second user (quota exhausted, gets 0 points)
        $this->post('/register', [
            'name' => 'User Two',
            'username' => 'usertwo',
            'email' => 'user2@example.com',
            'phone' => '081299990012',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $otp2 = session('pending_registration.otp');
        $this->post('/register/verify', ['otp' => $otp2]);

        $user2 = User::where('username', 'usertwo')->first();
        $this->assertNotNull($user2);
        $this->assertEquals(0, $user2->points_balance);
    }

    public function test_member_with_referral_code_still_gets_grand_opening_bonus(): void
    {
        Setting::set('promo_grand_opening_active', '1');
        Setting::set('promo_grand_opening_points', '2000');
        Setting::set('promo_grand_opening_quota', '100');

        $referrer = User::create([
            'name' => 'Referrer User',
            'username' => 'referrer',
            'email' => 'referrer@example.com',
            'phone' => '081200000001',
            'password' => bcrypt('secret123'),
            'role' => 'member',
        ]);

        $this->post('/register', [
            'name' => 'Referred Buyer',
            'username' => 'referredbuyer',
            'email' => 'referred@example.com',
            'phone' => '081200000002',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'referral_code' => $referrer->referral_code,
        ]);

        $otp = session('pending_registration.otp');
        $this->post('/register/verify', ['otp' => $otp]);

        $newMember = User::where('username', 'referredbuyer')->first();
        $this->assertNotNull($newMember);
        $this->assertEquals($referrer->id, $newMember->referred_by_id);
        $this->assertEquals(2000, $newMember->points_balance);
        $this->assertDatabaseHas('point_logs', [
            'user_id' => $newMember->id,
            'amount' => 2000,
            'type' => 'welcome_bonus',
        ]);
    }
}
