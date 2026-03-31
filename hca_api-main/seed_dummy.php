<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

try {
    echo "Creating schema...\n";

    DB::statement("
    CREATE TABLE IF NOT EXISTS `groups` (
        group_id INT AUTO_INCREMENT PRIMARY KEY,
        group_name VARCHAR(255)
    );");

    DB::statement("
    CREATE TABLE IF NOT EXISTS roles (
        role_id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(255)
    );");

    DB::statement("
    CREATE TABLE IF NOT EXISTS grp_role_usr_mapping (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        group_id INT,
        role_id INT
    );");

    DB::statement("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        last_name VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        password VARCHAR(255),
        is_active INT DEFAULT 1,
        is_signup INT DEFAULT 0,
        user_group_id INT,
        role INT,
        entity_id INT DEFAULT 1,
        external_user_id INT DEFAULT 1,
        permissions TEXT NULL,
        theme VARCHAR(50) DEFAULT 'default',
        last_login_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL
    );");

    echo "Inserting defaults...\n";

    DB::statement("INSERT IGNORE INTO `groups` (group_id, group_name) VALUES (1, 'Admin Group');");
    DB::statement("INSERT IGNORE INTO roles (role_id, role) VALUES (1, 'Super Admin');");

    // Insert user if not exists
    $user = DB::table('users')->where('email', 'kershr@gmail.com')->first();
    if (!$user) {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'kershr@gmail.com',
            'password' => password_hash('8#90G9QMc&il', PASSWORD_BCRYPT),
            'is_active' => 1,
            'is_signup' => 0,
            'user_group_id' => 1,
            'role' => 1
        ]);
        echo "Created User ID: {$userId}\n";
        DB::statement("INSERT INTO grp_role_usr_mapping (user_id, group_id, role_id) VALUES ({$userId}, 1, 1);");
        echo "Mapped user to group and role.\n";
    }
    echo "Done.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

