<?php

namespace App\Http\Controllers\Api;

class DocumentationController
{
    public function getDocumentation()
    {
        $documentation = file_get_contents(resource_path('generated_api.html'));

        return response("{$documentation}");
    }
}