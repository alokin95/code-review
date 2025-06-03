<?php

namespace App\Repository;

use App\Entity\Message;
use App\Filter\MessageFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[]
     */
    public function findByFilter(MessageFilter $filter): array
    {
        $qb = $this->createQueryBuilder('m');

        if ($filter->status !== null) {
            $qb
                ->andWhere('m.status = :status')
                ->setParameter('status', $filter->status)
            ;
        }

        /** @var Message[] */
        return $qb->getQuery()->getResult();
    }
}
