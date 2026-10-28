<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use App\Services\ProductPriceService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductPriceController extends Controller
{
    public function __construct(private ProductPriceService $service) {}

    #[OA\Get(
        path: "/products/{id}/prices",
        tags: ["Precios por producto"],
        summary: "Listar precios de un producto",
        description: "Obtiene todos los precios registrados de un producto en diferentes divisas.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Identificador del producto",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de precios del producto",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ProductPrice")
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Producto no encontrado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function index(Product $product): JsonResponse
    {
        $product->load('prices.currency');

        return response()->json([
            'success' => true,
            'data' => ProductPriceResource::collection($product->prices),
        ]);
    }

    #[OA\Post(
        path: "/products/{id}/prices",
        tags: ["Precios por producto"],
        summary: "Crear un precio para un producto",
        description: "Registra un nuevo precio del producto en una divisa específica. No se permite duplicar la combinación producto/divisa.",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Identificador del producto",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_id", "currency_id", "price"],
                properties: [
                    new OA\Property(property: "product_id", type: "integer", example: 1),
                    new OA\Property(property: "currency_id", type: "integer", example: 2),
                    new OA\Property(property: "price", type: "number", format: "float", example: 1333.33),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Precio creado",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
            new OA\Response(response: 422, description: "Divisa inexistente o precio duplicado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function store(StoreProductPriceRequest $request, Product $product): JsonResponse
    {
        $price = $this->service->createProductPrice($request->validated())->load('currency');

        return response()->json([
            'success' => true,
            'message' => 'Precio creado.',
            'data' => new ProductPriceResource($price),
        ], 201);
    }
}
