<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_displays_default_social_links_and_cs_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://instagram.com');
        $response->assertSee('https://tiktok.com');
        $response->assertSee('https://youtube.com');
        $response->assertSee('https://wa.me/6281234567890');
        $response->assertSee('Hubungi CS');
    }

    public function test_footer_reflects_custom_social_and_cs_settings(): void
    {
        Setting::set('cs_whatsapp_url', 'https://wa.me/6289999999999');
        Setting::set('social_instagram', 'https://instagram.com/wistek_official');
        Setting::set('social_tiktok', 'https://tiktok.com/@wistek_topup');
        Setting::set('social_youtube', 'https://youtube.com/@wistekgaming');
        Setting::set('social_whatsapp', 'https://chat.whatsapp.com/samplegroup');

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://wa.me/6289999999999');
        $response->assertSee('https://instagram.com/wistek_official');
        $response->assertSee('https://tiktok.com/@wistek_topup');
        $response->assertSee('https://youtube.com/@wistekgaming');
        $response->assertSee('https://chat.whatsapp.com/samplegroup');
    }

    public function test_footer_hides_social_icons_when_setting_is_empty(): void
    {
        Setting::set('social_instagram', '');
        Setting::set('social_tiktok', '');

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('title="Instagram"', false);
        $response->assertDontSee('title="TikTok"', false);
    }
}
