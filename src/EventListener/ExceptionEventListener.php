<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsEventListener(event: 'kernel.exception')]
class ExceptionEventListener
{
    public function __invoke(ExceptionEvent $event)
    {
        $error = $event->getThrowable();
        if (!$error instanceof NotFoundHttpException) {
            return;
        }

        $request = $event->getRequest();
        $this->startsWithValidLanguage($request);
    }

    public function startsWithValidLanguage(Request $request): bool
    {
        $validLanguages = ['en', 'pt_BR'];
        foreach ($validLanguages as $language) {
            if (str_starts_with($request->getPathInfo(), "/$language")) {
                return true;
            }
        }

        return false;
    }
}