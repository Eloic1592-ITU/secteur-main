<?php

namespace App\Controller;

use App\Entity\OrigineValide;
use App\Form\OrigineValideType;
use App\Repository\OrigineValideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\OrigineValideHistorique;

#[Route('/origine/valide')]
final class OrigineValideController extends AbstractController
{

    #[Route(name: 'app_origine_valide_index', methods: ['GET'])]
    public function index(OrigineValideRepository $origineValideRepository): Response
    {
        return $this->render('origine_valide/index.html.twig', [
            'origine_valides' => $origineValideRepository->findAllNonDeleted(),
        ]);
    }

    #[Route('/new', name: 'app_origine_valide_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $origineValide = new OrigineValide();
        $form = $this->createForm(OrigineValideType::class, $origineValide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $origineValide->setCreatedBy($this->getUser()->getNom()); // Stocke le créateur
            $entityManager->persist($origineValide);
            $entityManager->flush();

            return $this->redirectToRoute('app_origine_valide_index');
        }

        return $this->render('origine_valide/new.html.twig', [
            'origine_valide' => $origineValide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_origine_valide_show', methods: ['GET'])]
    public function show(OrigineValide $origineValide): Response
    {
        return $this->render('origine_valide/show.html.twig', [
            'origine_valide' => $origineValide,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_origine_valide_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrigineValide $origineValide, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrigineValideType::class, $origineValide);
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
        // Crée une nouvelle ligne d'historique
        $historique = new OrigineValideHistorique();
        $historique->setModifiePar($this->getUser()->getNom());
        $origineValide->addHistorique($historique);

        $entityManager->flush();

        return $this->redirectToRoute('app_origine_valide_index');
        }

        return $this->render('origine_valide/edit.html.twig', [
            'form' => $form,
            'origine_valide' => $origineValide,
        ]);
        }

        #[Route('/{id}', name: 'app_origine_valide_delete', methods: ['POST'])]
        public function delete(Request $request, OrigineValide $origineValide, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete'.$origineValide->getId(), $request->request->get('_token'))) {
                // Convertir la date actuelle en string format 'Y-m-d H:i:s'
                $dateString = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
                
                $origineValide->setDeletedAt($dateString); // Marquer comme supprimé
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_origine_valide_index', [], Response::HTTP_SEE_OTHER);
        }
}
