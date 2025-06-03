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
    private readonly EntityManagerInterface $em;
    private readonly MessageRepository $messages;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->em->createQuery('DELETE FROM App\Entity\Message')->execute();

        $this->messages = self::getContainer()->get(MessageRepository::class);
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