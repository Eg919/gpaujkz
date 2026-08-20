<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Retourne des messages API plus explicites que le générique "Server Error".
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Les données envoyées sont invalides.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Vous devez vous connecter pour effectuer cette action.',
                ], 401);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage();

                if (empty($message) || $message === 'Server Error') {
                    $message = match ($status) {
                        403 => 'Accès refusé pour cette action.',
                        404 => 'Ressource introuvable.',
                        405 => 'Méthode HTTP non autorisée pour cette route.',
                        429 => 'Trop de requêtes. Veuillez réessayer plus tard.',
                        default => 'Une erreur est survenue lors du traitement de votre demande.',
                    };
                }

                return response()->json(['message' => $message], $status);
            }

            return response()->json([
                'message' => 'Une erreur interne est survenue. Veuillez réessayer.',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
