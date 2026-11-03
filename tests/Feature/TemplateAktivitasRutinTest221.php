<?php

namespace Tests\Feature;

use App\Models\Activitie221;
use App\Models\ActivityTemplate221;
use App\Models\Category;
use App\Models\EmissionFactor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateAktivitasRutinTest221 extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected EmissionFactor $activeFactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $category = Category::create([
            'name' => 'Transportasi',
            'code' => 'transportasi',
            'is_active' => true,
        ]);

        $this->activeFactor = EmissionFactor::create([
            'category_id' => $category->id,
            'name' => 'Sepeda Motor',
            'factor_value' => 0.1200,
            'unit' => 'km',
            'is_active' => true,
        ]);
    }

    public function test_menolak_nama_kosong_atau_kembar_untuk_pengguna_yang_sama(): void
    {
        $this->actingAs($this->user);

        // Buat template pertama
        ActivityTemplate221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'name' => 'Motor ke Kampus',
            'amount' => 15.0000,
            'estimated_emission' => 1.8000,
        ]);

        // Coba buat template dengan nama kembar untuk user yang sama
        $response = $this->post(route('template.store'), [
            'name' => 'Motor ke Kampus',
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 15,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_nama_template_boleh_sama_jika_berbeda_pengguna(): void
    {
        ActivityTemplate221::create([
            'user_id' => $this->otherUser->id,
            'emission_factor_id' => $this->activeFactor->id,
            'name' => 'Motor ke Kampus',
            'amount' => 15.0000,
            'estimated_emission' => 1.8000,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('template.store'), [
            'name' => 'Motor ke Kampus',
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10,
        ]);

        $response->assertRedirect(route('template.index'));
        $this->assertDatabaseHas('activity_templates', [
            'user_id' => $this->user->id,
            'name' => 'Motor ke Kampus',
        ]);
    }

    public function test_satu_klik_pakai_hari_ini_menghasilkan_catatan_aktivitas_dengan_angka_benar(): void
    {
        $this->actingAs($this->user);

        $template = ActivityTemplate221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'name' => 'Motor ke Kampus',
            'amount' => 15.0000,
            'estimated_emission' => 1.8000,
        ]);

        $response = $this->post(route('template.pakai', $template));
        $response->assertRedirect(route('template.index'));
        $response->assertSessionHas('sukses');

        $activity = Activitie221::where('user_id', $this->user->id)->first();
        $this->assertNotNull($activity);
        $this->assertEquals($this->activeFactor->id, $activity->emission_factor_id);
        $this->assertEquals(15.0000, (float) $activity->amount);
        $this->assertEquals(1.8000, (float) $activity->emission);
        $this->assertEquals(Carbon::today()->toDateString(), $activity->activity_date->toDateString());
    }

    public function test_menghapus_template_tidak_menghapus_riwayat_aktivitas(): void
    {
        $this->actingAs($this->user);

        $template = ActivityTemplate221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'name' => 'Template Rutin',
            'amount' => 10.0000,
            'estimated_emission' => 1.2000,
        ]);

        // Buat aktivitas dari template
        $aktivitas = Activitie221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10.0000,
            'emission' => 1.2000,
            'activity_date' => Carbon::today()->toDateString(),
            'note' => 'Dicatat dari template: Template Rutin',
        ]);

        // Hapus template
        $response = $this->delete(route('template.destroy', $template));
        $response->assertRedirect(route('template.index'));

        // Pastikan template terhapus tetapi aktivitas tetap ada di basis data
        $this->assertDatabaseMissing('activity_templates', ['id' => $template->id]);
        $this->assertDatabaseHas('activities', ['id' => $aktivitas->id]);
    }

    public function test_pengguna_lain_tidak_dapat_mengubah_atau_menghapus_template(): void
    {
        $template = ActivityTemplate221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'name' => 'Template Rahasia',
            'amount' => 10.0000,
            'estimated_emission' => 1.2000,
        ]);

        $this->actingAs($this->otherUser);

        $response = $this->delete(route('template.destroy', $template));
        $response->assertForbidden();

        $responsePakai = $this->post(route('template.pakai', $template));
        $responsePakai->assertForbidden();
    }
}
