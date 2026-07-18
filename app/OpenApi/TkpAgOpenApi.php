<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'TKP AG API',
    description: 'Internal API documentation for the TKP AG Laravel sales recording application.'
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\Get(
    path: '/health',
    operationId: 'health',
    summary: 'Health check',
    tags: ['System'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Application is healthy',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    new OA\Property(property: 'app', type: 'string', example: 'TKP AG'),
                ],
                type: 'object',
            ),
        ),
    ],
)]
#[OA\Schema(
    schema: 'SaleStatus',
    type: 'string',
    enum: ['unpaid', 'partially_paid', 'paid'],
    example: 'partially_paid',
)]
#[OA\Schema(
    schema: 'Sale',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'SL-20260714-0001'),
        new OA\Property(property: 'sale_date', type: 'string', format: 'date'),
        new OA\Property(property: 'status', ref: '#/components/schemas/SaleStatus'),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float'),
        new OA\Property(property: 'paid_amount', type: 'number', format: 'float'),
        new OA\Property(property: 'remaining_amount', type: 'number', format: 'float'),
    ],
    type: 'object',
)]
final class TkpAgOpenApi
{
}
