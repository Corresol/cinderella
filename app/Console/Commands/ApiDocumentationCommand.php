<?php

namespace App\Console\Commands;

use App\Libraries\ApidocBuilder;

class ApiDocumentationCommand extends \Illuminate\Console\Command
{
    protected $signature = 'api:doc';

    public function handle()
    {
        $classes = [
            'App\Http\Controllers\Api\UserController',
            'App\Http\Controllers\Api\WalletController'
        ];

        $outputDir  = resource_path();
        $file = 'generated_api.html';
        $template = resource_path('views/api.html');

        $builder = new ApidocBuilder($classes, $outputDir, 'API Documentation', $file, $template);
        $builder->generate();
    }
}