<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJourneysTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'auto_increment' => true],
            'seats'     => ['type' => 'INT'],
            'date_'     => ['type' => 'DATETIME'],
            'smoking'   => ['type' => 'BOOLEAN'],
            'animal'    => ['type' => 'BOOLEAN'],
            'is_search' => ['type' => 'BOOLEAN'],
            'user_id'   => ['type' => 'INT'],
            'status_id' => ['type' => 'INT'],
            'driver_id' => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'journey_statuses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('driver_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('journeys', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('journeys');
    }
}
