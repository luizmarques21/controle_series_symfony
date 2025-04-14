<?php

namespace App\Repository;

use App\DTO\SeriesCreationInputDTO;
use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Series>
 *
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    public function __construct(
        private ManagerRegistry $registry,
        private SeasonRepository $seasonRepository,
        private EpisodeRepository $episodeRepository
    )
    {
        parent::__construct($registry, Series::class);
    }

    public function add(SeriesCreationInputDTO $input): Series
    {
        $entityManager = $this->getEntityManager();

        $series = new Series($input->seriesName, $input->coverImage);
        $entityManager->persist($series);
        $entityManager->flush();

        try {
            $this->seasonRepository->addSeasonsQuantity($input->seasonsQuantity, $series->getId());
            $seasons = $this->seasonRepository->findBy(['series' => $series]);
            $this->episodeRepository->addEpisodesPerSeason($input->episodesPerSeason, $seasons);
        } catch (\Exception $e) {
            $this->remove($series, true);
        }

        return $series;
    }

    public function remove(Series $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeById(int $id): void
    {
        $series = $this->getEntityManager()->getReference(Series::class, $id);

        $this->remove($series, true);
    }
}
