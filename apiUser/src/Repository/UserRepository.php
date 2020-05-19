<?php

namespace App\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

use App\Document\User;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetaData = $dm->getClassMetadata(User::class);
        parent::__construct($dm, $uow, $classMetaData);
    }

    public function findByUsernameOrEmail($login)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->find(User::class);

        $queryBuilder->addOr($queryBuilder->expr()->field('username')->equals($login));
        $queryBuilder->addOr($queryBuilder->expr()->field('email')->equals($login));

        return $queryBuilder->getQuery()->getSingleResult();
    }
}
