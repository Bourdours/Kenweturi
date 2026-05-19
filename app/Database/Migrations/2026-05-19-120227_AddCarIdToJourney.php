<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCarIdToJourney extends Migration
{
    public function up()
    {
        $this->forge->addColumn('journey', [
            'car_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'user_id',
            ]
        ]);

        $this->forge->addForeignKey('car_id', 'car', 'id');
        $this->forge->processIndexes('journey');
    }

    public function down()
    {
        $this->forge->dropColumn('journey', 'car_id');
    }
}
