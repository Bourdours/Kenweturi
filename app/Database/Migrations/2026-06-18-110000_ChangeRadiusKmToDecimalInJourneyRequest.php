<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeRadiusKmToDecimalInJourneyRequest extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('journey_request', [
            'radius_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,1',
                'null'       => false,
                'default'    => 10.0,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('journey_request', [
            'radius_km' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 10,
            ],
        ]);
    }
}
