<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiHealthModuleTest extends TestCase
{
    public function test_health_endpoint_returns_application_status(): void
    {
        $this->getJson(route('api.v1.health'))
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'app' => config('app.name'),
            ]);
    }
}
