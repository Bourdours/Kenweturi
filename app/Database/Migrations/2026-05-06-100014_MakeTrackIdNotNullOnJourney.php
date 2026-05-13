<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class MakeTrackIdNotNullOnJourney extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('journey', 'journey_track_id_foreign');

        $this->forge->modifyColumn('journey', [
            'track_id' => [
                'name' => 'track_id',
                'type' => 'INT',
                'null' => false,
            ],
        ]);

        $this->forge->addForeignKey('track_id', 'track', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('journey');
    }

    public function down()
    {
        $this->forge->dropForeignKey('journey', 'journey_track_id_foreign');

        $this->forge->modifyColumn('journey', [
            'track_id' => [
                'name' => 'track_id',
                'type' => 'INT',
                'null' => true,
            ],
        ]);

        $this->forge->addForeignKey('track_id', 'track', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('journey');
    }
}