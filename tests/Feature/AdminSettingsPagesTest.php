<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageApiSettings;
use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_both_settings_pages(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get('/w1st3k/manage-api-settings')
            ->assertStatus(200)
            ->assertSee('Duitku Payment Gateway')
            ->assertSee('Digiflazz H2H Topup')
            ->assertSee('Status Integrasi &amp; Koneksi API', false);

        $this->actingAs($admin)
            ->get('/w1st3k/manage-settings')
            ->assertStatus(200)
            ->assertSee('Promo Grand Opening')
            ->assertSee('Pengaturan Tampilan Ulasan Pelanggan')
            ->assertSee('Kontak Layanan Pelanggan (CS) &amp; Media Sosial', false);
    }

    public function test_non_admin_cannot_access_settings_pages(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
        ]);

        $responseApi = $this->actingAs($member)->get('/w1st3k/manage-api-settings');
        $this->assertTrue(in_array($responseApi->status(), [302, 403]));

        $responseSettings = $this->actingAs($member)->get('/w1st3k/manage-settings');
        $this->assertTrue(in_array($responseSettings->status(), [302, 403]));
    }

    public function test_can_save_api_settings_via_livewire(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin);

        Livewire::test(ManageApiSettings::class)
            ->fillForm([
                'duitku_merchant_code' => 'TEST_MERCHANT_123',
                'digiflazz_username' => 'test_supplier_user',
                'whatsapp_enabled' => '1',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('TEST_MERCHANT_123', Setting::get('duitku_merchant_code'));
        $this->assertEquals('test_supplier_user', Setting::get('digiflazz_username'));
        $this->assertEquals('1', Setting::get('whatsapp_enabled'));
    }

    public function test_can_save_system_settings_via_livewire(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin);

        Livewire::test(ManageSettings::class)
            ->fillForm([
                'review_section_enabled' => '1',
                'review_display_limit' => '6',
                'review_autoplay_speed' => '7',
                'cs_whatsapp_url' => 'https://wa.me/6281111222333',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('1', Setting::get('review_section_enabled'));
        $this->assertEquals('6', Setting::get('review_display_limit'));
        $this->assertEquals('7', Setting::get('review_autoplay_speed'));
        $this->assertEquals('https://wa.me/6281111222333', Setting::get('cs_whatsapp_url'));
    }
}
