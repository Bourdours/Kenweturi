<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserStatusesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'    => ['type' => 'INT', 'auto_increment' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('user_statuses', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('user_statuses');
    }
}
