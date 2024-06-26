<?php
return [
    'stubs' => [
        'migration' => resource_path('stubs/migration.stub'),
        'controller' => resource_path('stubs/controller.stub'),
        'model' => resource_path('stubs/model.stub'),
        'view' => resource_path('stubs/view.stub'),
    ],
    'paths' => [
        'migration' => 'database/migrations/Backend',
        'controller' => 'app/Http/Controllers/Backend',
        'model' => 'app/Models/Backend',
        'view' => 'resources/views/backend',
    ],
];
