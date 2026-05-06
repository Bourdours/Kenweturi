<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateJourneyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'seats' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],
            'smoking' => [
                'type' => 'BOOLEAN',
                'null' => false,
            ],
            'canceled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'track_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'location_start_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'location_end_id' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('track_id', 'track', 'id');
        $this->forge->addForeignKey('user_id', 'user', 'id');
        $this->forge->addForeignKey('location_start_id', 'location', 'id');
        $this->forge->addForeignKey('location_end_id', 'location', 'id');
        $this->forge->createTable('journey');
    }

    public function down()
    {
        $this->forge->dropTable('journey');
    }
}
