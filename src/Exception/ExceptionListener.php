<?php

namespace App\Exception;

use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationErrorException) {
            $this->setJsonResponse($event, $exception, 422);
        } elseif ($exception instanceof NotFoundException || $exception instanceof NotFoundHttpException || $exception instanceof ResourceNotFoundException) {
            $this->setJsonResponse($event, $exception, 404);
        } elseif ($exception instanceof AuthenticationException) {
            $this->setJsonResponse($event, $exception, 401);
        }
    }

    private function setJsonResponse(ExceptionEvent $event, \Throwable $exception, int $defaultStatusCode)
    {
        $statusCode = $exception->getCode();
        if ($statusCode < 100 || $statusCode >= 600) {
            $statusCode = $defaultStatusCode;
        }

        $response = new JsonResponse([
            'code' => $statusCode,
            'message' => $exception->getMessage(),
        ], $statusCode);

        $event->setResponse($response);
    }
}
