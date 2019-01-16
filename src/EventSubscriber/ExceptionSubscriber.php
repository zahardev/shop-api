<?php


namespace App\EventSubscriber;


use App\Utils\JsonHALResponse;
use App\Utils\Links;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTFailureEventInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{

    private $links;

    private $authorizationChecker;

    public function __construct(Links $links, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->links = $links;
        $this->authorizationChecker = $authorizationChecker;
    }

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

        $title = Response::$statusTexts[$statusCode];

        $type = $title ? $this->toUnderscore($title) : 'about:blank';

        try{
            $isLoggedIn = $this->authorizationChecker->isGranted('ROLE_USER');
        }catch(\Exception $e){
            $isLoggedIn = false;
        }

        $links = $this->links->getLinks($event->getRequest()->getPathInfo(), $isLoggedIn);

        $data = $this->createResponseData($statusCode, $type, $title, $e->getMessage(), $links);

        $response = new JsonHALResponse(
            $data,
            $statusCode
        );

        $event->setResponse($response);
    }

    public function onJWTExpired(JWTFailureEventInterface $event)
    {
        $type = 'token_expired';
        $this->onJWTException($event, $type);
    }

    public function onJWTInvalid(JWTFailureEventInterface $event)
    {
        $type = 'token_invalid';
        $this->onJWTException($event, $type);
    }

    public function onJWTNotFound(JWTFailureEventInterface $event)
    {
        $type = 'token_not_found';
        $this->onJWTException($event, $type);
    }


    public function onJWTException(JWTFailureEventInterface $event, $type )
    {
        $response = $event->getResponse();
        $msg =  $response->getMessage();
        //Can not get request from event, so just get path from globals.
        $path = $_SERVER['REQUEST_URI'];
        $data = $this->createResponseData($response->getStatusCode(), $type, $msg, $msg, $this->links->getLinks($path, false));
        $response = new JsonHALResponse($data, $response->getStatusCode());
        $event->setResponse($response);
    }

    public function createResponseData(string $status, string $type, string $title, string $detail, array $links)
    {
        return [
            'status' => $status,
            'type' => $type,
            'title' => $title,
            'detail' => $detail,
            '_links' => $links,
        ];
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException',
            Events::JWT_EXPIRED => 'onJWTExpired',
            Events::JWT_INVALID => 'onJWTInvalid',
            Events::JWT_NOT_FOUND => 'onJWTNotFound',
        );
    }

    private function toUnderscore(string $input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', str_replace(' ', '', $input)));
    }
}
