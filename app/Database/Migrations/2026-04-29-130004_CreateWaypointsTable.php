<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWaypointsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'auto_increment' => true],
            'latitude'  => ['type' => 'DECIMAL', 'constraint' => '15,5'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '15,5'],
            'city_id'   => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('city_id', 'cities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('waypoints', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('waypoints');
    }
}
