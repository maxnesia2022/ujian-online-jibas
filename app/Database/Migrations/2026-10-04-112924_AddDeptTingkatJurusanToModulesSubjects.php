<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeptTingkatJurusanToModulesSubjects extends Migration
{
    public function up()
    {
        $fields = [
            'departemen' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'name',
            ],
            'idtingkat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'departemen',
            ],
            'idjurusan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'idtingkat',
            ],
        ];

        $this->forge->addColumn('modules', $fields);
        
        $fieldsSubj = $fields;
        $fieldsSubj['departemen']['after'] = 'description';
        $fieldsSubj['idtingkat']['after'] = 'departemen';
        $fieldsSubj['idjurusan']['after'] = 'idtingkat';
        
        $this->forge->addColumn('subjects', $fieldsSubj);
    }

    public function down()
    {
        $this->forge->dropColumn('modules', ['departemen', 'idtingkat', 'idjurusan']);
        $this->forge->dropColumn('subjects', ['departemen', 'idtingkat', 'idjurusan']);
    }
}
