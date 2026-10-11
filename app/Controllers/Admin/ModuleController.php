<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ModuleModel;
use App\Models\ActivityLogModel;

class ModuleController extends BaseController
{
    protected ModuleModel $moduleModel;
    protected ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->moduleModel = new ModuleModel();
        $this->activityLog = new ActivityLogModel();
    }

    public function index()
    {
        $modules = $this->moduleModel->getModulesWithDetails()->paginate(10);
        $pager   = $this->moduleModel->pager;

        return view('admin/modules/index', [
            'modules' => $modules,
            'pager'   => $pager
        ]);
    }

    public function create()
    {
        $dbJbsakad = \Config\Database::connect('jbsakad');
        $departemenList = $dbJbsakad->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();

        return view('admin/modules/form', [
            'module'     => null,
            'departemen' => $departemenList
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->moduleModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $exists = $this->moduleModel->withDeleted()->where('name', $name)->first();
        
        if ($exists) {
            if ($exists->deleted_at !== null) {
                // Restore the soft-deleted module
                $data = [
                    'departemen' => $this->request->getPost('departemen') ?: null,
                    'idtingkat'  => $this->request->getPost('idtingkat') ?: null,
                    'idjurusan'  => $this->request->getPost('idjurusan') ?: null,
                    'is_enabled' => $this->request->getPost('is_enabled') ? 1 : 0,
                    'user_id'    => session('user_id'),
                ];
                if ($this->moduleModel->reuseDeletedModule($exists->id, $data)) {
                    $this->activityLog->log('update', session('user_id'), 'module', $exists->id, "Me-restore modul: {$name}");
                    return redirect()->to('/admin/modules')->with('success', 'Modul berhasil direstore dan ditambahkan.');
                }
                return redirect()->back()->withInput()->with('error', 'Gagal merestore modul.');
            }
            return redirect()->back()->withInput()->with('error', 'Modul dengan nama yang sama sudah ada.');
        }

        $data = [
            'name'       => $name,
            'departemen' => $this->request->getPost('departemen') ?: null,
            'idtingkat'  => $this->request->getPost('idtingkat') ?: null,
            'idjurusan'  => $this->request->getPost('idjurusan') ?: null,
            'is_enabled' => $this->request->getPost('is_enabled') ? 1 : 0,
            'user_id'    => session('user_id'),
        ];

        if ($this->moduleModel->skipValidation(true)->insert($data)) {
            $insertId = $this->moduleModel->getInsertID();
            $this->activityLog->log('create', session('user_id'), 'module', $insertId, "Membuat modul: {$name}");
            return redirect()->to('/admin/modules')->with('success', 'Modul berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan modul.');
    }

    public function edit($id)
    {
        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->to('/admin/modules')->with('error', 'Modul tidak ditemukan.');
        }

        $dbJbsakad = \Config\Database::connect('jbsakad');
        $departemenList = $dbJbsakad->table('departemen')->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();
        
        $tingkatList = [];
        if (!empty($module->departemen)) {
            $tingkatList = $dbJbsakad->table('tingkat')->where('departemen', $module->departemen)->where('aktif', 1)->orderBy('urutan', 'ASC')->get()->getResult();
        }

        $dbCbt = \Config\Database::connect();
        $jurusanList = [];
        if (!empty($module->departemen) && !empty($module->idtingkat)) {
            $jurusanList = $dbCbt->table('jurusan')->where('departemen', $module->departemen)->where('idtingkat', $module->idtingkat)->where('is_active', 1)->get()->getResult();
        }

        return view('admin/modules/form', [
            'module'     => $module,
            'departemen' => $departemenList,
            'tingkat'    => $tingkatList,
            'jurusan'    => $jurusanList
        ]);
    }

    public function update($id)
    {
        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->to('/admin/modules')->with('error', 'Modul tidak ditemukan.');
        }

        $rules = [
            'name' => "required|max_length[255]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        
        $exists = $this->moduleModel->withDeleted()->where('name', $name)->where('id !=', $id)->first();
        if ($exists) {
            if ($exists->deleted_at !== null) {
                return redirect()->back()->withInput()->with('error', 'Modul dengan nama yang sama sudah pernah dibuat dan dihapus (Soft-Deleted). Silakan hubungi administrator atau buat dengan nama lain untuk menghindari konflik database.');
            }
            return redirect()->back()->withInput()->with('error', 'Modul dengan nama yang sama sudah ada.');
        }

        $data = [
            'name'       => $name,
            'departemen' => $this->request->getPost('departemen') ?: null,
            'idtingkat'  => $this->request->getPost('idtingkat') ?: null,
            'idjurusan'  => $this->request->getPost('idjurusan') ?: null,
            'is_enabled' => $this->request->getPost('is_enabled') ? 1 : 0,
        ];

        if ($this->moduleModel->skipValidation(true)->update($id, $data)) {
            $this->activityLog->log('update', session('user_id'), 'module', $id, "Mengupdate modul: {$data['name']}");
            return redirect()->to('/admin/modules')->with('success', 'Modul berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui modul.');
    }

    public function delete($id)
    {
        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->back()->with('error', 'Modul tidak ditemukan.');
        }

        if ($id == 1) {
            return redirect()->back()->with('error', 'Modul Default tidak dapat dihapus.');
        }

        if ($this->moduleModel->delete($id)) {
            // Cascade delete subjects
            $subjectModel = new \App\Models\SubjectModel();
            $subjects = $subjectModel->where('module_id', $id)->findAll();
            if (!empty($subjects)) {
                $subIds = array_column($subjects, 'id');
                $subjectModel->delete($subIds);
                
                // Cascade delete questions
                $questionModel = new \App\Models\QuestionModel();
                $questionModel->whereIn('subject_id', $subIds)->delete();
            }

            $this->activityLog->log('delete', session('user_id'), 'module', $id, "Menghapus modul: {$module->name}");
            return redirect()->to('/admin/modules')->with('success', 'Modul beserta seluruh subjek dan soal di dalamnya berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus modul.');
    }
}
