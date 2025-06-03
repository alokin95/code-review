<?php
declare(strict_types=1);

namespace Repository;

use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Filter\MessageFilter;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private MessageRepository $messages;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $this->em = $em;
        $this->em->createQuery('DELETE FROM App\Entity\Message')->execute();

        /** @var MessageRepository $messagesRepository */
        $messagesRepository = self::getContainer()->get(MessageRepository::class);
        $this->messages = $messagesRepository;
    }

    public function test_it_has_connection(): void
    {
        $this->assertSame([], $this->messages->findAll());
    }

    public function test_find_by_filter_returns_matching_status(): void
    {
        $matchMessage = new Message();
        $matchMessage->setText('Match');
        $matchMessage->setStatus(MessageStatusEnum::Sent);

        $ignoreMessage = new Message();
        $ignoreMessage->setText('Ignore');
        $ignoreMessage->setStatus(MessageStatusEnum::Read);

        $this->em->persist($matchMessage);
        $this->em->persist($ignoreMessage);
        $this->em->flush();

        $filter = new MessageFilter(MessageStatusEnum::Sent);
        $result = $this->messages->findByFilter($filter);

        $this->assertCount(1, $result);
        $this->assertSame('Match', $result[0]->getText());
    }

    public function test_find_by_filter_returns_all_when_no_filter_applied(): void
    {
        $message1 = new Message();
        $message1->setText('One');
        $message1->setStatus(MessageStatusEnum::Sent);

        $message2 = new Message();
        $message2->setText('Two');
        $message2->setStatus(MessageStatusEnum::Read);

        $this->em->persist($message1);
        $this->em->persist($message2);
        $this->em->flush();

        $filter = new MessageFilter(); // no status set
        $result = $this->messages->findByFilter($filter);

        $this->assertCount(2, $result);
    }
}