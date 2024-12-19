<?php

namespace Utilities;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RepositoryUtilities
{
    public static function arrayToRequest($request)
    {
        if (is_array($request)) {
            $request = new Request([], $request);
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
