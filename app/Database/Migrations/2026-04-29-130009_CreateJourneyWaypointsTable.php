<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJourneyWaypointsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'order_'      => ['type' => 'INT', 'null' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'journey_id'  => ['type' => 'INT'],
            'waypoint_id' => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('journey_id', 'journeys', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('waypoint_id', 'waypoints', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('journey_waypoints', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('journey_waypoints');
    }
}
