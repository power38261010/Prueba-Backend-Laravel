<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

/**
 * Esquemas reutilizables de la API OpenAPI (atributos PHP 8).
 */
#[OA\Schema(
    schema: 'Currency',
    type: 'object',
    title: 'Divisa',
    description: 'Información de una divisa',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1, description: 'Identificador único'),
        new OA\Property(property: 'name', type: 'string', example: 'Dolar estadounidense'),
        new OA\Property(property: 'symbol', type: 'string', example: 'USD'),
        new OA\Property(property: 'exchange_rate', type: 'number', format: 'float', example: 1.000000, description: 'Valor de 1 unidad de la divisa en la divisa base'),
    ]
)]
#[OA\Schema(
    schema: 'ProductPrice',
    type: 'object',
    title: 'Precio por divisa',
    description: 'Precio de un producto en una divisa específica',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'currency_id', type: 'integer', example: 2),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 1333.33),
        new OA\Property(property: 'currency', ref: '#/components/schemas/Currency'),
    ]
)]
#[OA\Schema(
    schema: 'Product',
    type: 'object',
    title: 'Producto',
    description: 'Producto con su precio en divisa base',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Laptop'),
        new OA\Property(property: 'description', type: 'string', example: 'Computadora portátil de 14 pulgadas'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 1200.00),
        new OA\Property(property: 'currency_id', type: 'integer', example: 1),
        new OA\Property(property: 'currency', ref: '#/components/schemas/Currency'),
        new OA\Property(property: 'tax_cost', type: 'number', format: 'float', example: 216.00),
        new OA\Property(property: 'manufacturing_cost', type: 'number', format: 'float', example: 700.00),
        new OA\Property(
            property: 'prices',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProductPrice')
        ),
    ]
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    type: 'object',
    title: 'Respuesta exitosa',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operación exitosa.'),
        new OA\Property(property: 'data', type: 'object', description: 'Contenido del recurso'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    title: 'Respuesta de error',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Error de validación.'),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
class OpenApi {}
