<?php

namespace App\Repository;

use App\Entity\GoogleVolume;
use App\Entity\SavedBook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedBook>
 */
class SavedBookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedBook::class);
    }

    /**
     * @return SavedBook[] Returns an array of SavedBook objects
     */
    public function findByVolumes(string $userId, array $volumes): array
    {
        $volumeIds = array_map(fn ($volume) => $volume->getVolumeId(), $volumes);

        return $this->createQueryBuilder('s')
            ->leftJoin('s.volume', 'v')
            ->andWhere('s.userId = :userId')
            ->andWhere('v.volumeId IN (:volumeIds)')
            ->setParameter('userId', $userId)
            ->setParameter('volumeIds', $volumeIds)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return SavedBook|null Returns an array of SavedBook objects
     */
    public function findOneByVolume(string $userId, GoogleVolume $volume): ?SavedBook
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.volume', 'v')
            ->andWhere('s.userId = :userId')
            ->andWhere('v.volumeId = :volumeId')
            ->setParameter('userId', $userId)
            ->setParameter('volumeId', $volume->getVolumeId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
