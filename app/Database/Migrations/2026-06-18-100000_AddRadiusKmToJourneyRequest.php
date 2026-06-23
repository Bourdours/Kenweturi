<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRadiusKmToJourneyRequest extends Migration
{
    public function up()
    {
        $this->forge->addColumn('journey_request', [
            'radius_km' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 10,
                'after'    => 'seats',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('journey_request', 'radius_km');
    }
}
