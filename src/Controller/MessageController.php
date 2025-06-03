<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Filter\MessageFilter;
use App\Message\SendMessage;
use App\Query\GetMessagesQuery;
use App\QueryHandler\GetMessagesHandler;
use App\Repository\MessageRepository;
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
 * TODO: review both methods and also the `openapi.yaml` specification
 *       Add Comments for your Code-Review, so that the developer can understand why changes are needed.
 */
class MessageController extends AbstractController
{
    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored)
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

    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request, MessageBusInterface $bus): Response
    {
        $text = $request->query->get('text');
        
        if (!$text) {
            return new Response('Text is required', 400);
        }

        $bus->dispatch(new SendMessage($text));
        
        return new Response('Successfully sent', 204);
    }
}