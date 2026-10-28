<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Prueba Backend Laravel - API de Productos y Divisas",
    version: "1.0.0",
    description: "API RESTful para gestionar productos, la información de divisas y los precios de los productos en distintas divisas. Endpoints base: /products, /currencies, /products/{id}/prices",
    contact: new OA\Contact(name: "Soporte", email: "soporte@example.com")
)]
class InfoAttr
{
}
