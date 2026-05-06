<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrackTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'GeoJson' => [
                'type' => 'JSON',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('track');
    }

    public function down()
    {
        $this->forge->dropTable('track');
    }
}
