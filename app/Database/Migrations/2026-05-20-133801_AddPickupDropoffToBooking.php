<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPickupDropoffToBooking extends Migration
{
    public function up()
    {
        $this->forge->addColumn('booking', [
            'location_pickup_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'seat_numbers',
            ],
            'location_dropoff_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'location_pickup_id',
            ],
        ]);

        $this->forge->addForeignKey('location_pickup_id', 'location', 'id');
        $this->forge->addForeignKey('location_dropoff_id', 'location', 'id');
        $this->forge->processIndexes('booking');
    }

    public function down()
    {
        $this->forge->dropColumn('booking', 'location_pickup_id');
        $this->forge->dropColumn('booking', 'location_dropoff_id');
    }
}
