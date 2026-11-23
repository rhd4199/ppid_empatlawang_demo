<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Official;
use App\Models\User;
use App\Support\AssistantTools;
use Database\Seeders\AgencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AgencyTest extends TestCase
{
    use RefreshDatabase;

    private function agency(array $override = []): Agency
    {
        return Agency::create(array_merge([
            'name' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Empat Lawang',
            'short_name' => 'Disdukcapil',
            'slug' => 'dinas-kependudukan-pencatatan-sipil',
            'category' => 'dinas',
            'description' => 'Melayani KTP-el, KK dan akta.',
            'address' => 'Jl. Uji No. 1, Tebing Tinggi',
            'address_verified' => true,
            'phones' => [['number' => '(0702) 111222', 'label' => 'Kantor', 'verified' => true], ['number' => '0812-0000-9999', 'label' => 'WA', 'verified' => false]],
            'emails' => [['address' => 'capil@empatlawangkab.go.id', 'verified' => true], ['address' => 'ragu@gmail.com', 'verified' => false]],
            'website' => 'https://disdukcapil.empatlawangkab.go.id',
            'checked_at' => '2026-11-23',
            'is_published' => true,
            'order' => 1,
        ], $override));
    }

    public function test_public_pages_show_published_rows_and_verified_contacts_only(): void
    {
        $this->agency();
        $this->agency(['name' => 'Kecamatan Pendopo Barat', 'short_name' => null, 'slug' => 'kecamatan-pendopo-barat', 'category' => 'kecamatan',
            'address_verified' => false, 'address' => 'Alamat Satu Sumber', 'phones' => [], 'emails' => [], 'website' => null]);
        $this->agency(['name' => 'Badan Rahasia', 'slug' => 'badan-rahasia', 'category' => 'badan', 'is_published' => false]);

        $this->get(route('agencies.index'))->assertOk()
            ->assertSee('Disdukcapil')->assertSee('Kecamatan Pendopo Barat')->assertDontSee('Badan Rahasia')
            ->assertSee('href="tel:0702111222"', false)->assertSee('mailto:capil@empatlawangkab.go.id', false)
            ->assertDontSee('0812-0000-9999')->assertDontSee('ragu@gmail.com')->assertDontSee('Alamat Satu Sumber')
            ->assertSee('google.com/maps/search', false);

        $this->get(route('agencies.index', ['q' => 'pendopo']))->assertOk()->assertSee('Kecamatan Pendopo Barat')->assertDontSee('Dinas Kependudukan dan Pencatatan Sipil');
        $this->get(route('agencies.index', ['kategori' => 'dinas']))->assertOk()->assertSee('Disdukcapil')->assertDontSee('Kecamatan Pendopo Barat');

        $this->get(route('agencies.show', 'dinas-kependudukan-pencatatan-sipil'))->assertOk()
            ->assertSee('Melayani KTP-el, KK dan akta.')->assertSee('Jl. Uji No. 1, Tebing Tinggi')->assertDontSee('0812-0000-9999');
        $this->get(route('agencies.show', 'badan-rahasia'))->assertNotFound();
        $this->get(route('sitemap'))->assertSee(route('agencies.show', 'kecamatan-pendopo-barat'))->assertDontSee('badan-rahasia');
        $this->get('/')->assertSee(route('agencies.index'));
    }

    public function test_head_comes_from_linked_official_only_while_published(): void
    {
        $official = Official::create(['name' => 'Idul Adha', 'position' => 'Kepala Disdukcapil', 'order' => 1, 'is_published' => false]);
        $agency = $this->agency(['official_id' => $official->id, 'head_name' => 'Idul Adha']);

        $this->assertNull($agency->fresh()->head);
        $official->update(['is_published' => true]);
        $this->assertSame('Idul Adha', $agency->fresh()->head);
        $this->assertSame('Tanpa Tautan', $this->agency(['slug' => 'x', 'head_name' => 'Tanpa Tautan'])->head);
    }

    public function test_admin_crud_requires_login_and_validates(): void
    {
        $this->get(route('admin.agencies.index'))->assertRedirect(route('login'));
        $this->post(route('admin.agencies.store'), ['name' => 'X', 'category' => 'dinas'])->assertRedirect(route('login'));
        $this->assertSame(0, Agency::count());

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.agencies.index'))->assertOk()->assertSee('Perangkat Daerah');
        $this->get(route('admin.agencies.create'))->assertOk()->assertSee('Tugas & Layanan Utama', false);

        $this->post(route('admin.agencies.store'), ['name' => '', 'category' => 'polres', 'website' => 'bukan-url'])
            ->assertSessionHasErrors(['name', 'category', 'website']);
        $this->post(route('admin.agencies.store'), ['name' => 'Dinas Uji', 'category' => 'dinas', 'emails_text' => 'bukan email'])
            ->assertSessionHasErrors('emails_text');

        $this->post(route('admin.agencies.store'), [
            'name' => 'Dinas Pertanian Kabupaten Empat Lawang', 'short_name' => 'Distan', 'category' => 'dinas',
            'address' => 'Jl. Tani', 'address_verified' => 1,
            'phones_text' => "(0702) 123 | Kantor\n? 0813-1 | WA",
            'emails_text' => "distan@empatlawangkab.go.id\n? distan@gmail.com",
            'social' => ['instagram' => 'https://instagram.com/distan', 'facebook' => ''],
            'sources_text' => 'https://empatlawangkab.go.id/dinas-pertanian | Halaman SKPD',
            'is_published' => 1,
        ])->assertRedirect(route('admin.agencies.index'));

        $agency = Agency::sole();
        $this->assertSame('dinas-pertanian-kabupaten-empat-lawang', $agency->slug);
        $this->assertSame([['number' => '(0702) 123', 'label' => 'Kantor', 'verified' => true], ['number' => '0813-1', 'label' => 'WA', 'verified' => false]], $agency->phones);
        $this->assertSame(['distan@empatlawangkab.go.id'], $agency->public_emails);
        $this->assertSame(['instagram' => 'https://instagram.com/distan'], $agency->social);
        $this->assertSame('Halaman SKPD', $agency->sources[0]['title']);

        $this->get(route('admin.agencies.edit', $agency->id))->assertOk()->assertSee('? 0813-1 | WA');
        $this->put(route('admin.agencies.update', $agency->id), ['name' => 'Dinas Pertanian', 'slug' => $agency->slug, 'category' => 'dinas', 'is_published' => 1])
            ->assertRedirect(route('admin.agencies.index'));
        $this->assertSame('Dinas Pertanian', $agency->fresh()->name);

        $this->post(route('admin.agencies.toggle-status', $agency->id))->assertRedirect();
        $this->get(route('agencies.index'))->assertDontSee('Dinas Pertanian');
        $this->delete(route('admin.agencies.destroy', $agency->id))->assertRedirect(route('admin.agencies.index'));
        $this->assertSame(0, Agency::count());
    }

    public function test_seeder_links_officials_and_keeps_rows_edited_by_admin(): void
    {
        $rows = json_decode(file_get_contents(database_path('seeders/assets/agencies.json')), true);
        $this->assertGreaterThanOrEqual(40, count($rows));
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'slug'))));
        foreach ($rows as $row) {
            $this->assertArrayHasKey($row['category'], Agency::CATEGORIES, $row['slug']);
            $this->assertLessThanOrEqual(255, mb_strlen($row['name']));
            // the footer of empatlawangkab.go.id has template contacts: never use them
            $this->assertStringNotContainsString('123456', json_encode($row['phones'] ?? []), $row['slug']);
        }
        $this->assertSame(10, collect($rows)->where('category', 'kecamatan')->count());

        $first = $rows[0];
        $official = Official::create(['name' => 'Pejabat Uji, S.E.', 'position' => 'Kepala', 'order' => 1, 'is_published' => true]);
        Agency::import([array_merge($first, ['head_name' => 'Pejabat Uji'])]);
        $this->assertSame($official->id, Agency::where('slug', $first['slug'])->value('official_id')); // linked by name, degree ignored
        Agency::query()->delete();

        (new AgencySeeder)->run();
        $this->assertSame(count($rows), Agency::count()); // no duplicates
        $edited = Agency::where('slug', $first['slug'])->first();
        $this->travel(1)->minutes();
        $edited->update(['name' => 'Diubah Petugas']);
        $other = Agency::where('slug', $rows[1]['slug'])->first();
        $other->timestamps = false;
        $other->update(['name' => 'Lama']);

        (new AgencySeeder)->run();
        $this->assertSame('Diubah Petugas', $edited->fresh()->name);
        $this->assertSame($rows[1]['name'], $other->fresh()->name);
    }

    public function test_kontak_instansi_uses_the_table_first_then_the_web(): void
    {
        $this->agency();
        $this->agency(['name' => 'Kecamatan Pendopo Kabupaten Empat Lawang', 'short_name' => null, 'slug' => 'kecamatan-pendopo', 'category' => 'kecamatan', 'phones' => [], 'emails' => []]);
        $this->agency(['name' => 'Kecamatan Pendopo Barat Kabupaten Empat Lawang', 'short_name' => null, 'slug' => 'kecamatan-pendopo-barat', 'category' => 'kecamatan']);
        $this->agency(['name' => 'Badan Rahasia', 'short_name' => 'Samsat', 'slug' => 'rahasia', 'category' => 'badan', 'is_published' => false]);
        config(['services.openai.key' => 'sk-test', 'services.openai.search_model' => 'gpt-4o-mini']);
        Http::fake(['api.openai.com/*' => Http::response(['output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Samsat di Jl. Web.', 'annotations' => []]]]]])]);

        $db = AssistantTools::run('kontak_instansi', ['instansi' => 'Kantor Dukcapil Empat Lawang']);
        $this->assertSame('Disdukcapil', $db['hasil'][0]['singkatan']);
        $this->assertSame(['(0702) 111222 (Kantor)'], $db['hasil'][0]['telepon']); // unverified WA left out
        $this->assertSame(['capil@empatlawangkab.go.id'], $db['hasil'][0]['email']);
        $this->assertSame(route('agencies.show', 'dinas-kependudukan-pencatatan-sipil'), $db['hasil'][0]['halaman']);
        $this->assertSame('kecamatan-pendopo', basename(AssistantTools::run('kontak_instansi', ['instansi' => 'Kantor Camat Pendopo'])['hasil'][0]['halaman']));
        Http::assertNothingSent();

        $web = AssistantTools::run('kontak_instansi', ['instansi' => 'Samsat']); // hidden row is not used
        $this->assertSame('Samsat di Jl. Web.', $web['jawaban']);
        Http::assertSent(fn (Request $r) => str_contains($r['input'], 'Samsat'));

        $list = AssistantTools::run('daftar_instansi', ['kategori' => 'kecamatan']);
        $this->assertSame(['Kecamatan'], array_keys($list['instansi']));
        $this->assertSame(2, $list['jumlah']);
        $this->assertContains('daftar_instansi', array_column(array_column(AssistantTools::definitions(), 'function'), 'name'));
    }
}
