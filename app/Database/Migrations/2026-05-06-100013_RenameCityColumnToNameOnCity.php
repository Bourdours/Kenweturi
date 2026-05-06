<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCityColumnToNameOnCity extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('city', [
            'city' => [
                'name'       => 'name',
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('city', [
            'name' => [
                'name'       => 'city',
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);
    }
}
