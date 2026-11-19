<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unimplemented_resource_actions_are_not_found_instead_of_500(): void
    {
        foreach (['/event/1', '/berita/create', '/laporan/1', '/pengadaan/1', '/standar-layanan/1',
            '/galeri/create', '/informasi-publik/create', '/informasi-publik/1/edit'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_home_alias_redirects_to_homepage(): void
    {
        $this->get('/home')->assertStatus(301)->assertRedirect('/');
    }
}
