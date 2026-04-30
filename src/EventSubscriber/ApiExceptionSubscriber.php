<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

        $response = [
            'message' => $this->getErrorMessage($exception, $statusCode),
        ];

        $errors = $this->extractValidationErrors($exception);
        if ($errors !== []) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response = [
                'message' => 'Validation failed.',
                'errors' => $errors,
            ];
        }

        $event->setResponse(new JsonResponse($response, $statusCode, $headers));
    }

    private function getErrorMessage(\Throwable $exception, int $statusCode): string
    {
        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            return 'Internal Server Error';
        }

        $message = trim($exception->getMessage());

        return $message !== '' ? $message : 'Request failed.';
    }

    private function extractValidationErrors(\Throwable $exception): array
    {
        $validationException = $this->findValidationException($exception);

        if ($validationException === null) {
            return [];
        }

        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($validationException->getViolations() as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $errors;
    }

    private function findValidationException(\Throwable $exception): ?ValidationFailedException
    {
        if ($exception instanceof ValidationFailedException) {
            return $exception;
        }

        $previous = $exception->getPrevious();
        while ($previous !== null) {
            if ($previous instanceof ValidationFailedException) {
                return $previous;
            }

            $previous = $previous->getPrevious();
        }

        return null;
    }
}
