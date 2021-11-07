<?php

namespace App\EventSubscriber;

use App\Exception\RessourceValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(Security $security)
    {
        $this->session = $security;
    }

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
        $response = [
            'status_code' => 500,
            'message' => $exception->getMessage()
        ];
        if (is_callable([$exception, 'getStatusCode'])) {
            $response['status_code'] = $exception->getStatusCode();
        }
        if ($exception instanceof AccessDeniedException) {
            $response['status_code'] = 403;
        }
        if ($exception instanceof AuthenticationException || ($exception instanceof AccessDeniedException && !$this->session->getUser())) {
            $response['status_code'] = 401;
            $response['message'] = "Authentication required.";
        }
        if ($exception instanceof RessourceValidationException) {
            $response['errors'] = $exception->getErrors();
        }
        $body = new JsonResponse($response, $response['status_code']);
        $event->setResponse($body) ;
    }
}