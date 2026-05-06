<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeTrackIdNotNullOnJourney extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('journey', [
            'track_id' => [
                'name' => 'track_id',
                'type' => 'INT',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('journey', [
            'track_id' => [
                'name' => 'track_id',
                'type' => 'INT',
                'null' => true,
            ],
        ]);
    }
}
