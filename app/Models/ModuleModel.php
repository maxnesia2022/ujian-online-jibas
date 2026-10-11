<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $table            = 'modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name', 'departemen', 'idtingkat', 'idjurusan', 'is_enabled', 'user_id'
    ];

    protected $validationRules = [
        'name' => 'required|max_length[255]',
    ];

    /**
     * Get module with author details and subject count
     */
    public function getModulesWithDetails()
    {
        return $this->select('modules.*, users.firstname as author_name, COUNT(subjects.id) as subject_count, t.tingkat as nama_tingkat, j.nama_jurusan')
                    ->join('users', 'users.id = modules.user_id', 'left')
                    ->join('subjects', 'subjects.module_id = modules.id AND subjects.deleted_at IS NULL', 'left')
                    ->join('jbsakad.tingkat t', 'modules.idtingkat = t.replid', 'left')
                    ->join('jurusan j', 'modules.idjurusan = j.id', 'left')
                    ->groupBy('modules.id')
                    ->orderBy('modules.name', 'ASC');
    }

    /**
     * Restore and update a soft-deleted module
     */
    public function reuseDeletedModule($id, $data)
    {
        $data['deleted_at'] = null;
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }
}
