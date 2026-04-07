<?php

namespace App\Controller;

use App\Entity\MvtBonsValide;
use App\Form\MvtBonsValideType;
use App\Repository\MvtBonsValideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mvt/bons/valide')]
final class MvtBonsValideController extends AbstractController
{
    #[Route(name: 'app_mvt_bons_valide_index', methods: ['GET'])]
    public function index(MvtBonsValideRepository $mvtBonsValideRepository): Response
    {
        return $this->render('mvt_bons_valide/index.html.twig', [
            'mvt_bons_valides' => $mvtBonsValideRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mvt_bons_valide_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mvtBonsValide = new MvtBonsValide();
        $form = $this->createForm(MvtBonsValideType::class, $mvtBonsValide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Bypasse Doctrine et on insère manuellement
            $conn = $entityManager->getConnection();

            // Fonction pour formater les valeurs numériques
            $num = fn(?string $v) => $v !== null ? number_format((float)$v, 2, '.', '') : 'NULL';
            $int = fn(?int $v)    => $v !== null ? (int)$v : 'NULL';
            $str = fn(?string $v) => $v !== null ? "'" . str_replace("'", "''", $v) . "'" : 'NULL';

            $sql = sprintf(
                "INSERT INTO MVT_BONS_VALIDE 
                    (NUMMVT, D_BONS, NUMSEM, NBRSS, NBRSS0, NBRSS1, NBRSS2,
                     MAN, MAN1, MAN2, MSM, MSM1, MSM2,
                     MAD, MAD1, MAD2, TXPMIN, TXPMAX,
                     TXAMIN, TXAMAX, TXMP)
                 VALUES 
                    (seq_MVT_BONS_VALIDE.NEXTVAL, %s, %s, %s, %s, %s, %s,
                     %s, %s, %s, %s, %s, %s,
                     %s, %s, %s, %s, %s,
                     %s, %s, %s)",
                $str($mvtBonsValide->getDBONS()),
                $int($mvtBonsValide->getNUMSEM()),
                $num($mvtBonsValide->getNBRSS()),
                $num($mvtBonsValide->getNBRSS0()),
                $num($mvtBonsValide->getNBRSS1()),
                $num($mvtBonsValide->getNBRSS2()),
                $num($mvtBonsValide->getMAN()),
                $num($mvtBonsValide->getMAN1()),
                $num($mvtBonsValide->getMAN2()),
                $num($mvtBonsValide->getMSM()),
                $num($mvtBonsValide->getMSM1()),
                $num($mvtBonsValide->getMSM2()),
                $num($mvtBonsValide->getMAD()),
                $num($mvtBonsValide->getMAD1()),
                $num($mvtBonsValide->getMAD2()),
                $num($mvtBonsValide->getTXPMIN()),
                $num($mvtBonsValide->getTXPMAX()),
                $num($mvtBonsValide->getTXAMIN()),
                $num($mvtBonsValide->getTXAMAX()),
                $num($mvtBonsValide->getTXMP())
            );

            $conn->executeStatement($sql);

            $this->addFlash('success', 'L\'enregistrement a été créé avec succès.');

            return $this->redirectToRoute('app_mvt_bons_valide_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mvt_bons_valide/new.html.twig', [
            'mvt_bons_valide' => $mvtBonsValide,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_mvt_bons_valide_show', methods: ['GET'])]
    public function show(MvtBonsValide $mvtBonsValide): Response
    {
        return $this->render('mvt_bons_valide/show.html.twig', [
            'mvt_bons_valide' => $mvtBonsValide,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mvt_bons_valide_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MvtBonsValide $mvtBonsValide, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MvtBonsValideType::class, $mvtBonsValide);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            // Bypasse Doctrine et on update manuellement
            $conn = $entityManager->getConnection();
    
            // Fonctions pour formater les valeurs
            $num = fn(?string $v) => $v !== null ? number_format((float)$v, 2, '.', '') : 'NULL';
            $int = fn(?int $v)    => $v !== null ? (int)$v : 'NULL';
            $str = fn(?string $v) => $v !== null ? "'" . str_replace("'", "''", $v) . "'" : 'NULL';
    
            $sql = sprintf(
                "UPDATE MVT_BONS_VALIDE SET
                    D_BONS  = %s,
                    NUMSEM  = %s,
                    NBRSS   = %s,
                    NBRSS0  = %s,
                    NBRSS1  = %s,
                    NBRSS2  = %s,
                    MAN     = %s,
                    MAN1    = %s,
                    MAN2    = %s,
                    MSM     = %s,
                    MSM1    = %s,
                    MSM2    = %s,
                    MAD     = %s,
                    MAD1    = %s,
                    MAD2    = %s,
                    TXPMIN  = %s,
                    TXPMAX  = %s,
                    TXAMIN  = %s,
                    TXAMAX  = %s,
                    TXMP    = %s
                WHERE NUMMVT = %s",
                $str($mvtBonsValide->getDBONS()),
                $int($mvtBonsValide->getNUMSEM()),
                $num($mvtBonsValide->getNBRSS()),
                $num($mvtBonsValide->getNBRSS0()),
                $num($mvtBonsValide->getNBRSS1()),
                $num($mvtBonsValide->getNBRSS2()),
                $num($mvtBonsValide->getMAN()),
                $num($mvtBonsValide->getMAN1()),
                $num($mvtBonsValide->getMAN2()),
                $num($mvtBonsValide->getMSM()),
                $num($mvtBonsValide->getMSM1()),
                $num($mvtBonsValide->getMSM2()),
                $num($mvtBonsValide->getMAD()),
                $num($mvtBonsValide->getMAD1()),
                $num($mvtBonsValide->getMAD2()),
                $num($mvtBonsValide->getTXPMIN()),
                $num($mvtBonsValide->getTXPMAX()),
                $num($mvtBonsValide->getTXAMIN()),
                $num($mvtBonsValide->getTXAMAX()),
                $num($mvtBonsValide->getTXMP()),
                $int($mvtBonsValide->getNUMMVT()) 
            );
    
            $conn->executeStatement($sql);
    
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
    
        $now = new \DateTime();
        $conn->executeStatement(
            sprintf(
                "INSERT INTO MVT_BONS_VALIDE_HISTORIQUE 
                    (ID,MVT_BONS_VALIDE_ID, DATE_MODIFICATION, MODIFIE_PAR)
                 VALUES 
                    (seq_MVT_BONS_VALIDE_HISTORIQUE.NEXTVAL,%d, TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS'), '%s')",
                $mvtBonsValide->getNUMMVT(),
                $now->format('Y-m-d H:i:s'),
                str_replace("'", "''", $currentUser->getMatricule())
            )
        
        );
            return $this->redirectToRoute('app_mvt_bons_valide_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('mvt_bons_valide/edit.html.twig', [
            'mvt_bons_valide' => $mvtBonsValide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mvt_bons_valide_delete', methods: ['POST'])]
    public function delete(Request $request, MvtBonsValide $mvtBonsValide, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mvtBonsValide->getNUMMVT(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($mvtBonsValide);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mvt_bons_valide_index', [], Response::HTTP_SEE_OTHER);
    }
}
