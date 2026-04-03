<?php

namespace App\Controller;

use App\Entity\SecteurValide;
use App\Form\SecteurValideType;
use App\Repository\SecteurValideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\SecteurValideHistorique;

#[Route('/secteur/valide')]
final class SecteurValideController extends AbstractController
{
    #[Route(name: 'app_secteur_valide_index', methods: ['GET'])]
    public function index(SecteurValideRepository $secteurValideRepository): Response
    {
        return $this->render('secteur_valide/index.html.twig', [
            'secteur_valides' => $secteurValideRepository->findAllNonDeleted(),
        ]);
    }

    #[Route('/new', name: 'app_secteur_valide_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $secteurValide = new SecteurValide();
        $form = $this->createForm(SecteurValideType::class, $secteurValide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secteurValide->setCreatedBy($this->getUser()->getNom()); // Stocke le créateur
            $entityManager->persist($secteurValide);
            $entityManager->flush();

            return $this->redirectToRoute('app_secteur_valide_index');
        }
        return $this->render('secteur_valide/new.html.twig', [
            'secteur_valide' => $secteurValide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_secteur_valide_show', methods: ['GET'])]
    public function show(SecteurValide $secteurValide): Response
    {
        return $this->render('secteur_valide/show.html.twig', [
            'secteur_valide' => $secteurValide,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_secteur_valide_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SecteurValide $secteurValide, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SecteurValideType::class, $secteurValide);
        $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {
        // Crée une nouvelle ligne d'historique
        $historique = new SecteurValideHistorique();
        $historique->setModifiePar($this->getUser()->getNom());
        $secteurValide->addHistorique($historique);

        $entityManager->flush();

        return $this->redirectToRoute('app_secteur_valide_index');
        }

        return $this->render('secteur_valide/edit.html.twig', [
            'form' => $form,
            'secteur_valide' => $secteurValide,
        ]);
    }

        #[Route('/{id}', name: 'app_secteur_valide_delete', methods: ['POST'])]
        public function delete(Request $request, SecteurValide $secteurValide, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete'.$secteurValide->getId(), $request->getPayload()->getString('_token'))) {
                // On crée la date au format string 'Y-m-d H:i:s' (ou 'Y-m-d' si tu préfères)
                $dateNow = (new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
                $secteurValide->setDeletedAt($dateNow); // Marquer comme supprimé en string
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_secteur_valide_index', [], Response::HTTP_SEE_OTHER);
        }
}
