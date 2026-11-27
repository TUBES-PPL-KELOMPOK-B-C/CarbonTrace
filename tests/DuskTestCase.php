<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;
use Symfony\Component\Process\Process;

// Tes browser Laravel Dusk (branch Test_Dusk).
//
// Cara menjalankan:  npm run build   lalu   php artisan dusk
// Tidak perlu menyalakan "php artisan serve": kelas ini menyalakan server tes sendiri di
// http://127.0.0.1:8001 dengan APP_ENV=dusk dan database SQLite database/dusk.sqlite, lalu
// mematikannya setelah tes selesai. Server pengembangan di port 8000 dan database MySQL tidak
// tersentuh. Tambahkan --browse (atau DUSK_HEADLESS_DISABLED=1) untuk melihat Chrome bekerja.
abstract class DuskTestCase extends BaseTestCase
{
    public const PORT = 8001;

    private static ?Process $server = null;

    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }

        static::nyalakanServerTes();
    }

    // Server PHP bawaan dengan variabel lingkungan tes. Variabel proses lebih kuat daripada isi
    // .env, jadi .env pengembangan tidak perlu diubah atau ditukar.
    private static function nyalakanServerTes(): void
    {
        if (self::$server?->isRunning()) {
            return;
        }

        $akar = dirname(__DIR__);
        $basisData = $akar.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'dusk.sqlite';
        if (! file_exists($basisData)) {
            touch($basisData);
        }

        if (@fsockopen('127.0.0.1', self::PORT)) {
            throw new RuntimeException('Port '.self::PORT.' sudah dipakai program lain. Tutup program itu lalu jalankan ulang tes Dusk.');
        }

        self::$server = new Process(
            [PHP_BINARY, '-S', '127.0.0.1:'.self::PORT, $akar.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'],
            $akar.DIRECTORY_SEPARATOR.'public',
            [
                'APP_ENV' => 'dusk',
                'APP_URL' => 'http://127.0.0.1:'.self::PORT,
                'APP_DEBUG' => 'true',
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $basisData,
                'DB_URL' => '',
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'array',
                'MAIL_MAILER' => 'log', // jangan pernah mengirim email sungguhan dari tes
                'QUEUE_CONNECTION' => 'sync',
                'BCRYPT_ROUNDS' => '4',
            ],
        );
        self::$server->start();

        for ($i = 0; $i < 50; $i++) {
            if ($soket = @fsockopen('127.0.0.1', self::PORT)) {
                fclose($soket);
                break;
            }
            usleep(100_000);
        }

        register_shutdown_function(fn () => self::$server?->stop(1));
    }

    // Pengaman terakhir: tes browser memakai DatabaseMigrations (migrate:fresh), yang menghapus semua
    // tabel. Sebelum itu terjadi, pastikan koneksinya benar-benar SQLite database/dusk.sqlite.
    public function createApplication()
    {
        $app = parent::createApplication();

        $koneksi = $app['config']->get('database.default');
        $berkas = (string) $app['config']->get("database.connections.{$koneksi}.database");
        $driver = $app['config']->get("database.connections.{$koneksi}.driver");

        if ($driver !== 'sqlite' || ! str_ends_with(str_replace(DIRECTORY_SEPARATOR, '/', $berkas), 'database/dusk.sqlite')) {
            throw new RuntimeException("Tes Dusk dihentikan: basis data yang terpakai adalah [{$driver}: {$berkas}], bukan database/dusk.sqlite. Tidak ada tabel yang disentuh.");
        }

        return $app;
    }

    // Chrome dipakai ulang antartes. Sesi login dan localStorage dibersihkan supaya tiap tes mulai
    // sebagai tamu, tidak membawa akun dari tes sebelumnya (yang database-nya sudah dikosongkan).
    protected function tearDown(): void
    {
        foreach (static::$browsers as $browser) {
            try {
                $browser->driver->executeScript('try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}');
                $browser->driver->manage()->deleteAllCookies();
            } catch (\Throwable) {
                // jendela sudah tertutup: tidak ada yang perlu dibersihkan
            }
        }

        parent::tearDown();
    }

    protected function baseUrl(): string
    {
        return 'http://127.0.0.1:'.self::PORT;
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1440,1000',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--lang=id',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
