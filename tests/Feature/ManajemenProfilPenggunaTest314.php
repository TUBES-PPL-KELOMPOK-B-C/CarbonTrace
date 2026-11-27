<?php

namespace Tests\Feature;

use App\Models\User314;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManajemenProfilPenggunaTest314 extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    }

    public function test_halaman_profil_dapat_dibuka_oleh_pengguna_yang_telah_masuk(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->get(route('profil.index'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_menolak_ketika_aturan_dilanggar_name_wajib(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->put(route('profil.update'), [
            'name' => '',
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_menolak_ketika_aturan_dilanggar_email_wajib_dan_valid(): void
    {
        $user = User314::factory()->create();

        $response = $this->actingAs($user)->put(route('profil.update'), [
            'name' => 'User Valid',
            'email' => 'bukan-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_menolak_ketika_email_sudah_digunakan_oleh_pengguna_lain(): void
    {
        $user1 = User314::factory()->create(['email' => 'user1@example.com']);
        $user2 = User314::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user1)->put(route('profil.update'), [
            'name' => $user1->name,
            'email' => 'user2@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_memastikan_pengguna_dapat_mengubah_seluruh_data_profilnya_dan_perubahan_tersimpan(): void
    {
        Storage::fake('public');

        $user = User314::factory()->create();
        $foto = UploadedFile::fake()->image('profil.jpg');

        $response = $this->actingAs($user)->put(route('profil.update'), [
            'name' => 'Nama Baru Rosi',
            'email' => 'rosi.baru@example.com',
            'campus' => 'Telkom University Bandung',
            'study_program' => 'S1 Informatika',
            'photo' => $foto,
        ]);

        $response->assertRedirect(route('profil.index'));
        $response->assertSessionHas('sukses', 'Profil tersimpan.');

        $user->refresh();
        $this->assertSame('Nama Baru Rosi', $user->name);
        $this->assertSame('rosi.baru@example.com', $user->email);
        $this->assertSame('Telkom University Bandung', $user->campus);
        $this->assertSame('S1 Informatika', $user->study_program);
        $this->assertNotNull($user->photo);
        Storage::disk('public')->assertExists($user->photo);
    }

    public function test_memastikan_foto_lama_tidak_menumpuk_di_penyimpanan(): void
    {
        Storage::fake('public');

        $user = User314::factory()->create();
        $foto1 = UploadedFile::fake()->image('foto1.jpg');

        $this->actingAs($user)->put(route('profil.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $foto1,
        ]);

        $user->refresh();
        $pathFotoLama = $user->photo;
        Storage::disk('public')->assertExists($pathFotoLama);

        $foto2 = UploadedFile::fake()->image('foto2.png');
        $this->actingAs($user)->put(route('profil.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $foto2,
        ]);

        $user->refresh();
        $pathFotoBaru = $user->photo;
        Storage::disk('public')->assertExists($pathFotoBaru);
        Storage::disk('public')->assertMissing($pathFotoLama);
    }

    public function test_pengguna_dapat_memperbarui_kata_sandi(): void
    {
        $user = User314::factory()->create([
            'password' => Hash::make('password_lama123'),
        ]);

        $response = $this->actingAs($user)->put(route('profil.sandi'), [
            'current_password' => 'password_lama123',
            'password' => 'password_baru123',
            'password_confirmation' => 'password_baru123',
        ]);

        $response->assertRedirect(route('profil.index'));
        $response->assertSessionHas('sukses');

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru123', $user->password));
    }

    public function test_memastikan_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $user = User314::factory()->create([
            'email' => 'nonaktif@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->from(route('auth.masuk'))->post(route('auth.proses'), [
            'email' => 'nonaktif@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('galat_masuk', 'Akun ini dinonaktifkan. Hubungi administrator.');
        $this->assertGuest();
    }

    public function test_pengguna_dapat_menonaktifkan_akunnya_sendiri(): void
    {
        $user = User314::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('profil.destroy'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_pengguna_a_tidak_dapat_mengubah_profil_pengguna_b(): void
    {
        $userA = User314::factory()->create();
        $userB = User314::factory()->create();

        $this->assertFalse($userA->can('update', $userB));
        $this->assertFalse($userA->can('delete', $userB));
        $this->assertFalse($userA->can('view', $userB));
    }

    public function test_menolak_foto_bukan_gambar_atau_melebihi_2mb(): void
    {
        Storage::fake('public');
        $user = User314::factory()->create();

        // Format bukan gambar (PDF)
        $pdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $responsePdf = $this->actingAs($user)->put(route('profil.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $pdf,
        ]);
        $responsePdf->assertSessionHasErrors('photo');

        // Ukuran melebihi 2048 KB (3 MB)
        $fotoBesar = UploadedFile::fake()->image('besar.jpg')->size(3072);
        $responseBesar = $this->actingAs($user)->put(route('profil.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $fotoBesar,
        ]);
        $responseBesar->assertSessionHasErrors('photo');
    }

    public function test_menolak_kampus_dan_prodi_melebihi_120_karakter(): void
    {
        $user = User314::factory()->create();
        $teksPanjang = str_repeat('a', 121);

        $response = $this->actingAs($user)->put(route('profil.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'campus' => $teksPanjang,
            'study_program' => $teksPanjang,
        ]);

        $response->assertSessionHasErrors(['campus', 'study_program']);
    }

    public function test_sandi_pendek_ditolak_dengan_pesan_bahasa_indonesia(): void
    {
        $user = User314::factory()->create([
            'password' => Hash::make('password_lama123'),
        ]);

        $response = $this->actingAs($user)->put(route('profil.sandi'), [
            'current_password' => 'password_lama123',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'Kata sandi baru minimal 8 karakter.',
        ]);
    }

    public function test_administrator_terakhir_tidak_dapat_menonaktifkan_dirinya_sendiri(): void
    {
        $adminRole = Role::where('name', 'administrator')->first();
        $admin = User314::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->delete(route('profil.destroy'));

        $response->assertRedirect(route('profil.index'));
        $response->assertSessionHasErrors([
            'nonaktifkan' => 'Administrator terakhir yang aktif tidak dapat dinonaktifkan.',
        ]);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_administrator_dapat_menonaktifkan_dirinya_jika_masih_ada_admin_aktif_lain(): void
    {
        $adminRole = Role::where('name', 'administrator')->first();

        $admin1 = User314::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $admin1->assignRole('administrator');

        $admin2 = User314::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $admin2->assignRole('administrator');

        $response = $this->actingAs($admin1)->delete(route('profil.destroy'));

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertFalse($admin1->fresh()->is_active);
        $this->assertTrue($admin2->fresh()->is_active);
    }

    public function test_menonaktifkan_akun_memutus_seluruh_sesi_di_perangkat_lain(): void
    {
        $user = User314::factory()->create([
            'is_active' => true,
        ]);

        // Simulasikan sesi aktif pengguna yang tersimpan di perangkat lain pada tabel sessions
        DB::table('sessions')->insert([
            'id' => 'sesi-perangkat-lain-123',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.50',
            'user_agent' => 'Perangkat Lain',
            'payload' => base64_encode('test'),
            'last_activity' => time(),
        ]);

        $this->assertDatabaseHas('sessions', [
            'id' => 'sesi-perangkat-lain-123',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('profil.destroy'));

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseMissing('sessions', [
            'user_id' => $user->id,
        ]);

        // Permintaan lanjutan dari perangkat lain dengan akun nonaktif juga langsung diputus oleh middleware
        $perangkatLain = $this->actingAs($user->fresh())->get(route('profil.index'));
        $perangkatLain->assertRedirect(route('auth.masuk'));
        $this->assertGuest();
    }
}
