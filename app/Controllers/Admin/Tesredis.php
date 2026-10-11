<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Tesredis extends BaseController
{
    public function index()
    {
        // Memanggil layanan Redis kustom yang dipakai untuk Ujian (Autosave, Pub/Sub, dll)
        // Client ini otomatis membaca redis.host, redis.port, REDIS_PASSWORD, dan REDIS_PREFIX dari .env
        $redis = \App\Libraries\RedisClient::getInstance();

        if ($redis) {
            // Kita coba tulis pesan. Jika OPT_PREFIX berfungsi, 
            // kunci aslinya di server Redis akan menjadi "prefix_tes_koneksi_redis"
            $prefixInfo = env('REDIS_PREFIX') ? env('REDIS_PREFIX') : '(tanpa prefix)';
            $pesan = "Berhasil terhubung ke Redis Ujian CBT! [Menggunakan Prefix: {$prefixInfo}]";
            
            $redis->setex('tes_koneksi_redis', 300, $pesan);

            // Mencoba membaca kembali data
            $info = $redis->get('tes_koneksi_redis');

            if ($info) {
                echo "<h3>Status: $info</h3>";
                echo "<b>Detail Parameter Aktif (.env):</b><br>";
                echo "Host: " . env('redis.host', 'redis') . "<br>";
                echo "Port: " . env('redis.port', '6379') . "<br>";
                echo "Password: " . (env('REDIS_PASSWORD') ? '***** (Tersetel)' : '(Kosong)') . "<br>";
            } else {
                echo "<h3>Koneksi ke Redis berhasil, namun aplikasi gagal menulis atau membaca data (Kemungkinan Redis Read-Only).</h3>";
            }
        } else {
            echo "<h3>GAGAL TERHUBUNG KE REDIS!</h3>";
            echo "Pastikan server Redis menyala dan konfigurasi <b>redis.host</b>, <b>redis.port</b>, serta <b>REDIS_PASSWORD</b> di file <b>.env</b> sudah tepat.";
        }
    }
}