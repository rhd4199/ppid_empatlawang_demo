<?php

namespace Tests\Feature;

use App\Models\ContactSetting;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeBentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_platform_is_parsed_from_domain(): void
    {
        $this->assertSame('instagram', social_platform(['url' => 'https://www.instagram.com/pemkab', 'platform' => 'website']));
        $this->assertSame('facebook', social_platform(['url' => 'https://m.facebook.com/x']));
        $this->assertSame('twitter', social_platform(['url' => 'https://x.com/x']));
        $this->assertSame('website', social_platform(['url' => 'https://notinstagram.com/x']));
        $this->assertSame('tiktok', social_platform(['url' => '', 'platform' => 'tiktok']));
        $this->assertSame('fab fa-youtube', social_icon(['url' => 'https://youtu.be/abc']));
        $this->assertSame('fas fa-globe', social_icon(['url' => 'https://empatlawangkab.go.id']));
    }

    public function test_home_shows_published_info_public_bento_and_parsed_icons(): void
    {
        Document::create(['title' => 'SE Tugas Belajar', 'category' => 'informasi-publik-setiap-saat', 'is_published' => true]);
        Document::create(['title' => 'Draft Berkala', 'category' => 'informasi-publik-berkala', 'is_published' => false]);
        ContactSetting::create(['social_media' => [
            ['platform' => 'website', 'name' => 'IG', 'url' => 'https://instagram.com/ppid'],
        ]]);

        // drafts stay off the public page
        $this->get('/')
            ->assertOk()
            ->assertSee('info-bento', false)
            ->assertSee('SE Tugas Belajar')
            ->assertDontSee('Draft Berkala')
            ->assertSee('1 dokumen')
            ->assertSee('fab fa-instagram', false);
    }
}
