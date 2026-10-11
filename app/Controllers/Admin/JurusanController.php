<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JurusanModel;
use App\Models\ActivityLogModel;

class JurusanController extends BaseController
{
    protected JurusanModel $jurusanModel;
    protected ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->jurusanModel = new JurusanModel();
        $this->activityLog = new ActivityLogModel();
    }

    public function index()
    {
        $jurusans = $this->jurusanModel->getJurusanWithDetails()->paginate(15);
        
        $dbJbsakad = \Config\Database::connect('jbsakad');
        $departemenList = $dbJbsakad->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();

        return view('admin/jurusan/index', [
            'jurusans'   => $jurusans,
            'pager'      => $this->jurusanModel->pager,
            'departemen' => $departemenList
        ]);
    }

    public function create()
    {
        $dbJbsakad = \Config\Database::connect('jbsakad');
        $departemenList = $dbJbsakad->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();

        return view('admin/jurusan/form', [
            'jurusan'    => null,
            'departemen' => $departemenList
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;

        if (!$this->validate($this->jurusanModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->jurusanModel->insert($data)) {
            $this->activityLog->log('create', session('user_id'), 'jurusan', $this->jurusanModel->getInsertID(), "Membuat jurusan: {$data['nama_jurusan']}");
            return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan jurusan.');
    }

    public function edit($id)
    {
        $jurusan = $this->jurusanModel->find($id);
        if (!$jurusan) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        $dbJbsakad = \Config\Database::connect('jbsakad');
        $departemenList = $dbJbsakad->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();
        $tingkatList = $dbJbsakad->table('tingkat')->where('departemen', $jurusan->departemen)->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();

        return view('admin/jurusan/form', [
            'jurusan'    => $jurusan,
            'departemen' => $departemenList,
            'tingkat'    => $tingkatList
        ]);
    }

    public function update($id)
    {
        $jurusan = $this->jurusanModel->find($id);
        if (!$jurusan) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        $data = $this->request->getPost();
        $data['is_active'] = $this->request->getPost('is_active') ? 1 : 0;

        if (!$this->validate($this->jurusanModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->jurusanModel->update($id, $data)) {
            $this->activityLog->log('update', session('user_id'), 'jurusan', $id, "Mengupdate jurusan: {$data['nama_jurusan']}");
            return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui jurusan.');
    }

    public function delete($id)
    {
        $jurusan = $this->jurusanModel->find($id);
        if (!$jurusan) {
            return redirect()->back()->with('error', 'Jurusan tidak ditemukan.');
        }

        if ($this->jurusanModel->delete($id)) {
            $this->activityLog->log('delete', session('user_id'), 'jurusan', $id, "Menghapus jurusan: {$jurusan->nama_jurusan}");
            return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus jurusan.');
    }

    public function getOptions()
    {
        $departemen = $this->request->getGet('departemen');
        $idtingkat  = $this->request->getGet('idtingkat');

        $builder = $this->jurusanModel->where('is_active', 1);
        if ($departemen) $builder->where('departemen', $departemen);
        if ($idtingkat) $builder->where('idtingkat', $idtingkat);

        $jurusans = $builder->orderBy('nama_jurusan', 'ASC')->findAll();

        $data = [];
        foreach ($jurusans as $j) {
            $data[] = [
                'id'   => $j->id,
                'text' => $j->nama_jurusan
            ];
        }

        return $this->response->setJSON($data);
    }
}
