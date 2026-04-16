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
    // Filtre les donnees ayant les meme dates
    #[Route(name: 'app_mvt_bons_valide_index', methods: ['GET'],defaults: ['title' => 'Mouvement Bons Validés'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conn = $entityManager->getConnection();
    
        $dateFilter = $request->query->get('date_filter');
    
    if ($dateFilter) {

        $date = \DateTime::createFromFormat('Y-m-d', $dateFilter);
        $dateFormatted = $date ? $date->format('d/m/y') : null;

        $sql = sprintf(
            "SELECT * FROM MVT_BONS_VALIDE 
             WHERE D_BONS ='%s'
             ORDER BY NUMSEM",
            $dateFormatted
        );

        $rows = $conn->fetchAllAssociative($sql);

        // REGROUPEMENT
        $grouped = [];

        foreach ($rows as $row) {

            $dateKey = $row['D_BONS'];

            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [
                    'D_BONS' => $row['D_BONS'],
                    'taux' => [],
                    'MAN' => null,
                    'MSM1' => null,
                    'MAD1' => null,
                    'id' => $row['NUMMVT']
                ];
            }

            // TAUX (NUMSEM 1 à 5)
            if ($row['NUMSEM'] >= 1 && $row['NUMSEM'] <= 5) {
                $grouped[$dateKey]['taux'][$row['NUMSEM']] = $row['TXMP'];
            }

            // OFFRES (NUMSEM = 6)
            if ($row['NUMSEM'] == 6) {
                $grouped[$dateKey]['MAN'] = $row['MAN'];
                $grouped[$dateKey]['MSM1'] = $row['MSM1'];
                $grouped[$dateKey]['MAD1'] = $row['MAD1'];
            }
        }

        // IMPORTANT → reset index pour Twig
        $mvt_bons_valides = array_values($grouped);  
        } else {
            $mvt_bons_valides = [];
        }

        return $this->render('mvt_bons_valide/index.html.twig', [
            'mvt_bons_valides' => $mvt_bons_valides,
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

            // Récupérer l'utilisateur connecté
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            $userIdentifier = $currentUser ? $currentUser->getNom() : 'Système';
            
            // Date actuelle pour CREATED_AT et UPDATED_AT
            $currentDate = (new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
    
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
                $this->addFlash('danger', 'Veuillez remplir tous les champs des deux sections (Taux ET Offres).');
                return $this->redirectToRoute('app_mvt_bons_valide_new');
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
                                 TXAMIN, TXAMAX, TXMP,
                                CREATED_AT, UPDATED_AT, CREATED_BY)
                            VALUES 
                                (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), %d, NULL, NULL, NULL, NULL,
                                NULL, NULL, NULL, NULL, NULL, NULL,
                                NULL, NULL, NULL, NULL, NULL,
                                NULL, NULL, %s,
                                TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS'),
                                TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS'),
                                '%s')",
                            $dBonsStr,
                            $numSem,
                            $txmp,
                            $currentDate,
                            $currentDate,
                            str_replace("'", "''", $userIdentifier)
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
                            TXAMIN, TXAMAX, TXMP,
                            CREATED_AT, UPDATED_AT, CREATED_BY)
                        VALUES 
                            (seq_MVT_BONS_VALIDE.NEXTVAL, TO_DATE('%s', 'DD/MM/YY'), 6, NULL, NULL, NULL, NULL,
                            %s, %s, %s, NULL, %s, NULL,
                            NULL, %s, NULL, NULL, NULL,
                            NULL, NULL, NULL,
                            TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS'),
                            TO_TIMESTAMP('%s', 'YYYY-MM-DD HH24:MI:SS'),
                            '%s')",
                        $dBonsStr,
                        $man,   // MAN
                        $man1,  // MAN1 (identique à MAN)
                        $man2,  // MAN2 = 0
                        $msm1,  // MSM1
                        $mad1,   // MAD1
                        $currentDate,
                        $currentDate,
                        str_replace("'", "''", $userIdentifier)
                    );
    
                    $conn->executeStatement($sql);
    
                    // Récupérer le NUMMVT inséré
                    $lastId = $conn->fetchOne("SELECT seq_MVT_BONS_VALIDE.CURRVAL FROM DUAL");
                    $insertedIds[] = $lastId;
                    $insertCount++;
                }
    
                // ========== INSERTION DANS L'HISTORIQUE ==========
                // if (!empty($insertedIds)) {
    
                //     $currentUser = $this->getUser() ? $this->getUser()->getUserIdentifier() : 'Système';
                //     $currentDate = (new \DateTime())->format('d/m/y H:i:s');
    
                //     foreach ($insertedIds as $nummvt) {
                //         $sqlHistorique = sprintf(
                //             "INSERT INTO MVT_BONS_VALIDE_HISTORIQUE 
                //                 (ID, MVT_BONS_VALIDE_ID, DATE_MODIFICATION, MODIFIE_PAR)
                //              VALUES 
                //                 (seq_MVT_BONS_VALIDE_HISTORIQUE.NEXTVAL, %d, TO_DATE('%s', 'DD/MM/YY HH24:MI:SS'), '%s')",
                //             $nummvt,
                //             $currentDate,
                //             str_replace("'", "''", $currentUser)
                //         );
    
                //         $conn->executeStatement($sqlHistorique);
                //     }
                // }
    
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
    public function show(MvtBonsValide $mvtBonsValide,EntityManagerInterface $entityManager): Response {

        $conn = $entityManager->getConnection();

        $dateKey = $mvtBonsValide->getDBONS();
 
        $dateFilter = $dateKey ? $dateKey : null;

        $mvt_bons_valides = [];

        if ($dateFilter) {

            // =========================
            // 1. DONNÉES MOUVEMENTS
            // =========================
            $sql = sprintf(
                "SELECT * FROM MVT_BONS_VALIDE 
                WHERE D_BONS ='%s'
                 ORDER BY NUMSEM",
                $dateFilter
            );

            $rows = $conn->fetchAllAssociative($sql);
            // dd($rows);

            // =========================
            // 2. HISTORIQUES
            // =========================
            $sqlHist = "
                SELECT h.*
                FROM MVT_BONS_VALIDE_HISTORIQUE h
                INNER JOIN MVT_BONS_VALIDE m ON m.NUMMVT = h.MVT_BONS_VALIDE_ID
                WHERE m.D_BONS = :dateFilter
                ORDER BY h.DATE_MODIFICATION DESC FETCH FIRST 5 ROWS ONLY
            ";

            $stmtHist = $conn->prepare($sqlHist);
            $resultHist = $stmtHist->executeQuery([
                'dateFilter' => $dateFilter
            ]);

            $histories = $resultHist->fetchAllAssociative();
            // dd($histories);

            // =========================
            // 3. GROUP HISTORIQUES
            // =========================
            $histGrouped = [];

            foreach ($histories as $h) {

                $key = $h['D_BONS'] ?? $dateFilter;

                if (!isset($histGrouped[$key])) {
                    $histGrouped[$key] = [];
                }

                $histGrouped[$key][] = $h;
            }

            // =========================
            // 4. GROUP MOUVEMENTS
            // =========================
            $grouped = [];

            foreach ($rows as $row) {

                $key = $row['D_BONS'];

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'D_BONS' => $row['D_BONS'],
                        'taux' => [],
                        'MAN' => null,
                        'MSM1' => null,
                        'MAD1' => null,
                        'id' => $row['NUMMVT'],
                        'historiques' => []
                    ];
                }

                // TAUX (NUMSEM 1-5)
                if ($row['NUMSEM'] >= 1 && $row['NUMSEM'] <= 5) {
                    $grouped[$key]['taux'][$row['NUMSEM']] = $row['TXMP'];
                }

                // OFFRES (NUMSEM 6)
                if ($row['NUMSEM'] == 6) {
                    $grouped[$key]['MAN'] = $row['MAN'];
                    $grouped[$key]['MSM1'] = $row['MSM1'];
                    $grouped[$key]['MAD1'] = $row['MAD1'];
                }
            }

            // =========================
            // 5. INJECT HISTORIQUES
            // =========================
            foreach ($grouped as $key => &$data) {
                $data['historiques'] = $histGrouped[$key] ?? [];
            }
            

            // =========================
            // 6. FINAL ARRAY
            // =========================
            $mvt_bons_valides = array_values($grouped);
        }

        return $this->render('mvt_bons_valide/show.html.twig', [
            'mvt_bons_valides' => $mvt_bons_valides,
            'mvt_bons_valide' => $mvtBonsValide, 
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mvt_bons_valide_edit', methods: ['GET', 'POST'],defaults: ['title' => 'Modifier le Mouvement Bons Validés'])]
    public function edit(Request $request, MvtBonsValide $mvtBonsValide, EntityManagerInterface $entityManager): Response
    {
        $conn = $entityManager->getConnection();

        // ================== DONNÉES ==================
        $nummvt = $mvtBonsValide->getNUMMVT();
        $dBonsCurrent = $mvtBonsValide->getDBONS();

        $currentDate = (new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))
            ->format('Y-m-d H:i:s');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userIdentifier = $user ? $user->getNom() : 'Système';

        // ================== SELECT EXISTANT ==================
        $sql = sprintf(
            "SELECT NUMMVT, NUMSEM, TXMP, MAN, MSM1, MAD1 
            FROM MVT_BONS_VALIDE 
            WHERE D_BONS = TO_DATE('%s', 'DD/MM/YY')
            ORDER BY NUMSEM",
            $dBonsCurrent
        );

        $records = $conn->fetchAllAssociative($sql);

        $currentData = [];
        foreach ($records as $record) {
            $currentData[$record['NUMSEM']] = $record;
        }

        // ================== META ==================
        $sqlMeta = sprintf(
            "SELECT CREATED_AT, CREATED_BY 
            FROM MVT_BONS_VALIDE
            WHERE D_BONS = TO_DATE('%s','DD/MM/YY')
            FETCH FIRST 1 ROWS ONLY",
            $dBonsCurrent
        );

        $meta = $conn->fetchAssociative($sqlMeta);

        $createdAtOriginal = $currentDate;

        if (!empty($meta['CREATED_AT'])) {
            $date = \DateTime::createFromFormat('d/m/y H:i:s', substr($meta['CREATED_AT'], 0, 17));
            if ($date !== false) {
                $createdAtOriginal = $date->format('Y-m-d H:i:s');
            }
        }

        $createdByOriginal = $meta['CREATED_BY'] ?? $userIdentifier;

        // ================== FORM ==================
        $form = $this->createForm(MvtBonsValideType::class, $mvtBonsValide);
        $transformer = new CommaToPointTransformer();

        // ================== PREFILL ==================
        if ($request->getMethod() === 'GET') {

            $dateObject = \DateTime::createFromFormat('d/m/y', $dBonsCurrent)
                ?: \DateTime::createFromFormat('Y-m-d', $dBonsCurrent);

            $form->get('D_BONS')->setData($dateObject);

            if (isset($currentData[1]['TXMP'])) {
                $form->get('taux_4_semaines')->setData($transformer->toFloat($currentData[1]['TXMP']));
            }
            if (isset($currentData[2]['TXMP'])) {
                $form->get('taux_12_semaines')->setData($transformer->toFloat($currentData[2]['TXMP']));
            }
            if (isset($currentData[3]['TXMP'])) {
                $form->get('taux_24_semaines')->setData($transformer->toFloat($currentData[3]['TXMP']));
            }
            if (isset($currentData[4]['TXMP'])) {
                $form->get('taux_26_semaines')->setData($transformer->toFloat($currentData[4]['TXMP']));
            }
            if (isset($currentData[5]['TXMP'])) {
                $form->get('taux_52_semaines')->setData($transformer->toFloat($currentData[5]['TXMP']));
            }

            if (isset($currentData[6])) {
                $form->get('montant_annonce')->setData($transformer->toFloat($currentData[6]['MAN'] ?? 0));
                $form->get('montant_soumis')->setData($transformer->toFloat($currentData[6]['MSM1'] ?? 0));
                $form->get('montant_adjuge')->setData($transformer->toFloat($currentData[6]['MAD1'] ?? 0));
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conn->beginTransaction();

            try {

                $dBons = $form->get('D_BONS')->getData();
                $dBonsStr = $dBons ? $dBons->format('d/m/y') : null;

                if (!$dBonsStr) {
                    throw new \Exception("Date invalide");
                }

                // ================== NOUVELLES VALEURS ==================
                $newTaux = [
                    1 => $form->get('taux_4_semaines')->getData(),
                    2 => $form->get('taux_12_semaines')->getData(),
                    3 => $form->get('taux_24_semaines')->getData(),
                    4 => $form->get('taux_26_semaines')->getData(),
                    5 => $form->get('taux_52_semaines')->getData(),
                ];

                $newOffres = [
                    'MAN' => $form->get('montant_annonce')->getData(),
                    'MSM1' => $form->get('montant_soumis')->getData(),
                    'MAD1' => $form->get('montant_adjuge')->getData(),
                ];

                // ================== DETECTION MODIFICATIONS ==================
                $modifiedIds = [];

                // UPDATE OU INSERT DES TAUX
                foreach ($newTaux as $numSem => $value) {

                    if ($value !== null && $value !== '') {

                        $txmp = number_format((float)$value, 2, '.', '');
                        $old = $currentData[$numSem]['TXMP'] ?? null;

                        // Si la ligne existe déjà
                        if (isset($currentData[$numSem])) {

                            $nummvtExistant = $currentData[$numSem]['NUMMVT'];

                            // UPDATE si modification
                            if ((float)$value !== (float)$old) {

                                $conn->executeStatement(sprintf(
                                    "UPDATE MVT_BONS_VALIDE
                                    SET TXMP = %s,
                                        UPDATED_AT = TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS')
                                    WHERE NUMMVT = %d",
                                    $txmp,
                                    $currentDate,
                                    $nummvtExistant
                                ));

                                $modifiedIds[] = $nummvtExistant;
                            }

                        } else {

                            // INSERT si nouvelle ligne
                            $conn->executeStatement(sprintf(
                                "INSERT INTO MVT_BONS_VALIDE
                                (NUMMVT, D_BONS, NUMSEM, TXMP, CREATED_AT, UPDATED_AT, CREATED_BY)
                                VALUES
                                (seq_MVT_BONS_VALIDE.NEXTVAL,
                                '%s',
                                %d,
                                %s,
                                TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS'),
                                TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS'),
                                '%s')",
                                $dBonsStr,
                                $numSem,
                                $txmp,
                                $currentDate,
                                $currentDate,
                                str_replace("'", "''", $userIdentifier)
                            ));

                            // Récupérer le NUMMVT inséré
                            $newId = $conn->fetchOne(sprintf(
                                "SELECT NUMMVT FROM MVT_BONS_VALIDE
                                WHERE D_BONS = TO_DATE('%s','DD/MM/YY')
                                AND NUMSEM = %d
                                ORDER BY NUMMVT DESC FETCH FIRST 1 ROWS ONLY",
                                $dBonsStr,
                                $numSem
                            ));

                            if ($newId) {
                                $modifiedIds[] = $newId;
                            }
                        }
                    }
                }

                // UPDATE OU INSERT DES OFFRES (NUMSEM = 6)
                if ($newOffres['MAN'] || $newOffres['MSM1'] || $newOffres['MAD1']) {

                    if (isset($currentData[6])) {

                        // Vérifier si modifié
                        $isModified = (
                            (float)$newOffres['MAN'] !== (float)$currentData[6]['MAN'] ||
                            (float)$newOffres['MSM1'] !== (float)$currentData[6]['MSM1'] ||
                            (float)$newOffres['MAD1'] !== (float)$currentData[6]['MAD1']
                        );

                        if ($isModified) {

                            $nummvtExistant = $currentData[6]['NUMMVT'];

                            $conn->executeStatement(sprintf(
                                "UPDATE MVT_BONS_VALIDE
                                SET MAN = %s,
                                    MSM1 = %s,
                                    MAD1 = %s,
                                    UPDATED_AT = TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS')
                                WHERE NUMMVT = %d",
                                number_format((float)$newOffres['MAN'], 2, '.', ''),
                                number_format((float)$newOffres['MSM1'], 2, '.', ''),
                                number_format((float)$newOffres['MAD1'], 2, '.', ''),
                                $currentDate,
                                $nummvtExistant
                            ));

                            $modifiedIds[] = $nummvtExistant;
                        }

                    } else {

                        // INSERT nouvelle ligne offres
                        $conn->executeStatement(sprintf(
                            "INSERT INTO MVT_BONS_VALIDE
                            (NUMMVT, D_BONS, NUMSEM, MAN, MSM1, MAD1,
                            CREATED_AT, UPDATED_AT, CREATED_BY)
                            VALUES
                            (seq_MVT_BONS_VALIDE.NEXTVAL,
                            '%s',
                            6,
                            %s, %s, %s,
                            TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS'),
                            TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS'),
                            '%s')",
                            $dBonsStr,
                            number_format((float)$newOffres['MAN'], 2, '.', ''),
                            number_format((float)$newOffres['MSM1'], 2, '.', ''),
                            number_format((float)$newOffres['MAD1'], 2, '.', ''),
                            $currentDate,
                            $currentDate,
                            str_replace("'", "''", $userIdentifier)
                        ));

                        $newId = $conn->fetchOne(sprintf(
                            "SELECT NUMMVT FROM MVT_BONS_VALIDE
                            WHERE D_BONS = '%s'
                            AND NUMSEM = 6
                            ORDER BY NUMMVT DESC FETCH FIRST 1 ROWS ONLY",
                            $dBonsStr
                        ));

                        if ($newId) {
                            $modifiedIds[] = $newId;
                        }
                    }
                }

                // ================== HISTORIQUE (seulement pour les modifiés) ==================
                foreach ($modifiedIds as $id) {

                    $conn->executeStatement(sprintf(
                        "INSERT INTO MVT_BONS_VALIDE_HISTORIQUE
                        (ID, MVT_BONS_VALIDE_ID, DATE_MODIFICATION, MODIFIE_PAR)
                        VALUES
                        (seq_MVT_BONS_VALIDE_HISTORIQUE.NEXTVAL,
                        %d,
                        TO_TIMESTAMP('%s','YYYY-MM-DD HH24:MI:SS'),
                        '%s')",
                        $id,
                        $currentDate,
                        str_replace("'", "''", $userIdentifier)
                    ));
                }

                $conn->commit();

                $this->addFlash('success', 'Modification réussie');
                return $this->redirectToRoute('app_mvt_bons_valide_index');

            } catch (\Exception $e) {

                $conn->rollBack();

                $this->addFlash('error', 'Erreur : ' . $e->getMessage());

                return $this->render('mvt_bons_valide/edit.html.twig', [
                    'form' => $form,
                ]);
            }
        }

        return $this->render('mvt_bons_valide/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mvt_bons_valide_delete', methods: ['POST'])]
    public function delete(Request $request, MvtBonsValide $mvtBonsValide, EntityManagerInterface $entityManager): Response
    {
        $conn = $entityManager->getConnection();

        $datefilter = $mvtBonsValide->getDBONS();
        // dd($date);

        if ($this->isCsrfTokenValid('delete'.$datefilter, $request->getPayload()->getString('_token'))) {

            $sql = "
                DELETE FROM MVT_BONS_VALIDE
                WHERE D_BONS = :datefilter
            ";

            $stmt = $conn->prepare($sql);
            $stmt->executeQuery([
                'datefilter' => $datefilter
            ]);

        }

        return $this->redirectToRoute('app_mvt_bons_valide_index');
    }
}
