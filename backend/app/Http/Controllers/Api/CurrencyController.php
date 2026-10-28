<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $service) {}

    #[OA\Get(
        path: '/currencies',
        tags: ['Divisas'],
        summary: 'Listar divisas',
        description: 'Obtiene la lista de divisas con su símbolo y tasa de cambio.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de divisas',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Currency')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => CurrencyResource::collection($this->service->getCurrencies()),
        ]);
    }

    #[OA\Post(
        path: '/currencies',
        tags: ['Divisas'],
        summary: 'Crear una divisa',
        description: 'Registra una nueva divisa con su símbolo y tasa de cambio respecto a la divisa base.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'symbol', 'exchange_rate'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Libra esterlina'),
                    new OA\Property(property: 'symbol', type: 'string', example: 'GBP'),
                    new OA\Property(property: 'exchange_rate', type: 'number', format: 'float', example: 1.250000),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Divisa creada',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $currency = $this->service->createCurrency($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Divisa creada.',
            'data' => new CurrencyResource($currency),
        ], 201);
    }

    #[OA\Get(
        path: '/currencies/{id}',
        tags: ['Divisas'],
        summary: 'Obtener una divisa por ID',
        description: 'Devuelve los datos de una divisa.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identificador de la divisa',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Divisa encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 404, description: 'Divisa no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(Currency $currency): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new CurrencyResource($currency),
        ]);
    }

    #[OA\Put(
        path: '/currencies/{id}',
        tags: ['Divisas'],
        summary: 'Actualizar una divisa (completo)',
        description: 'Actualización completa: todos los campos son requeridos (semántica PUT).',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identificador de la divisa',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'symbol', 'exchange_rate'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Libra esterlina'),
                    new OA\Property(property: 'symbol', type: 'string', example: 'GBP'),
                    new OA\Property(property: 'exchange_rate', type: 'number', format: 'float', example: 1.300000),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Divisa actualizada',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(response: 404, description: 'Divisa no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        $currency = $this->service->updateCurrency($currency, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Divisa actualizada.',
            'data' => new CurrencyResource($currency),
        ]);
    }

    #[OA\Delete(
        path: '/currencies/{id}',
        tags: ['Divisas'],
        summary: 'Eliminar una divisa',
        description: 'Elimina una divisa. Si está referenciada por productos o precios, la eliminación se rechaza.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identificador de la divisa',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Eliminada exitosamente sin contenido'),
            new OA\Response(response: 404, description: 'Divisa no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 409, description: 'Conflicto: no se puede eliminar', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(Currency $currency)
    {
        $this->service->deleteCurrency($currency);

        return response()->noContent(); // 204 sin contenido
    }
}
