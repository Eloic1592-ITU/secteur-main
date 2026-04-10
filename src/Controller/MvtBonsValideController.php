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
use App\Form\DataTransformer\CommaToPointTransformer;

#[Route('/mvt/bons/valide')]
final class MvtBonsValideController extends AbstractController
{
    #[Route(name: 'app_mvt_bons_valide_index', methods: ['GET'],defaults: ['title' => 'Mouvement Bons Validés'])]
    public function index(MvtBonsValideRepository $mvtBonsValideRepository): Response
    {
        return $this->render('mvt_bons_valide/index.html.twig', [
            'mvt_bons_valides' => $mvtBonsValideRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mvt_bons_valide_new', methods: ['GET', 'POST'],defaults: ['title' => 'Ajouter un Mouvement Bons Validés'])]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mvtBonsValide = new MvtBonsValide();
        $form = $this->createForm(MvtBonsValideType::class, $mvtBonsValide);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $conn = $entityManager->getConnection();
    
            // Récupérer la date des bons
            $dBons = $form->get('D_BONS')->getData();
            $dBonsStr = $dBons instanceof \DateTime ? $dBons->format('d/m/y') : null;
    
            if (!$dBonsStr) {
                $this->addFlash('error', 'La date des bons est obligatoire.');
                return $this->render('mvt_bons_valide/new.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
            // Mapping des champs taux → NUMSEM
            $tauxMapping = [
                1 => $form->get('taux_4_semaines')->getData(),
                2 => $form->get('taux_12_semaines')->getData(),
                3 => $form->get('taux_24_semaines')->getData(),
                4 => $form->get('taux_26_semaines')->getData(),
                5 => $form->get('taux_52_semaines')->getData(),
            ];
    
            // Récupérer les offres compétitives
            $montantAnnonce = $form->get('montant_annonce')->getData();
            $montantSoumis = $form->get('montant_soumis')->getData();
            $montantAdjuge = $form->get('montant_adjuge')->getData();
    
            // Vérifier si au moins un taux est saisi
            $hasTaux = array_filter($tauxMapping, fn($v) => $v !== null && $v !== '') !== [];
    
            // Vérifier si les offres compétitives sont complètes
            $hasOffres = ($montantAnnonce !== null && $montantAnnonce !== '') &&
                         ($montantSoumis !== null && $montantSoumis !== '') &&
                         ($montantAdjuge !== null && $montantAdjuge !== '');
    
            // Validation : les deux sections doivent être remplies
            if (!$hasTaux || !$hasOffres) {
                $this->addFlash('error', 'Veuillez remplir les deux sections (Taux Moyen Pondéré ET Offres Compétitives).');
                return $this->render('mvt_bons_valide/new.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
            // Démarrer une transaction
            $conn->beginTransaction();
    
            try {
                // ========== INSERTION DANS BONS_VALIDE ==========
                // Insérer dans BONS_VALIDE
                $sqlBonsValide = sprintf(
                    "INSERT INTO BONS_VALIDE (DDONS) VALUES (TO_DATE('%s', 'DD/MM/YY'))",
                    $dBonsStr
                );
                $conn->executeStatement($sqlBonsValide);
    
                // ========== INSERTION DES TAUX MOYENS PONDÉRÉS ==========
                $insertedIds = [];
                $insertCount = 0;
    
                foreach ($tauxMapping as $numSem => $tauxValue) {
                    if ($tauxValue !== null && $tauxValue !== '') {
                        $txmp = number_format((float)$tauxValue, 2, '.', '');
    
                        $sql = sprintf(
                            "INSERT INTO MVT_BONS_VALIDE 
                                (NUMMVT, D_BONS, NUMSEM, NBRSS, NBRSS0, NBRSS1, NBRSS2,
                                 MAN, MAN1, MAN2, MSM, MSM1, MSM2,
                                 MAD, MAD1, MAD2, TXPMIN, TXPMAX,
                                 TXAMIN, TXAMAX, TXMP)
                             VALUES 
                                (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), %d, NULL, NULL, NULL, NULL,
                                 NULL, NULL, NULL, NULL, NULL, NULL,
                                 NULL, NULL, NULL, NULL, NULL,
                                 NULL, NULL, %s)",
                            $dBonsStr,
                            $numSem,
                            $txmp
                        );
    
                        $conn->executeStatement($sql);
    
                        // Récupérer le NUMMVT inséré
                        $lastId = $conn->fetchOne("SELECT seq_MVT_BONS_VALIDE.CURRVAL FROM DUAL");
                        $insertedIds[] = $lastId;
                        $insertCount++;
                    }
                }
    
                // ========== INSERTION DES OFFRES COMPÉTITIVES ==========
                if ($hasOffres) {
    
                    $man = number_format((float)$montantAnnonce, 2, '.', '');
                    $man1 = $man; // MAN1 = MAN (identique)
                    $man2 = '0.00'; // MAN2 = 0
                    $msm1 = number_format((float)$montantSoumis, 2, '.', '');
                    $mad1 = number_format((float)$montantAdjuge, 2, '.', '');
    
                    $sql = sprintf(
                        "INSERT INTO MVT_BONS_VALIDE 
                            (NUMMVT, D_BONS, NUMSEM, NBRSS, NBRSS0, NBRSS1, NBRSS2,
                             MAN, MAN1, MAN2, MSM, MSM1, MSM2,
                             MAD, MAD1, MAD2, TXPMIN, TXPMAX,
                             TXAMIN, TXAMAX, TXMP)
                         VALUES 
                            (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), 6, NULL, NULL, NULL, NULL,
                             %s, %s, %s, NULL, %s, NULL,
                             NULL, %s, NULL, NULL, NULL,
                             NULL, NULL, NULL)",
                        $dBonsStr,
                        $man,   // MAN
                        $man1,  // MAN1 (identique à MAN)
                        $man2,  // MAN2 = 0
                        $msm1,  // MSM1
                        $mad1   // MAD1
                    );
    
                    $conn->executeStatement($sql);
    
                    // Récupérer le NUMMVT inséré
                    $lastId = $conn->fetchOne("SELECT seq_MVT_BONS_VALIDE.CURRVAL FROM DUAL");
                    $insertedIds[] = $lastId;
                    $insertCount++;
                }
    
                // ========== INSERTION DANS L'HISTORIQUE ==========
                if (!empty($insertedIds)) {
    
                    $currentUser = $this->getUser() ? $this->getUser()->getUserIdentifier() : 'Système';
                    $currentDate = (new \DateTime())->format('d/m/y H:i:s');
    
                    foreach ($insertedIds as $nummvt) {
                        $sqlHistorique = sprintf(
                            "INSERT INTO MVT_BONS_VALIDE_HISTORIQUE 
                                (ID, MVT_BONS_VALIDE_ID, DATE_MODIFICATION, MODIFIE_PAR)
                             VALUES 
                                (seq_MVT_BONS_VALIDE_HISTORIQUE.NEXTVAL, %d, TO_DATE('%s', 'DD/MM/YY HH24:MI:SS'), '%s')",
                            $nummvt,
                            $currentDate,
                            str_replace("'", "''", $currentUser)
                        );
    
                        $conn->executeStatement($sqlHistorique);
                    }
                }
    
                // Valider la transaction
                $conn->commit();
    
                $this->addFlash('success', "$insertCount enregistrement(s) créé(s) avec succès dans MVT_BONS_VALIDE et historique.");
    
            } catch (\Exception $e) {
                // Annuler la transaction en cas d'erreur
                $conn->rollBack();
    
                $this->addFlash('error', 'Erreur lors de l\'insertion : ' . $e->getMessage());
    
                return $this->render('mvt_bons_valide/new.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
            return $this->redirectToRoute('app_mvt_bons_valide_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('mvt_bons_valide/new.html.twig', [
            'mvt_bons_valide' => $mvtBonsValide,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mvt_bons_valide_show', methods: ['GET'],defaults: ['title' => 'Détails du Mouvement Bons Validés'])]
    public function show(MvtBonsValide $mvtBonsValide): Response
    {
        return $this->render('mvt_bons_valide/show.html.twig', [
            'mvt_bons_valide' => $mvtBonsValide,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mvt_bons_valide_edit', methods: ['GET', 'POST'],defaults: ['title' => 'Modifier le Mouvement Bons Validés'])]
    public function edit(Request $request, MvtBonsValide $mvtBonsValide, EntityManagerInterface $entityManager): Response
    {
        $conn = $entityManager->getConnection();
        
        // Récupérer les données actuelles pour pré-remplir le formulaire
        $nummvt = $mvtBonsValide->getNUMMVT();
        $dBonsCurrent = $mvtBonsValide->getDBONS();
        
        // Récupérer tous les enregistrements liés à cette date
        $sql = sprintf(
            "SELECT NUMSEM, TXMP, MAN, MAN1, MAN2, MSM1, MAD1 
             FROM MVT_BONS_VALIDE 
             WHERE D_BONS = TO_DATE('%s', 'DD/MM/YY')
             ORDER BY NUMSEM",
            $dBonsCurrent
        );
        
        $records = $conn->fetchAllAssociative($sql);
    
        // Organiser les données par NUMSEM
        $currentData = [];
        foreach ($records as $record) {
            $currentData[$record['NUMSEM']] = $record;
        }
    
        $form = $this->createForm(MvtBonsValideType::class, $mvtBonsValide);
        $transformer = new CommaToPointTransformer();
    
        // Pré-remplir le formulaire avec les données existantes
        if ($request->getMethod() === 'GET') {
            // Convertir DD/MM/YY (ex: 27/03/26) en objet DateTime
            $dateObject = null;
            if ($dBonsCurrent) {
                try {
                    $dateObject = \DateTime::createFromFormat('d/m/y', $dBonsCurrent);
                } catch (\Exception $e) {
                    $dateObject = \DateTime::createFromFormat('Y-m-d', $dBonsCurrent);
                }
            }
    
            $form->get('D_BONS')->setData($dateObject);
    
            // Pré-remplir les taux
            if (isset($currentData[1]['TXMP'])) {
                $form->get('taux_4_semaines')->setData(
                    $transformer->toFloat($currentData[1]['TXMP'])
                );
            }
    
            if (isset($currentData[2]['TXMP'])) {
                $form->get('taux_12_semaines')->setData(
                    $transformer->toFloat($currentData[2]['TXMP'])
                );
            }
    
            if (isset($currentData[3]['TXMP'])) {
                $form->get('taux_24_semaines')->setData(
                    $transformer->toFloat($currentData[3]['TXMP'])
                );
            }
    
            if (isset($currentData[4]['TXMP'])) {
                $form->get('taux_26_semaines')->setData(
                    $transformer->toFloat($currentData[4]['TXMP'])
                );
            }
    
            if (isset($currentData[5]['TXMP'])) {
                $form->get('taux_52_semaines')->setData(
                    $transformer->toFloat($currentData[5]['TXMP'])
                );
            }
    
            if (isset($currentData[6])) {
                if (isset($currentData[6]['MAN'])) {
                    $form->get('montant_annonce')->setData(
                        $transformer->toFloat($currentData[6]['MAN'])
                    );
                }
    
                if (isset($currentData[6]['MSM1'])) {
                    $form->get('montant_soumis')->setData(
                        $transformer->toFloat($currentData[6]['MSM1'])
                    );
                }
    
                if (isset($currentData[6]['MAD1'])) {
                    $form->get('montant_adjuge')->setData(
                        $transformer->toFloat($currentData[6]['MAD1'])
                    );
                }
            }
        }
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            // Récupérer la nouvelle date des bons et convertir en DD/MM/YY
            $dBons = $form->get('D_BONS')->getData();
            $dBonsStr = $dBons instanceof \DateTime ? $dBons->format('d/m/y') : null;
    
            if (!$dBonsStr) {
                $this->addFlash('error', 'La date des bons est obligatoire.');
                return $this->render('mvt_bons_valide/edit.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
            // Mapping des champs taux → NUMSEM
            $tauxMapping = [
                1 => $form->get('taux_4_semaines')->getData(),
                2 => $form->get('taux_12_semaines')->getData(),
                3 => $form->get('taux_24_semaines')->getData(),
                4 => $form->get('taux_26_semaines')->getData(),
                5 => $form->get('taux_52_semaines')->getData(),
            ];
    
            // Récupérer les offres compétitives
            $montantAnnonce = $form->get('montant_annonce')->getData();
            $montantSoumis = $form->get('montant_soumis')->getData();
            $montantAdjuge = $form->get('montant_adjuge')->getData();
    
            // ✅ NOUVELLE VALIDATION : Au moins UN champ doit être rempli (pas tous obligatoires)
            $hasTaux = array_filter($tauxMapping, fn($v) => $v !== null && $v !== '') !== [];
            $hasOffres = ($montantAnnonce !== null && $montantAnnonce !== '') ||
                         ($montantSoumis !== null && $montantSoumis !== '') ||
                         ($montantAdjuge !== null && $montantAdjuge !== '');
    
            if (!$hasTaux && !$hasOffres) {
                $this->addFlash('error', 'Veuillez remplir au moins un champ.');
                return $this->render('mvt_bons_valide/edit.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
            // Démarrer une transaction
            $conn->beginTransaction();
    
            try {
                $updatedIds = [];
                $updateCount = 0;
    
                // ========== SUPPRIMER LES ANCIENS ENREGISTREMENTS ==========
                // Supprimer dans BONS_VALIDE (avec l'ancienne date)
                $sqlDeleteBonsValide = sprintf(
                    "DELETE FROM BONS_VALIDE WHERE DDONS = TO_DATE('%s', 'DD/MM/YY')",
                    $dBonsCurrent
                );
                $conn->executeStatement($sqlDeleteBonsValide);
    
                // Supprimer tous les enregistrements MVT_BONS_VALIDE avec l'ancienne date
                $sqlDelete = sprintf(
                    "DELETE FROM MVT_BONS_VALIDE WHERE D_BONS = TO_DATE('%s', 'DD/MM/YY')",
                    $dBonsCurrent
                );
                $conn->executeStatement($sqlDelete);
    
                // ========== INSERTION DANS BONS_VALIDE ==========
                $sqlBonsValide = sprintf(
                    "INSERT INTO BONS_VALIDE (DDONS) VALUES (TO_DATE('%s', 'DD/MM/YY'))",
                    $dBonsStr
                );
                $conn->executeStatement($sqlBonsValide);
    
                // ========== INSERTION DES TAUX MOYENS PONDÉRÉS ==========
                foreach ($tauxMapping as $numSem => $tauxValue) {
                    // ✅ Insérer SEULEMENT si la valeur n'est pas vide
                    if ($tauxValue !== null && $tauxValue !== '') {
                        $txmp = number_format((float)$tauxValue, 2, '.', '');
    
                        $sql = sprintf(
                            "INSERT INTO MVT_BONS_VALIDE 
                                (NUMMVT, D_BONS, NUMSEM, NBRSS, NBRSS0, NBRSS1, NBRSS2,
                                 MAN, MAN1, MAN2, MSM, MSM1, MSM2,
                                 MAD, MAD1, MAD2, TXPMIN, TXPMAX,
                                 TXAMIN, TXAMAX, TXMP)
                             VALUES 
                                (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), %d, NULL, NULL, NULL, NULL,
                                 NULL, NULL, NULL, NULL, NULL, NULL,
                                 NULL, NULL, NULL, NULL, NULL,
                                 NULL, NULL, %s)",
                            $dBonsStr,
                            $numSem,
                            $txmp
                        );
    
                        $conn->executeStatement($sql);
    
                        // Récupérer le NUMMVT inséré
                        $lastId = $conn->fetchOne("SELECT seq_MVT_BONS_VALIDE.CURRVAL FROM DUAL");
                        $updatedIds[] = $lastId;
                        $updateCount++;
                    }
                }
    
                // ========== INSERTION DES OFFRES COMPÉTITIVES ==========
                // ✅ Insérer SEULEMENT si AU MOINS UN montant est rempli
                if ($hasOffres) {
                    
                    // ✅ Utiliser les valeurs existantes si les champs sont vides
                    $man = ($montantAnnonce !== null && $montantAnnonce !== '') 
                        ? number_format((float)$montantAnnonce, 2, '.', '') 
                        : (isset($currentData[6]['MAN']) ? $currentData[6]['MAN'] : 'NULL');
                        
                    $man1 = $man; // MAN1 = MAN (identique)
                    $man2 = '0.00'; // MAN2 = 0
                    
                    $msm1 = ($montantSoumis !== null && $montantSoumis !== '') 
                        ? number_format((float)$montantSoumis, 2, '.', '') 
                        : (isset($currentData[6]['MSM1']) ? $currentData[6]['MSM1'] : 'NULL');
                        
                    $mad1 = ($montantAdjuge !== null && $montantAdjuge !== '') 
                        ? number_format((float)$montantAdjuge, 2, '.', '') 
                        : (isset($currentData[6]['MAD1']) ? $currentData[6]['MAD1'] : 'NULL');
    
                    $sql = sprintf(
                        "INSERT INTO MVT_BONS_VALIDE 
                            (NUMMVT, D_BONS, NUMSEM, NBRSS, NBRSS0, NBRSS1, NBRSS2,
                             MAN, MAN1, MAN2, MSM, MSM1, MSM2,
                             MAD, MAD1, MAD2, TXPMIN, TXPMAX,
                             TXAMIN, TXAMAX, TXMP)
                         VALUES 
                            (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), 6, NULL, NULL, NULL, NULL,
                             %s, %s, %s, NULL, %s, NULL,
                             NULL, %s, NULL, NULL, NULL,
                             NULL, NULL, NULL)",
                        $dBonsStr,
                        $man,   // MAN
                        $man1,  // MAN1 (identique à MAN)
                        $man2,  // MAN2 = 0
                        $msm1,  // MSM1
                        $mad1   // MAD1
                    );
    
                    $conn->executeStatement($sql);
    
                    // Récupérer le NUMMVT inséré
                    $lastId = $conn->fetchOne("SELECT seq_MVT_BONS_VALIDE.CURRVAL FROM DUAL");
                    $updatedIds[] = $lastId;
                    $updateCount++;
                }
    
                // ========== INSERTION DANS L'HISTORIQUE ==========
                if (!empty($updatedIds)) {
    
                    /** @var \App\Entity\User $currentUser */
                    $currentUser = $this->getUser();
                    $userIdentifier = $currentUser ? $currentUser->getMatricule() : 'Système';
                    $currentDate = (new \DateTime())->format('d/m/y H:i:s');
    
                    foreach ($updatedIds as $nummvt) {
                        $sqlHistorique = sprintf(
                            "INSERT INTO MVT_BONS_VALIDE_HISTORIQUE 
                                (ID, MVT_BONS_VALIDE_ID, DATE_MODIFICATION, MODIFIE_PAR)
                             VALUES 
                                (seq_MVT_BONS_VALIDE_HISTORIQUE.NEXTVAL, %d, TO_DATE('%s', 'DD/MM/YY HH24:MI:SS'), '%s')",
                            $nummvt,
                            $currentDate,
                            str_replace("'", "''", $userIdentifier)
                        );
    
                        $conn->executeStatement($sqlHistorique);
                    }
                }
    
                // Valider la transaction
                $conn->commit();
    
                $this->addFlash('success', "$updateCount enregistrement(s) modifié(s) avec succès.");
    
            } catch (\Exception $e) {
                // Annuler la transaction en cas d'erreur
                $conn->rollBack();
    
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
    
                return $this->render('mvt_bons_valide/edit.html.twig', [
                    'mvt_bons_valide' => $mvtBonsValide,
                    'form' => $form,
                ]);
            }
    
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
