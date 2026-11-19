<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    private function jsonLd(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m));

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_news_page_has_escaped_meta_canonical_and_article_json_ld(): void
    {
        News::create([
            'title' => 'Rapat & "Evaluasi" <b>',
            'slug' => 'rapat-evaluasi',
            'content' => '<p>Paragraf satu</p><p>dua &amp; tiga</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $html = $this->get('/berita/rapat-evaluasi?utm_source=wa')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Rapat &amp; &quot;Evaluasi&quot; &lt;b&gt; | PPID Kabupaten Empat Lawang</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="Paragraf satu dua &amp; tiga">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/berita/rapat-evaluasi').'">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);

        $ld = $this->jsonLd($html);
        $this->assertSame('NewsArticle', $ld['@type']);
        $this->assertSame('Rapat & "Evaluasi" <b>', $ld['headline']);
    }

    public function test_home_has_organization_json_ld_and_login_is_noindex(): void
    {
        $ld = $this->jsonLd($this->get('/')->assertOk()->getContent());
        $this->assertSame('GovernmentOrganization', $ld['@graph'][1]['@type']);

        $this->get('/login')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
