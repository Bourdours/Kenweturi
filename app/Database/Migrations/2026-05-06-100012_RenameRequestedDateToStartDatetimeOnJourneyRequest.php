<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameRequestedDateToStartDatetimeOnJourneyRequest extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('journey_request', [
            'requested_date' => [
                'name' => 'start_datetime',
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('journey_request', [
            'start_datetime' => [
                'name' => 'requested_date',
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }
}
