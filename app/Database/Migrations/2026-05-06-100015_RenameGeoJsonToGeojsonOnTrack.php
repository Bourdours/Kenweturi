<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameGeoJsonToGeojsonOnTrack extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('track', [
            'GeoJson' => [
                'name' => 'geojson',
                'type' => 'JSON',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('track', [
            'geojson' => [
                'name' => 'GeoJson',
                'type' => 'LONGTEXT',
                'null' => false,
            ],
        ]);
    }
}