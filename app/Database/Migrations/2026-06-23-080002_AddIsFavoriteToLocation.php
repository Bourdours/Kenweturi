<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsFavoriteToLocation extends Migration
{
    public function up()
    {
        $this->forge->addColumn('location', [
            'is_favorite' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'city_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('location', 'is_favorite');
    }
}
