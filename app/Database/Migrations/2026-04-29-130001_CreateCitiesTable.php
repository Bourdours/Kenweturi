<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCitiesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'      => ['type' => 'INT', 'auto_increment' => true],
            'city'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'zipcode' => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('cities', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('cities');
    }
}
