<?php

namespace App\EventSubscriber;

use App\Exception\RessourceValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 255]
            ]
        ];
    }

    public function processException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $response = [];
        $statusCode = 500;
        if (is_callable([$exception, 'getStatusCode'])) {
            $statusCode = $exception->getStatusCode();
        }
        $response['status_code'] = $statusCode;
        $response['message'] = $exception->getMessage();
        if ($exception instanceof RessourceValidationException) {
            $response['errors'] = $exception->getErrors();
        }
        $body = new JsonResponse($response, $statusCode);
        $event->setResponse($body) ;
    }
}