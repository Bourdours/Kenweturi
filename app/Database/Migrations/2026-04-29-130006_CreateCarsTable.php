<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCarsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'      => ['type' => 'INT', 'auto_increment' => true],
            'brand'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'model'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'color'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'seats'   => ['type' => 'INT'],
            'user_id' => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cars', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('cars');
    }
}
