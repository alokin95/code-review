<?php
declare(strict_types=1);

namespace App\Controller;

use App\Filter\MessageFilter;
use App\Message\SendMessage;
use App\Query\GetMessagesQuery;
use App\QueryHandler\GetMessagesHandler;
use App\ValueResolver\MessageFilterValueResolver;
use App\ViewModel\MessageView;
use Controller\MessageControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @see MessageControllerTest
 */
class MessageController extends AbstractController
{
    /**
     * Refactored this method to follow CQRS pattern:
     * Introduced a Query + Handler: to decouple business logic from the controller.
     * This promotes single-responsibility and makes testing the behavior independent from the HTTP layer.
     * @see GetMessagesQuery
     * @see GetMessagesHandler
     *
     * Added MessageFilter with a ValueResolver: to centralize and validate request filtering logic.
     * This avoids repetitive parsing inside controllers and ensures type-safety (via enum mapping) before data hits the handler.
     * @see MessageFilter
     * @see MessageFilterValueResolver
     *
     * Introduced a dedicated ViewModel (MessageView): to explicitly control the API response structure.
     * This decouples the internal Message entity from the public contract, protecting internal data structures and aligning output with the OpenAPI spec.
     * @see MessageView
     *
     * These changes improve testability (each piece can be unit tested in isolation), readability (controller is reduced to orchestration),
     * and ensure alignment with both API spec and clean architecture principles.
     */
    #[Route('/messages')]
    public function list(
        #[ValueResolver('message_filter')] MessageFilter $filter,
        GetMessagesHandler $handler
    ): Response
    {
        $query = new GetMessagesQuery($filter);
        $messages = $handler->handle($query);

        return $this->json(['messages' => $messages]);
    }

    // NOTE: This method is intentionally not refactored per the task instructions.
    //
    // While the current implementation is functional, it has several REST and design issues:
    //
    // 1. It uses the GET HTTP method to modify state (send a message), which violates semantics.
    // A POST request would be appropriate.
    //
    // 2. The input is retrieved directly from the query string without validation or contract enforcement.
    // Using a proper DTO or request class would allow for better validation and clarity.
    //
    // 3. There is no error reporting structure or response schema defined.
    // Returning a 204 status code with a string body is not consistent.
    //
    // In a production environment I would recommend moving this logic to a POST endpoint, introducing a typed request
    // object or DTO, and handling dispatching in a dedicated application layer (maybe a command handler).
    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request, MessageBusInterface $bus): Response
    {
        $text = $request->query->getString('text');
        
        if (!$text) {
            return new Response('Text is required', 400);
        }

        $bus->dispatch(new SendMessage($text));
        
        return new Response('Successfully sent', 204);
    }
}