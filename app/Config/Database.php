<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    /**
     * Menetapkan 'jbscbt' sebagai koneksi default jika tidak ada yang ditentukan.
     */
    public string $defaultGroup = 'jbscbt';

    /**
     * Deklarasikan properti untuk setiap grup koneksi.
     * Properti 'default' akan diisi di constructor.
     */
    public array $default = [];
    public array $jbsakad = [];
    public array $jbsuser = [];
    public array $jbsperpus= [];
    public array $jbsfina = [];
    public array $jbssat = [];
    public array $jbsvcr = [];
    public array $jbssdm = [];
    public array $jbsumum = [];
    public array $jbsjs = [];
    public array $jbscbt = [];
    // Tambahkan grup lain jika diperlukan
    public array $sqliteSession = [];
    

    /**
     * The constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Pastikan variabel lingkungan (env) dimuat
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Konfigurasi dasar yang akan dibaca dari file .env
        // Ini mengurangi duplikasi kode.
        $baseConfig = [
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', 'root'),
            'password' => env('database.default.password', ''),
            'DBDriver' => 'MySQLi',
            'port'     => (int) env('database.default.port', 3434),
            'DBDebug'  => (ENVIRONMENT !== 'production'),
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'pConnect' => false,
        ];

        // Mengisi setiap grup koneksi dengan konfigurasi dasar + nama database spesifik
        // Nama database di sini akan menimpa 'database' yang mungkin ada di $baseConfig
        $this->jbsakad   = array_merge($baseConfig, ['database' => 'jbsakad']);
        $this->jbsuser   = array_merge($baseConfig, ['database' => 'jbsuser']);
        $this->jbsperpus    = array_merge($baseConfig, ['database' => 'jbsperpus']);
        $this->jbsfina    = array_merge($baseConfig, ['database' => 'jbsfina']);
        $this->jbssat    = array_merge($baseConfig, ['database' => 'jbssat']);
        $this->jbsvcr    = array_merge($baseConfig, ['database' => 'jbsvcr']);
        $this->jbssdm    = array_merge($baseConfig, ['database' => 'jbssdm']);
        $this->jbsumum   = array_merge($baseConfig, ['database' => 'jbsumum']);
        $this->jbsjs     = array_merge($baseConfig, ['database' => 'jbsjs']);
        $this->jbscbt    = array_merge($baseConfig, ['database' => 'jbscbt']);        
        // Konfigurasi khusus untuk SQLite yang digunakan oleh ChatSessionModel
        $this->sqliteSession = [
            'DSN'         => '',
            'hostname'    => '',
            'username'    => '',
            'password'    => '',
            'database'    => 'sessions.db', // Path ke file SQLite
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => '',
            'pConnect'    => false,
            'DBDebug'     => (ENVIRONMENT !== 'production'),
            'charset'     => 'utf8mb4',
            'DBCollat'    => 'utf8mb4_unicode_ci',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => false,
            'failover'    => [],
            'port'        => 3306, // Port tidak relevan untuk SQLite
            'foreignKeys' => true,
            'busyTimeout' => 1000,
        ];

        // Menetapkan koneksi default sama dengan jbscbt
        $this->default = $this->jbscbt;
    }
}
