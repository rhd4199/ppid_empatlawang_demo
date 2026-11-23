<?php

namespace Tests\Feature;

use App\Models\ContactSetting;
use Database\Seeders\ContactSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSettingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_contact_replaces_placeholder_but_not_admin_edits(): void
    {
        ContactSetting::create(['address' => 'Jl. Lintas Sumatera No. 1', 'phones' => ['(0702) 123456', '+62 812-3456-7890'], 'emails' => ['x@y.z']]);
        $this->seed(ContactSettingSeeder::class);
        $c = ContactSetting::sole();
        $this->assertSame(['0897-2007-121'], $c->phones);
        $this->assertSame(['ppid@empatlawangkab.go.id'], $c->emails);
        $this->assertStringContainsString('Komplek Perkantoran', $c->address);

        $c->update(['phones' => ['0702-111']]); // admin edit
        $this->seed(ContactSettingSeeder::class);
        $this->assertSame(['0702-111'], ContactSetting::sole()->phones);
    }
}
