<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Route `/` berada di dalam middleware `auth`: tamu harus diarahkan ke halaman login,
     * bukan menerima 200 (perilaku aplikasi ini, bukan perilaku scaffold Laravel).
     */
    public function test_guest_is_redirected_to_login_from_root_route(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
