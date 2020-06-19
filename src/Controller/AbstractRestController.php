<?php


namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class AbstractRestController extends AbstractController
{
    /**
     * Protege la serialization contre les erreur circulaire (bidirectionnal relations)
     */
    protected function json(
        $data,
        $status = 200,
        array $headers = [],
        array $context = null
    ): JsonResponse {
        if ($context == null) {
            // context to handle easily circular references
            $context = [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
            ];
        }

        return parent::json(
            $data,
            $status,
            $headers,
            $context
        );
    }

}