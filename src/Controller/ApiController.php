<?php

namespace App\Controller;

use App\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{
    /**
     * @var integer HTTP status code - 200 (OK) by default
     */
    protected $statusCode = 200;

    /**
     * Gets the value of statusCode.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param integer $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function response($data, $group = null, $headers = [])
    {
        $serializer = SerializerBuilder::create()->build();
        $headers['Content-type'] = "application/json";
        if ($group != null) {
            return new Response($serializer->serialize($data, 'json', SerializationContext::create()->enableMaxDepthChecks()->setSerializeNull(true)->setGroups(array($group))), $this->getStatusCode(), $headers);
        }
        return new Response($serializer->serialize($data, 'json', SerializationContext::create()->enableMaxDepthChecks()->setSerializeNull(true)), $this->getStatusCode(), $headers);
    }

    /**
     * Sets an error message and returns a JSON response
     *
     * @param string $errors
     * @param $headers
     * @return JsonResponse
     */
    public function respondWithErrors($errors, $headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'message' => $errors,
        ];

        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }


    /**
     * Sets a success message and data object (optional) and returns a JSON response
     *
     * @param string $success
     * @param $headers
     * @return JsonResponse
     */
    public function respondWithSuccess($successMessage, $object = null, $headers = [])
    {
        $serializer = SerializerBuilder::create()->build();
        $headers['Content-type'] = "application/json";
        $data = [
            'status' => $this->getStatusCode(),
            'message' => $successMessage,
            'data' => $object,
        ];

        return new Response($serializer->serialize($data, 'json', SerializationContext::create()->enableMaxDepthChecks()->setSerializeNull(true)), $this->getStatusCode(), $headers);
    }


    /**
     * Returns a 401 Unauthorized http response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondUnauthorized($message = 'Not authorized!')
    {
        return $this->setStatusCode(401)->respondWithErrors($message);
    }

    /**
     * Returns a 403 Forbidden http response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondForbidden($message = 'Forbidden!')
    {
        return $this->setStatusCode(403)->respondWithErrors($message);
    }

    /**
     * Returns a 422 Unprocessable Entity
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondValidationError($message = 'Validation errors')
    {
        return $this->setStatusCode(422)->respondWithErrors($message);
    }

    /**
     * Returns a 404 Not Found
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondNotFound($message = 'Not found!')
    {
        return $this->setStatusCode(404)->respondWithErrors($message);
    }

    /**
     * Returns a 201 Created
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    public function respondCreated($data = [])
    {
        return $this->setStatusCode(201)->response($data);
    }

    // this method allows us to accept JSON payloads in POST requests
    // since Symfony 4 doesn’t handle that automatically:

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

    public function tokenIdToRequest($request)
    {
        $me = $this->getUser()->getId();
        $request->request->add(['user' => $me]);
        return $request;
    }

    public function checkIfHavePagination($request): void
    {
        if (null === $request->get('nPage') || null === $request->get('nReturns')) {
            throw new NotFoundException('Datos inválidos');
        }
    }

    public function allNeededParametersPresent(Request $request, array $parameters): void
    {
        $missingParameters = [];

        foreach ($parameters as $parameter) {
            if (empty($request->get($parameter))) {
                $missingParameters[] = $parameter;
            }
        }

        if (!empty($missingParameters)) {
            $message = 'Faltan los siguientes parámetros: ' . implode(', ', $missingParameters);
            throw new \Exception($message);
        }
    }
}
