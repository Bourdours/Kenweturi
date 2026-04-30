<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdressFieldWaypointsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('waypoints',[
            "address"=>[
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>FALSE,
                'after'=>'longitude'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('waypoints','address');
    }
}
