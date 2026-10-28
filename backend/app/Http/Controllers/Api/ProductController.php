<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    #[OA\Get(
        path: "/products",
        tags: ["Productos"],
        summary: "Listar productos",
        description: "Obtiene la lista de productos con su divisa base y precios asociados.",
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de productos",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Product")
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
            'data' => ProductResource::collection($this->service->getProducts()),
        ]);
    }

    #[OA\Post(
        path: "/products",
        tags: ["Productos"],
        summary: "Crear un producto",
        description: "Crea un nuevo producto con su divisa base, costo de impuestos y costo de fabricación.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "description", "price", "currency_id", "tax_cost", "manufacturing_cost"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Monitor"),
                    new OA\Property(property: "description", type: "string", example: "Pantalla 27 pulgadas 4K"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 350.00),
                    new OA\Property(property: "currency_id", type: "integer", example: 1),
                    new OA\Property(property: "tax_cost", type: "number", format: "float", example: 63.00),
                    new OA\Property(property: "manufacturing_cost", type: "number", format: "float", example: 200.00),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto creado",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(response: 422, description: "Error de validación", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->createProduct($request->validated())->load('currency');

        return response()->json([
            'success' => true,
            'message' => 'Producto creado.',
            'data' => new ProductResource($product),
        ], 201);
    }

    #[OA\Get(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Obtener un producto por ID",
        description: "Devuelve un producto con su divisa base y lista de precios por divisa.",
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
                description: "Producto encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        $product->load(['currency', 'prices.currency']);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    #[OA\Put(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Actualizar un producto (completo)",
        description: "Actualización completa: todos los campos son requeridos (semántica PUT).",
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
                required: ["name", "description", "price", "currency_id", "tax_cost", "manufacturing_cost"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laptop actualizado"),
                    new OA\Property(property: "description", type: "string", example: "Nueva descripción"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 1500.00),
                    new OA\Property(property: "currency_id", type: "integer", example: 1),
                    new OA\Property(property: "tax_cost", type: "number", format: "float", example: 270.00),
                    new OA\Property(property: "manufacturing_cost", type: "number", format: "float", example: 900.00),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
            new OA\Response(response: 422, description: "Error de validación", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->service->updateProduct($product, $request->validated())->load('currency');

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado.',
            'data' => new ProductResource($product),
        ]);
    }

    #[OA\Delete(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Eliminar un producto",
        description: "Elimina un producto. Si tiene precios asociados, la eliminación se rechaza por integridad referencial.",
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
            new OA\Response(response: 204, description: "Eliminado exitosamente sin contenido"),
            new OA\Response(response: 404, description: "Producto no encontrado", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
            new OA\Response(response: 409, description: "Conflicto: no se puede eliminar", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product);

        return response()->noContent(); // 204 sin contenido
    }
}
