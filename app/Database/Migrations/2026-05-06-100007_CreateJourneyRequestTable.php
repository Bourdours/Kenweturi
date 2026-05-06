<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateJourneyRequestTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'requested_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'seats' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'message' => [
                'type'       => 'VARCHAR',
                'constraint' => 2000,
                'null'       => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
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
        $this->forge->addForeignKey('user_id', 'user', 'id');
        $this->forge->addForeignKey('location_start_id', 'location', 'id');
        $this->forge->addForeignKey('location_end_id', 'location', 'id');
        $this->forge->createTable('journey_request');
    }

    public function down()
    {
        $this->forge->dropTable('journey_request');
    }
}
