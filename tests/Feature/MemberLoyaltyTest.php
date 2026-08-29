<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Category;
use App\Models\PointLog;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DuitkuService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MemberLoyaltyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest can register and gets a referral code automatically
     */
    public function test_user_can_register_and_receives_referral_code(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '81234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register/verify');
        $this->assertTrue(session()->has('pending_registration'));

        $otp = session('pending_registration.otp');

        $verifyResponse = $this->post('/register/verify', [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'role' => 'member',
        ]);

        $user = User::where('username', 'johndoe')->first();
        $this->assertNotNull($user->referral_code);
        $this->assertStringStartsWith('WSTK-', $user->referral_code);
    }

    /**
     * Test registration with a valid referral code
     */
    public function test_user_can_register_with_valid_referral_code(): void
    {
        $referrer = User::create([
            'name' => 'Referrer User',
            'username' => 'referrer',
            'email' => 'referrer@example.com',
            'phone' => '81234567891',
            'password' => Hash::make('secret123'),
            'role' => 'member',
            'registration_ip' => '192.168.1.1',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])->post('/register', [
            'name' => 'Referred User',
            'username' => 'referred',
            'email' => 'referred@example.com',
            'phone' => '81234567892',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'referral_code' => $referrer->referral_code,
        ]);

        $response->assertRedirect('/register/verify');
        $otp = session('pending_registration.otp');

        $verifyResponse = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])->post('/register/verify', [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'username' => 'referred',
            'referred_by_id' => $referrer->id,
            'registration_ip' => '192.168.1.2',
        ]);
    }

    /**
     * Test registration correctly extracts real client IP behind reverse proxy (aaPanel)
     */
    public function test_registration_extracts_real_client_ip_behind_reverse_proxy(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '15.15.15.2', // aaPanel reverse proxy IP
            'HTTP_X_FORWARDED_FOR' => '203.0.113.195', // Real client IP
        ])->post('/register', [
            'name' => 'Proxy User',
            'username' => 'proxyuser',
            'email' => 'proxy@example.com',
            'phone' => '081299998888',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register/verify');
        $this->assertEquals('203.0.113.195', session('pending_registration.registration_ip'));

        $otp = session('pending_registration.otp');
        $this->post('/register/verify', ['otp' => $otp]);

        $this->assertDatabaseHas('users', [
            'username' => 'proxyuser',
            'registration_ip' => '203.0.113.195',
        ]);
    }

    /**
     * Test registration with own referral code is blocked (Anti-Abuse)
     */
    public function test_registration_with_own_referral_code_is_blocked(): void
    {
        $referrer = User::create([
            'name' => 'Referrer User',
            'username' => 'referrer',
            'email' => 'referrer@example.com',
            'phone' => '081234567891',
            'password' => Hash::make('secret123'),
            'role' => 'member',
            'registration_ip' => '192.168.1.10',
        ]);

        // Attempt using own email
        $response = $this->post('/register', [
            'name' => 'Self Referrer',
            'username' => 'selfreferrer',
            'email' => 'referrer@example.com', // same email
            'phone' => '081234567892',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'referral_code' => $referrer->referral_code,
        ]);

        $response->assertSessionHasErrors(['email']);

        // Attempt using own phone
        $response2 = $this->post('/register', [
            'name' => 'Self Referrer 2',
            'username' => 'selfreferrer2',
            'email' => 'other@example.com',
            'phone' => '081234567891', // same phone as referrer
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'referral_code' => $referrer->referral_code,
        ]);

        $response2->assertSessionHasErrors(['phone']);
    }

    /**
     * Test earning points on transaction success
     */
    public function test_earning_points_on_successful_transaction(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'username' => 'buyer',
            'email' => 'buyer@example.com',
            'phone' => '81234567890',
            'password' => Hash::make('secret123'),
            'role' => 'member',
            'points_balance' => 0,
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'invoice' => 'INV-TEST-12345',
            'category_name' => 'Games',
            'product_name' => 'Diamonds',
            'sku' => 'DM-10',
            'target_no' => '123456',
            'customer_phone' => '81234567890',
            'price' => 50000,
            'points_earned' => 500,
            'payment_method' => 'QRIS',
            'payment_status' => 'unpaid',
            'topup_status' => 'pending',
        ]);

        // Trigger simulation callback to make topup SUCCESS
        $response = $this->get("/simulate-paid/{$transaction->invoice}");
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(500, $user->points_balance);
        $this->assertDatabaseHas('point_logs', [
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'amount' => 500,
            'type' => 'earn',
        ]);
    }

    /**
     * Test points expire command
     */
    public function test_points_expire_command(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'username' => 'buyer',
            'email' => 'buyer@example.com',
            'phone' => '81234567890',
            'password' => Hash::make('secret123'),
            'role' => 'member',
            'points_balance' => 1000,
        ]);

        // Earn log that has expired
        PointLog::create([
            'user_id' => $user->id,
            'amount' => 600,
            'type' => 'earn',
            'description' => 'Old Transaction',
            'expired_at' => now()->subDay(),
            'is_expired' => false,
        ]);

        // Earn log that is still active
        PointLog::create([
            'user_id' => $user->id,
            'amount' => 400,
            'type' => 'earn',
            'description' => 'New Transaction',
            'expired_at' => now()->addMonths(5),
            'is_expired' => false,
        ]);

        $this->artisan('points:expire')
            ->expectsOutputToContain('Successfully expired points for 1 users.')
            ->assertExitCode(0);

        $user->refresh();
        // Points should be reduced from 1000 to 400
        $this->assertEquals(400, $user->points_balance);
        $this->assertDatabaseHas('point_logs', [
            'user_id' => $user->id,
            'amount' => -600,
            'type' => 'expire',
        ]);
    }

    /**
     * Test non-login guest checkout accumulates points if phone matches a registered user
     */
    public function test_non_login_checkout_accumulates_points_to_registered_phone_number(): void
    {
        // 1. Test case: member registered without leading 0 (e.g. 7123123123) and guest checks out with leading 0 (07123123123)
        $user1 = User::create([
            'name' => 'Member Buyer 1',
            'username' => 'memberbuyer1',
            'email' => 'memberbuyer1@example.com',
            'phone' => '7123123123',
            'password' => Hash::make('secret123'),
            'role' => 'member',
            'points_balance' => 0,
        ]);

        $category = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'type' => 'game',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => '10 Diamonds',
            'sku' => 'DM-10',
            'price_cost' => 45000,
            'price_sell' => 50000,
            'status' => true,
        ]);

        $subCategory = SubCategory::create([
            'category_id' => $category->id,
            'name' => 'Weekly Pass',
            'sort_order' => 1,
        ]);

        $product->update([
            'sub_category_id' => $subCategory->id,
        ]);

        $this->assertEquals($subCategory->id, $product->fresh()->sub_category_id);
        $this->assertEquals('Weekly Pass', $product->fresh()->subCategory->name);

        $this->mock(DuitkuService::class, function ($mock) {
            $mock->shouldReceive('getPaymentChannels')->andReturn([]);
            $mock->shouldReceive('createTransaction')->andReturn([
                'success' => true,
                'data' => [
                    'reference' => 'REF-TEST-12345',
                    'pay_code' => '8879898989',
                    'expired_time' => time() + 3600,
                ],
            ]);
        });

        $response = $this->post('/checkout', [
            'category_id' => $category->id,
            'product_id' => $product->id,
            'payment_method' => 'QRIS',
            'target_id' => '1234567890',
            'customer_phone' => '07123123123',
        ]);

        $transaction = Transaction::where('customer_phone', '07123123123')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals($user1->id, $transaction->user_id);
        $this->assertEquals(500, $transaction->points_earned);
    }

    /**
     * Test verification fails if code is incorrect
     */
    public function test_verify_otp_fails_with_incorrect_code(): void
    {
        $this->post('/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '81234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response = $this->post('/register/verify', [
            'otp' => '000000', // Incorrect code
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertFalse(Auth::check());
    }

    /**
     * Test OTP resend triggers cooldown limit
     */
    public function test_resend_otp_cooldown(): void
    {
        $this->post('/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '81234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        // Attempt immediate resend (cooldown is active)
        $response = $this->post('/register/resend-otp');
        $response->assertSessionHasErrors('otp');
    }

    /**
     * Test user can view edit profile page
     */
    public function test_user_can_view_edit_profile_page(): void
    {
        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/profile');
        $response->assertStatus(200);
        $response->assertSee('Edit Profil Saya');
    }

    /**
     * Test user can update profile details
     */
    public function test_user_can_update_profile_details(): void
    {
        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '89876543210',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '089876543210', // Automatically normalized phone format!
        ]);
    }

    /**
     * Test user can change password
     */
    public function test_user_can_change_password(): void
    {
        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/profile', [
            'name' => 'Member User',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'current_password' => 'secret123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /**
     * Test user can request forgot password OTP via WhatsApp
     */
    public function test_user_can_request_forgot_password_otp(): void
    {
        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $response = $this->post('/forgot-password', [
            'phone' => '08123456789',
        ]);

        $response->assertRedirect('/reset-password');
        $this->assertTrue(session()->has('password_reset_session'));
        $this->assertEquals($user->id, session('password_reset_session.user_id'));
    }

    /**
     * Test user can reset password with valid OTP
     */
    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $this->post('/forgot-password', [
            'phone' => '08123456789',
        ]);

        $otp = session('password_reset_session.otp');

        $response = $this->post('/reset-password', [
            'otp' => $otp,
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('newsecretpassword123', $user->fresh()->password));
    }

    /**
     * Test password reset fails with incorrect OTP
     */
    public function test_password_reset_fails_with_incorrect_otp(): void
    {
        User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $this->post('/forgot-password', [
            'phone' => '08123456789',
        ]);

        $response = $this->post('/reset-password', [
            'otp' => '000000', // Incorrect
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertSessionHasErrors('otp');
    }

    /**
     * Test user can upload profile photo
     */
    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Member User',
            'username' => 'member123',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->post('/dashboard/profile', [
            'name' => 'Member User',
            'email' => 'member@example.com',
            'phone' => '08123456789',
            'photo' => $file,
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    /**
     * Test admin cannot access customer dashboard and gets redirected
     */
    public function test_admin_cannot_access_member_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin123',
            'email' => 'admin@wistek.com',
            'phone' => '08111111111',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect('/admin');
    }

    /**
     * Test customer cannot access admin dashboard
     */
    public function test_member_cannot_access_admin_dashboard(): void
    {
        $customer = User::create([
            'name' => 'Customer User',
            'username' => 'customer123',
            'email' => 'customer@wistek.com',
            'phone' => '08222222222',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $this->assertFalse($customer->canAccessPanel(Filament::getPanel('admin')));

        $response = $this->actingAs($customer)->get('/admin');
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman admin.');
    }

    /**
     * Test admin can create a cash order manually
     */
    public function test_admin_can_create_cash_order_manually(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin123',
            'email' => 'admin@wistek.com',
            'phone' => '08111111111',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'name' => 'Customer User',
            'username' => 'customer123',
            'email' => 'customer@wistek.com',
            'phone' => '08222222222',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $category = Category::create(['name' => 'Game', 'slug' => 'game', 'type' => 'game']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Free Fire 50 Diamonds',
            'sku' => 'ff50',
            'price_cost' => 5000,
            'price_sell' => 7000,
            'status' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListTransactions::class)
            ->callAction('orderCash', [
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'target_no_game' => '12345678',
                'wa_notification' => '08222222222',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'sku' => 'ff50',
            'target_no' => '12345678',
            'price' => 7000,
            'payment_method' => 'CASH',
            'payment_status' => 'paid',
            'topup_status' => 'success',
            'customer_phone' => '08222222222',
        ]);
    }

    /**
     * Test admin can create a cash order manually for Mobile Legends
     */
    public function test_admin_can_create_cash_order_manually_ml(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin123_ml',
            'email' => 'admin_ml@wistek.com',
            'phone' => '08111111112',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'name' => 'Customer User',
            'username' => 'customer123_ml',
            'email' => 'customer_ml@wistek.com',
            'phone' => '08222222223',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        $category = Category::create(['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'type' => 'game']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => '86 Diamonds',
            'sku' => 'ml86',
            'price_cost' => 15000,
            'price_sell' => 20000,
            'status' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListTransactions::class)
            ->callAction('orderCash', [
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'user_id_ml' => '12345678',
                'zone_id_ml' => '1234',
                'wa_notification' => '08222222223',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $customer->id,
            'sku' => 'ml86',
            'target_no' => '12345678 (1234)',
            'price' => 20000,
            'payment_method' => 'CASH',
            'payment_status' => 'paid',
            'topup_status' => 'success',
            'customer_phone' => '08222222223',
        ]);
    }
}
