<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'message'    => ['type' => 'TEXT'],
            'send_at'    => ['type' => 'DATETIME'],
            'is_read'    => ['type' => 'BOOLEAN'],
            'journey_id' => ['type' => 'INT'],
            'user_id'    => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('journey_id', 'journeys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('messages', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('messages');
    }
}
