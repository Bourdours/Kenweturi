<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToBooking extends Migration
{
    public function up()
    {
        $this->forge->addColumn('booking', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'accepted', 'rejected'],
                'default'    => 'pending',
                'after'      => 'seat_numbers',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('booking', 'status');
    }
}
