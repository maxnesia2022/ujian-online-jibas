<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SiswaController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect('jbsakad');
        
        $departemen = $db->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();

        return view('admin/siswa/index', [
            'departemen' => $departemen
        ]);
    }

    public function getTahunAjaran()
    {
        $db = \Config\Database::connect('jbsakad');
        $departemen = $this->request->getGet('departemen');
        
        $builder = $db->table('tahunajaran')->where('aktif', 1);
        if ($departemen) {
            $builder->where('departemen', $departemen);
        }
        $data = $builder->orderBy('tahunajaran', 'DESC')->get()->getResultArray();
        
        return $this->response->setJSON($data);
    }

    public function getTingkat()
    {
        $db = \Config\Database::connect('jbsakad');
        $departemen = $this->request->getGet('departemen');
        
        $builder = $db->table('tingkat')->where('aktif', 1);
        if ($departemen) {
            $builder->where('departemen', $departemen);
        }
        $data = $builder->orderBy('urutan', 'ASC')->get()->getResultArray();
        
        foreach ($data as &$row) {
            $row['id'] = $row['replid'];
            $row['text'] = $row['tingkat'];
        }
        
        return $this->response->setJSON($data);
    }

    public function getKelas()
    {
        $db = \Config\Database::connect('jbsakad');
        $idtahunajaran = $this->request->getGet('idtahunajaran');
        $idtingkat = $this->request->getGet('idtingkat');
        
        $builder = $db->table('kelas')->where('aktif', 1);
        if ($idtahunajaran) {
            $builder->where('idtahunajaran', $idtahunajaran);
        }
        if ($idtingkat) {
            $builder->where('idtingkat', $idtingkat);
        }
        $data = $builder->orderBy('kelas', 'ASC')->get()->getResultArray();
        
        return $this->response->setJSON($data);
    }

    public function data()
    {
        $db = \Config\Database::connect('jbsakad');
        
        $departemen = $this->request->getGet('departemen');
        $tahunajaran = $this->request->getGet('tahunajaran');
        $tingkat = $this->request->getGet('tingkat');
        $kelas = $this->request->getGet('kelas');

        $builder = $db->table('siswa s')
            ->select('s.nis, s.nama, s.kelamin, k.kelas, t.tingkat, d.departemen')
            ->join('kelas k', 's.idkelas = k.replid', 'left')
            ->join('tingkat t', 'k.idtingkat = t.replid', 'left')
            ->join('departemen d', 't.departemen = d.departemen', 'left');

        if (!empty($departemen)) {
            $builder->where('d.departemen', $departemen);
        }
        if (!empty($tahunajaran)) {
            $builder->where('k.idtahunajaran', $tahunajaran);
        }
        if (!empty($tingkat)) {
            $builder->where('k.idtingkat', $tingkat);
        }
        if (!empty($kelas)) {
            $builder->where('s.idkelas', $kelas);
        }

        $siswa = $builder->orderBy('s.nama', 'ASC')->get()->getResultArray();

        return $this->response->setJSON(['data' => $siswa]);
    }

    public function generateUser()
    {
        $db = \Config\Database::connect('jbsakad');
        $userModel = new \App\Models\UserModel();

        $departemen = $this->request->getPost('departemen');
        $tahunajaran = $this->request->getPost('tahunajaran');
        $tingkat = $this->request->getPost('tingkat');
        $kelas = $this->request->getPost('kelas');

        $builder = $db->table('siswa s')
            ->select('s.nis, s.nama, s.tmplahir, s.nik')
            ->join('kelas k', 's.idkelas = k.replid', 'left')
            ->join('tingkat t', 'k.idtingkat = t.replid', 'left')
            ->join('departemen d', 't.departemen = d.departemen', 'left');

        if (!empty($departemen)) {
            $builder->where('d.departemen', $departemen);
        }
        if (!empty($tahunajaran)) {
            $builder->where('k.idtahunajaran', $tahunajaran);
        }
        if (!empty($tingkat)) {
            $builder->where('k.idtingkat', $tingkat);
        }
        if (!empty($kelas)) {
            $builder->where('s.idkelas', $kelas);
        }

        // Only active students
        $siswaList = $builder->where('s.aktif', 1)->get()->getResult();

        $countCreated = 0;
        $countExists = 0;

        foreach ($siswaList as $siswa) {
            if (empty($siswa->nis)) continue;

            $existingUser = $userModel->getByUsername($siswa->nis);
            if (!$existingUser) {
                $deletedUser = $userModel->findDeletedByUsername($siswa->nis);
                
                // Generate 6 chars password (lowercase and digits)
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
                
                $userData = [
                    'username'            => $siswa->nis,
                    'password'            => $password,
                    'role'                => 'siswa',
                    'firstname'           => $siswa->nama,
                    'birthplace'          => empty($siswa->tmplahir) ? null : $siswa->tmplahir,
                    'registration_number' => empty($siswa->nis) ? null : $siswa->nis,
                    'ssn'                 => empty($siswa->nik) ? null : $siswa->nik,
                    'is_active'           => 1
                ];

                if ($deletedUser) {
                    $userModel->reuseDeletedUser($deletedUser->id, $userData);
                } else {
                    // Skip validation to allow 6 char password, model validation requires 8
                    $userModel->skipValidation(true)->insert($userData);
                }
                $countCreated++;
            } else {
                $countExists++;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Berhasil generate $countCreated user baru. $countExists user sudah ada."
        ]);
    }
}
