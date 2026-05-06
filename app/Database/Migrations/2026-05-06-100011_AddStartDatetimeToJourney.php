<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStartDatetimeToJourney extends Migration
{
    public function up()
    {
        $this->forge->addColumn('journey', [
            'start_datetime' => [
                'type'  => 'DATETIME',
                'null'  => false,
                'after' => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('journey', 'start_datetime');
    }
}
