<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineArticleRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Catalog\{ArticleRepository, Article, ArticleId};
use App\Domain\Common\Barcode;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineArticle;
use App\Infrastructure\Persistence\Doctrine\Mapper\ArticleMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineArticleRepository implements ArticleRepository
{
    public function __construct(private EntityManagerInterface $em, private ArticleMapper $m) {}

    public function byId(ArticleId $id): ?Article
    {
        $e = $this->em->find(DoctrineArticle::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function byBarcode(Barcode $barcode): ?Article
    {
        $e = $this->em->getRepository(DoctrineArticle::class)->findOneBy(['barcode'=>$barcode->value]);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(Article $a): void
    {
        $e = $this->m->toEntity($a);
        $this->em->persist($e);
        $this->em->flush();
    }
}
