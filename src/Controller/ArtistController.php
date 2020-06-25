<?php


namespace App\Controller;


use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/api/artist")
 */
class ArtistController extends AbstractRestController
{
    private $entityManager;
    private $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ArtistRepository $repository
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @Rest\Get("")
     */
    public function getAll()
    {
        $entities = $this->repository->findAll();
        return $this->json($entities);
    }

    /**
     * @Rest\Get("/{id}")
     */
    public function getOne(int $id)
    {
        $entity = $this->repository->find($id);
        if ($entity == null) {
            return $this->json("Not found", 404);
        }
        return $this->json($entity);
    }

    /**
     * @Rest\Post("")
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(Request $request)
    {
        $entity = new Artist();

        $form = $this->createForm(ArtistType::class, $entity);
        $form->submit($request->request->all());

        if ($form->isValid()) {

            // Vérification de doublon d'un artiste dans la bdd
            $existing = $this->repository->findOneBy(
                [
                    'name' => $entity->getName(),
                ]
            );
            if ($existing != null) {
                return $this->json("Duplicate artist", 409);
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return $this->json($entity);
        }

        return $this->json($form, 400);
    }

    /**
     * @Rest\Put("/{id}")
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(int $id, Request $request)
    {
        $entity = $this->repository->find($id);

        if ($entity == null) {
            return $this->json("Not found", 404);
        }

        $form = $this->createForm(ArtistType::class, $entity);
        $form->submit($request->request->all());

        if ($form->isValid()) {

            // Vérification de doublon d'un artiste dans la bdd
            $existing = $this->repository->findOneBy(
                [
                    'name' => $entity->getName()
                ]
            );
            if ($existing != null && $existing->getId() != $entity->getId()) {
                $this->json("Duplicate artist", 409);
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return $this->json($entity);
        }

        return $this->json($form, 400);
    }

    /**
     * @Rest\Delete("/{id}")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(int $id) {
        $entity = $this->repository->find($id);
        if ($entity == null) {
            return $this->json("Not found", 404);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $this->json("L'artiste a bien été supprimé");
    }
}
