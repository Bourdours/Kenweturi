<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCarIdForeignKeyOnJourney extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('journey', 'journey_car_id_foreign');
        $this->forge->addForeignKey('car_id', 'car', 'id', '', 'SET NULL');
        $this->forge->processIndexes('journey');
    }

    public function down()
    {
        $this->forge->dropForeignKey('journey', 'journey_car_id_foreign');
        $this->forge->addForeignKey('car_id', 'car', 'id');
        $this->forge->processIndexes('journey');
    }
}
