<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStageTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'departure_time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'position' => [
                'type' => 'INT',
                'null' => false,
            ],
            'location_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'journey_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('location_id', 'location', 'id');
        $this->forge->addForeignKey('journey_id', 'journey', 'id');
        $this->forge->createTable('stage');
    }

    public function down()
    {
        $this->forge->dropTable('stage');
    }
}
