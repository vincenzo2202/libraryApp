<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler  implements AccessDeniedHandlerInterface
{
  public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
  {
    $content = 'Acceso denegado. No tienes permisos para acceder a este recurso';
    $response = ["message" => $content, "status" => Response::HTTP_FORBIDDEN];
    return new JsonResponse($response);
  }
}
