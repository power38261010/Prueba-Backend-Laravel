<?php

use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API stateless: se exceptuan las rutas de la API de la proteccion CSRF.
        $middleware->validateCsrfTokens(except: [
            '/products',
            '/products/*',
            '/currencies',
            '/currencies/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Excepciones de negocio (reglas de validacion de dominio).
        $exceptions->render(function (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => null,
            ], $e->getStatusCode());
        });

        // Recurso no encontrado (Eloquent findOrFail / consultas).
        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado.',
                'errors' => null,
            ], 404);
        });

        // Endpoint inexistente.
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado.',
                'errors' => null,
            ], 404);
        });

        // Errores de validacion (Form Requests): envelope consistente.
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validacion.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Errores de integridad de base de datos (FK, unicidad, etc.).
        $exceptions->render(function (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de integridad de datos.',
                'errors' => null,
            ], 500);
        });

        // Errores inesperados: respuesta generica en JSON, sin stack traces
        // ni informacion interna (APP_DEBUG = false).
        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.',
                'errors' => null,
            ], 500);
        });
    })->create();
