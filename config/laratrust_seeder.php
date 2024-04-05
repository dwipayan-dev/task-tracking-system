<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'admin' => [
            'dashboard' => 'r',
            'employee' => 'c,r,u,d',
            'role' => 'c,r,u,d',
            'all_task' => 'r',
            'audit_log' => 'r',
        ],
        'employee' => [
            'dashboard' => 'r',
            'manage_task' => 'c,r,u,d',
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
