<?php

namespace App\EventListener;

use App\Exception\CustomAccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;

class ExceptionListener
{
    private $requestStack;
    private $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {


        $exception = $event->getThrowable();
        if($exception instanceof AccessDeniedHttpException){

            //dd($exception->getMessage());

            $request = $this->requestStack->getCurrentRequest();
            $session = $request->getSession();
            if (!$session->isStarted()) {
                $session->start();
            }
            $session->getFlashBag()->add('user_error', $exception->getMessage());

            // Get the event id from the request attributes
            $eventId = $request->attributes->get('id');

            // Generate the URL for the event show page with the event id
            $url = $this->router->generate('app_event_show', ['id' => $eventId]);

            $response = new RedirectResponse($url);

            $event->setResponse($response);

        }
    }
}