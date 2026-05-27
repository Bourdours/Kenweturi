<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsBannedToUser extends Migration
{
    public function up(): void
    {
        $fields = [];

        $existingColumns = $this->db->getFieldNames('user');

        if (! in_array('is_banned', $existingColumns)) {
            $fields['is_banned'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_admin',
            ];
        }

        if (! in_array('status', $existingColumns)) {
            $fields['status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'rejected'],
                'default'    => 'pending',
                'after'      => 'email',
            ];
        }

        if (! in_array('deleted_at', $existingColumns)) {
            $fields['deleted_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('user', $fields);
        }
    }

    public function down(): void
    {
$this->forge->dropColumn('user', ['is_banned', 'status', 'deleted_at']);    }
}