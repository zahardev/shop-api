<?php


namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        if (!$e instanceof HttpExceptionInterface) {
            return;
        }

        $statusCode = $e->getStatusCode();

        if ($statusCode >= 500) {
            return;
        }

        $data = [
            'status' => $statusCode,
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$statusCode],
            'detail' => $e->getMessage(),
        ];

        $response = new JsonResponse(
            $data,
            $statusCode
        );

        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException',
        );
    }
}
