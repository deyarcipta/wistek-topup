<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test refund policy page loads successfully.
     */
    public function test_refund_policy_page_loads_successfully()
    {
        $response = $this->get('/refund-policy');

        $response->assertStatus(200)
            ->assertSee('Kebijakan Pengembalian Dana')
            ->assertSee('Refund & Cancellation Policy', false);
    }

    /**
     * Test terms and conditions page loads successfully.
     */
    public function test_terms_and_conditions_page_loads_successfully()
    {
        $response = $this->get('/terms-and-conditions');

        $response->assertStatus(200)
            ->assertSee('Syarat & Ketentuan Penggunaan', false)
            ->assertSee('Terms & Conditions', false);
    }

    /**
     * Test privacy policy page loads successfully.
     */
    public function test_privacy_policy_page_loads_successfully()
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200)
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Privacy Policy');
    }
}
