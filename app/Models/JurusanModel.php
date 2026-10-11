<?php

namespace App\Models;

use CodeIgniter\Model;

class JurusanModel extends Model
{
    protected $table            = 'jurusan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['departemen', 'idtingkat', 'nama_jurusan', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'nama_jurusan' => 'required|min_length[3]|max_length[100]',
        'idtingkat'    => 'required|numeric',
        'departemen'   => 'required'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get Jurusan with Tingkat details by joining with jbsakad
     */
    public function getJurusanWithDetails()
    {
        return $this->select('jurusan.*, t.tingkat as nama_tingkat')
                    ->join('jbsakad.tingkat t', 'jurusan.idtingkat = t.replid', 'left')
                    ->orderBy('jurusan.departemen', 'ASC')
                    ->orderBy('t.urutan', 'ASC')
                    ->orderBy('jurusan.nama_jurusan', 'ASC');
    }
}
