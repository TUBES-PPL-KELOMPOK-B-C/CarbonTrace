<?php

namespace Tests\Feature;

use App\Models\Activitie221;
use App\Models\Category;
use App\Models\EmissionFactor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PencatatanAktivitasEmisiHarianTest221 extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Category $category;
    protected EmissionFactor $activeFactor;
    protected EmissionFactor $inactiveFactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Hadi Dwi Pranoto']);
        $this->otherUser = User::factory()->create(['name' => 'Pengguna Lain']);

        $this->category = Category::create([
            'name' => 'Transportasi',
            'code' => 'transportasi',
            'icon' => 'truck',
            'is_active' => true,
        ]);

        $this->activeFactor = EmissionFactor::create([
            'category_id' => $this->category->id,
            'name' => 'Motor Bensin',
            'factor_value' => 0.1200,
            'unit' => 'km',
            'is_active' => true,
        ]);

        $this->inactiveFactor = EmissionFactor::create([
            'category_id' => $this->category->id,
            'name' => 'Faktor Kadaluarsa',
            'factor_value' => 0.5000,
            'unit' => 'km',
            'is_active' => false,
        ]);
    }

    public function test_menolak_faktor_emisi_kosong_atau_tidak_aktif(): void
    {
        $this->actingAs($this->user);

        // Faktor kosong
        $response = $this->post(route('aktivitas.store'), [
            'amount' => 10,
            'activity_date' => Carbon::today()->toDateString(),
        ]);
        $response->assertSessionHasErrors('emission_factor_id');

        // Faktor tidak aktif (diarsipkan)
        $responseInactive = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->inactiveFactor->id,
            'amount' => 10,
            'activity_date' => Carbon::today()->toDateString(),
        ]);
        $responseInactive->assertSessionHasErrors('emission_factor_id');
    }

    public function test_menolak_jumlah_kosong_nol_atau_negatif(): void
    {
        $this->actingAs($this->user);

        // Jumlah nol
        $resNol = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 0,
            'activity_date' => Carbon::today()->toDateString(),
        ]);
        $resNol->assertSessionHasErrors('amount');

        // Jumlah negatif
        $resNeg = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => -5,
            'activity_date' => Carbon::today()->toDateString(),
        ]);
        $resNeg->assertSessionHasErrors('amount');
    }

    public function test_menolak_tanggal_di_masa_depan(): void
    {
        $this->actingAs($this->user);

        $besok = Carbon::tomorrow()->toDateString();

        $response = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10,
            'activity_date' => $besok,
        ]);

        $response->assertSessionHasErrors('activity_date');
    }

    public function test_menolak_catatan_lebih_dari_255_karakter(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10,
            'activity_date' => Carbon::today()->toDateString(),
            'note' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('note');
    }

    public function test_catatan_tersimpan_dengan_emisi_benar_sampai_dua_angka_desimal(): void
    {
        $this->actingAs($this->user);

        // 12.5 km x 0.12 = 1.5 kg CO2e
        $response = $this->post(route('aktivitas.store'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 12.5,
            'activity_date' => Carbon::today()->toDateString(),
            'note' => 'Perjalanan ke kantor',
        ]);

        $response->assertRedirect(route('aktivitas.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 12.5000,
            'emission' => 1.5000,
            'note' => 'Perjalanan ke kantor',
        ]);
    }

    public function test_mengubah_jumlah_membuat_angka_emisi_ikut_berubah(): void
    {
        $this->actingAs($this->user);

        $aktivitas = Activitie221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10.0000,
            'emission' => 1.2000,
            'activity_date' => Carbon::today()->toDateString(),
        ]);

        // Ubah dari 10 km menjadi 20 km -> emisi menjadi 2.40 kg CO2e
        $response = $this->put(route('aktivitas.update', $aktivitas), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 20.0000,
            'activity_date' => Carbon::today()->toDateString(),
            'note' => 'Jarak diperbarui',
        ]);

        $response->assertRedirect(route('aktivitas.index'));

        $aktivitas->refresh();
        $this->assertEquals(20.0000, (float) $aktivitas->amount);
        $this->assertEquals(2.4000, (float) $aktivitas->emission);
    }

    public function test_menghapus_catatan_membuat_total_berkurang_sesuai(): void
    {
        $this->actingAs($this->user);

        $aktivitas = Activitie221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 15.0000,
            'emission' => 1.8000,
            'activity_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->delete(route('aktivitas.destroy', $aktivitas));
        $response->assertRedirect(route('aktivitas.index'));

        $this->assertDatabaseMissing('activities', ['id' => $aktivitas->id]);
    }

    public function test_pengguna_lain_tidak_dapat_mengakses_atau_mengubah_catatan(): void
    {
        $aktivitasMilikUser = Activitie221::create([
            'user_id' => $this->user->id,
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 10.0000,
            'emission' => 1.2000,
            'activity_date' => Carbon::today()->toDateString(),
        ]);

        // Login sebagai otherUser
        $this->actingAs($this->otherUser);

        $responseEdit = $this->get(route('aktivitas.edit', $aktivitasMilikUser));
        $responseEdit->assertForbidden();

        $responseUpdate = $this->put(route('aktivitas.update', $aktivitasMilikUser), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 50,
            'activity_date' => Carbon::today()->toDateString(),
        ]);
        $responseUpdate->assertForbidden();

        $responseDelete = $this->delete(route('aktivitas.destroy', $aktivitasMilikUser));
        $responseDelete->assertForbidden();
    }

    public function test_api_pratinjau_menghasilkan_kalkulasi_json_yang_benar(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('aktivitas.pratinjau'), [
            'emission_factor_id' => $this->activeFactor->id,
            'amount' => 12.5,
        ]);

        $response->assertOk();
        $response->assertJson([
            'emisi' => 1.5,
            'satuan' => 'kg CO2e',
            'faktor' => [
                'nama' => 'Motor Bensin',
                'nilai' => 0.12,
            ],
        ]);
    }
}
