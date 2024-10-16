<?php

namespace Utilities;

use Symfony\Component\HttpFoundation\ParameterBag;

class RepositoryUtilities
{
    public static function arrayToRequest($request)
    {
        if (is_array($request)) {
            $parameterBag = new ParameterBag();
            $parameterBag->add($request);
            $request = $parameterBag;
        }

        return $request;
    }

    public static function removeByRepository($repository, $collection): void
    {
        foreach ($collection as $item) {
            $repository->remove($item);
        }
    }

    public static function removeCollection($collection, $em): void
    {
        foreach ($collection as $item) {
            $em->remove($item);
        }
    }
}
