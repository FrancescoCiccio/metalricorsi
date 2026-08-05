<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Download;
use App\Models\Video;
use Illuminate\Database\Seeder;

/**
 * Backfills EN/FR translations for existing IT content (courses, videos, downloads).
 *
 * Non-destructive: a translation is written only if the locale is still empty,
 * so anything already entered by hand in Filament is preserved. Safe to re-run.
 *
 * Usage: php artisan db:seed --class=ContentTranslationSeeder
 */
class ContentTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $this->translate(Course::class, $this->courses());
        $this->translate(Video::class, $this->videos());
        $this->translate(Download::class, $this->downloads());
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<int, array<string, array<string, string>>>  $translations
     */
    private function translate(string $model, array $translations): void
    {
        foreach ($translations as $id => $fields) {
            $record = $model::find($id);

            if ($record === null) {
                $this->command?->warn("{$model} #{$id} not found, skipped.");

                continue;
            }

            foreach ($fields as $field => $locales) {
                foreach ($locales as $locale => $value) {
                    if ($record->getTranslation($field, $locale, false) === '') {
                        $record->setTranslation($field, $locale, $value);
                    }
                }
            }

            if ($record->isDirty()) {
                $record->saveQuietly();
            }
        }
    }

    /**
     * @return array<int, array<string, array<string, string>>>
     */
    private function courses(): array
    {
        return [
            3 => [
                'title' => [
                    'en' => 'Design MTR® lattice girders with CDS Win',
                    'fr' => 'Concevez les poutres treillis MTR® avec CDS Win',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>Our online training events in partnership with the <strong>leading structural engineering software houses</strong> continue.<br>This new session will be dedicated to the <strong>design of MTR® beams</strong> with the <strong>CDS Win calculation software</strong>, with the contribution of the <strong>software developer S.T.S</strong>.<br>Discover the full programme of the webinar "<strong>Design MTR® lattice girders with CDS Win</strong>"!</p><p><strong>WEDNESDAY 20 NOVEMBER 2024</strong><br><strong>11:00 AM</strong><br><strong>(Duration 40')</strong></p><p><strong><em>Dr Maresa Conte – Metal.Ri S.r.l.</em></strong></p><ul><li>Opening and introduction to the webinar.</li></ul><p><strong><em>Eng. Angelo Biondi – S.T.S. Software Tecnico Scientifico S.r.l.</em></strong></p><ul><li>Institutional overview of the CDS Win structural calculation software;</li><li>Description of the CDS Win structural model under study.</li></ul><p><strong><em>Eng. Alberto de Gennaro – Metal.Ri S.r.l.</em></strong></p><ul><li>Presentation of MTR® System (MTR® A, MTR® T and MTR® C beams, Software.MTR®);</li><li>Why choose MTR® System? Advantages and compatibility with precast floor slabs;</li><li>Composite construction system, self-supporting lattice girders and description of the construction stages (stage 1 and stage 2);</li><li>Managing MTR® lattice girders in the CDS Win environment;</li><li>Checking and verifying MTR® beams with the MTR® Software for correct preliminary sizing.</li></ul><p>The webinar will end with a Q&amp;A session with the participants.</p><p>If you would like to receive the <strong>CDS Win</strong> <strong>manual</strong> dedicated to the design of MTR® beams, <em>send an email to</em> <a href="mailto:marketing@metalri.eu">marketing@metalri.eu</a> with the subject "CDS Win manual request".</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Les événements de formation en ligne se poursuivent en collaboration avec les <strong>principaux éditeurs de logiciels pour l'ingénierie structurelle</strong>.<br>Ce nouveau rendez-vous sera consacré à la <strong>conception des poutres MTR®</strong> avec le <strong>logiciel de calcul CDS Win</strong>, avec la contribution de la <strong>société de développement S.T.S</strong>.<br>Découvrez tout le programme du webinaire « <strong>Concevez les poutres treillis MTR® avec CDS Win</strong> » !</p><p><strong>MERCREDI 20 NOVEMBRE 2024</strong><br><strong>11 H 00</strong><br><strong>(Durée 40')</strong></p><p><strong><em>Mme Maresa Conte – Metal.Ri S.r.l.</em></strong></p><ul><li>Ouverture et introduction du webinaire.</li></ul><p><strong><em>Ing. Angelo Biondi – S.T.S. Software Tecnico Scientifico S.r.l.</em></strong></p><ul><li>Présentation institutionnelle du logiciel de calcul de structures CDS Win ;</li><li>Description du modèle structurel CDS Win étudié.</li></ul><p><strong><em>Ing. Alberto de Gennaro – Metal.Ri S.r.l.</em></strong></p><ul><li>Présentation de MTR® System (poutres MTR® A, MTR® T, MTR® C, Software.MTR®) ;</li><li>Pourquoi choisir MTR® System ? Avantages et compatibilité avec les planchers préfabriqués ;</li><li>Système constructif mixte, autoportance des poutres treillis et description des phases de construction (phase 1 et phase 2) ;</li><li>Gestion des poutres treillis MTR® dans l'environnement CDS Win ;</li><li>Contrôle et vérifications des poutres MTR® avec le logiciel MTR® pour un prédimensionnement correct.</li></ul><p>Le webinaire se conclura par les réponses aux questions des participants.</p><p>Si vous souhaitez recevoir le <strong>manuel</strong> <strong>CDS Win</strong> dédié à la conception des poutres MTR®, <em>envoyez un e-mail à</em> <a href="mailto:marketing@metalri.eu">marketing@metalri.eu</a> avec pour objet « Demande manuel CDS Win ».</p>
                    HTML,
                ],
            ],
            4 => [
                'title' => [
                    'en' => 'Design MTR® lattice girders with PRO_SAP',
                    'fr' => 'Concevez les poutres treillis MTR® avec PRO_SAP',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>A new online training event to develop your skills in the <strong>design of MTR® beams</strong> with the leading calculation software packages. The next session is organised in <strong>collaboration with 2S.I. Software e Servizi per l'Ingegneria</strong>, with the webinar "<strong>Design MTR® lattice girders with PRO_SAP</strong>".</p><p><strong>FRIDAY 12 APRIL 2024</strong><br><strong>11:00 AM</strong><br><strong>(Duration 40')</strong></p><p><strong><em>Dr Maresa Conte – Metal.Ri S.r.l.</em></strong></p><ul><li>Opening and introduction to the webinar.</li></ul><p><strong><em>Eng. Mirco Basaglia – 2S.I. Software e Servizi per l'Ingegneria S.r.l.</em></strong></p><ul><li>Institutional overview of the PRO_SAP structural calculation software.</li></ul><p><strong><em>Eng. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>Presentation of MTR® System (MTR® A, MTR® T and MTR® C beams, Software.MTR®);</li><li>Why choose MTR® System? Advantages and compatibility with precast floor slabs;</li><li>Composite construction system, self-supporting lattice girders and description of the construction stages (stage 1 and stage 2);</li><li>Description of the PRO_SAP structural model under study;</li><li>Managing MTR® lattice girders in the PRO_SAP environment;</li><li>Checking and verifying MTR® beams with the MTR® Software for correct preliminary sizing.</li></ul><p>The webinar will end with a Q&amp;A session with the participants.</p><p><strong>TO START DESIGNING WITH MTR® BEAMS...</strong><br>If you already own <strong>module 10 of PRO_SAP</strong>, the ability to calculate MTR® lattice girders is already included in the program.<br>If you <strong>do not own module 10</strong>, request it from <a href="mailto:conte@metalri.eu">conte@metalri.eu</a> by sending an email with the subject "Software.MTR® request"&nbsp;stating the licence holder and the key number on which to activate it <em>free of charge</em>.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Un nouvel événement de formation en ligne pour développer ses compétences dans la <strong>conception des poutres MTR®</strong> avec les principaux logiciels de calcul. Le prochain rendez-vous se tient en <strong>collaboration avec 2S.I. Software e Servizi per l'Ingegneria</strong>, avec le webinaire « <strong>Concevez les poutres treillis MTR® avec PRO_SAP</strong> ».</p><p><strong>VENDREDI 12 AVRIL 2024</strong><br><strong>11 H 00</strong><br><strong>(Durée 40')</strong></p><p><strong><em>Mme Maresa Conte – Metal.Ri S.r.l.</em></strong></p><ul><li>Ouverture et introduction du webinaire.</li></ul><p><strong><em>Ing. Mirco Basaglia – 2S.I. Software e Servizi per l'Ingegneria S.r.l.</em></strong></p><ul><li>Présentation institutionnelle du logiciel de calcul de structures PRO_SAP.</li></ul><p><strong><em>Ing. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>Présentation de MTR® System (poutres MTR® A, MTR® T, MTR® C, Software.MTR®) ;</li><li>Pourquoi choisir MTR® System ? Avantages et compatibilité avec les planchers préfabriqués ;</li><li>Système constructif mixte, autoportance des poutres treillis et description des phases de construction (phase 1 et phase 2) ;</li><li>Description du modèle structurel PRO_SAP étudié ;</li><li>Gestion des poutres treillis MTR® dans l'environnement PRO_SAP ;</li><li>Contrôle et vérifications des poutres MTR® avec le logiciel MTR® pour un prédimensionnement correct.</li></ul><p>Le webinaire se conclura par les réponses aux questions des participants.</p><p><strong>POUR COMMENCER À CONCEVOIR AVEC LES POUTRES MTR®...</strong><br>Ceux qui possèdent le <strong>module 10 de PRO_SAP</strong> trouveront déjà dans le programme la possibilité de calculer les poutres treillis MTR®.<br>Ceux qui <strong>ne possèdent pas le module 10</strong> doivent en faire la demande à <a href="mailto:conte@metalri.eu">conte@metalri.eu</a> en envoyant un e-mail avec pour objet « Demande Software.MTR® »&nbsp;en indiquant le titulaire de la licence et le numéro de clé sur lequel l'activer <em>gratuitement</em>.</p>
                    HTML,
                ],
            ],
            5 => [
                'title' => [
                    'en' => 'MTR® System lightweight prefabrication for residential and industrial building',
                    'fr' => 'La préfabrication légère de MTR® System pour le bâtiment résidentiel et industriel',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>After the great interest shown in our <a href="https://www.metalri.it/eventi/webinar-progetta-travi-tralicciate-mtr-sismicad/"><strong>first webinar</strong></a> organised with <strong>Concrete</strong> and the patronage of <strong>ISI - Ingegneria Sismica Italiana</strong>, here is a new unmissable Metal.Ri training event.<br>We will discuss some of the topics and questions raised during the first meeting, and how the technical and performance characteristics of MTR® lattice girders meet the different construction needs of both residential and industrial buildings.</p><p><strong>FRIDAY 22 MARCH 2024</strong><br><strong>11:00 AM</strong><br><strong>(Duration 60')</strong></p><p><strong><em><br>Dr Maresa Conte – Metal.Ri S.r.l.</em></strong><strong><br></strong><br></p><ul><li>Opening and introduction to the webinar</li></ul><p><strong><em>Eng. Gianni Bizzotto – Concrete S.r.l.</em></strong></p><ul><li>Institutional overview of the SISMICAD structural calculation software</li></ul><p><strong><em>Eng. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>From the design concept to the construction site, exploiting the potential of MTR® BEAMS;</li><li>Using MTR® SYSTEM in a residential building (choice of MTR® BEAM type and matching precast floor slab);</li><li>Using MTR® SYSTEM in an industrial/commercial building (choice of MTR® BEAM type and matching precast floor slab);</li><li>MTR® calculation and materials report, tables and drawings for the seismic filing;</li><li>General and component checks of MTR® BEAMS in stage one and stage two.</li></ul><p><strong>The webinar will end with a Q&amp;A session with the participants.</strong></p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Après le grand intérêt suscité par notre <a href="https://www.metalri.it/eventi/webinar-progetta-travi-tralicciate-mtr-sismicad/"><strong>premier webinaire</strong></a> organisé avec <strong>Concrete</strong> et le parrainage d'<strong>ISI - Ingegneria Sismica Italiana</strong>, voici un nouvel événement de formation Metal.Ri à ne pas manquer.<br>Nous aborderons certains thèmes et questions apparus lors de la première rencontre, ainsi que la manière dont les caractéristiques techniques et les performances des poutres treillis MTR® répondent aux différentes exigences constructives des bâtiments résidentiels comme industriels.</p><p><strong>VENDREDI 22 MARS 2024</strong><br><strong>11 H 00</strong><br><strong>(Durée 60')</strong></p><p><strong><em><br>Mme Maresa Conte – Metal.Ri S.r.l.</em></strong><strong><br></strong><br></p><ul><li>Ouverture et introduction du webinaire</li></ul><p><strong><em>Ing. Gianni Bizzotto – Concrete S.r.l.</em></strong></p><ul><li>Présentation institutionnelle du logiciel de calcul de structures SISMICAD</li></ul><p><strong><em>Ing. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>De l'idée de projet au chantier en exploitant le potentiel des POUTRES MTR® ;</li><li>L'utilisation de MTR® SYSTEM dans un bâtiment résidentiel (choix du type de POUTRE MTR® et du plancher préfabriqué à associer) ;</li><li>L'utilisation de MTR® SYSTEM dans un bâtiment industriel/commercial (choix du type de POUTRE MTR® et du plancher préfabriqué à associer) ;</li><li>Note de calcul MTR® et sur les matériaux, tableaux et documents graphiques pour le dépôt sismique ;</li><li>Vérifications générales et des composants des POUTRES MTR® en première et seconde phase.</li></ul><p><strong>Le webinaire se conclura par les réponses aux questions des participants.</strong></p>
                    HTML,
                ],
            ],
            6 => [
                'title' => [
                    'en' => 'Design MTR® lattice girders with Sismicad',
                    'fr' => 'Concevez les poutres treillis MTR® avec Sismicad',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p><strong>Metal.Ri</strong> and <strong>Concrete</strong> together, with the <strong>patronage of ISI - Ingegneria Sismica Italiana</strong>, for an <strong>online training webinar</strong> on the new construction systems and how to integrate them into your calculation model.<br>The webinar will illustrate how to <strong>insert MTR® beams into a Sismicad structural model</strong>, with result checking and final verifications with the help of the <strong>MTR® Software</strong>.</p><p><strong>Attending the webinar entitles you to receive the MTR® Software</strong> for Sismicad, with which you can start designing with the MTR® technology and build safer, more efficient and more sustainable buildings.</p><p><strong>FRIDAY 16 FEBRUARY 2024</strong><br><strong>11:00 AM</strong><br><strong>(Duration 40')</strong></p><p><strong><em>Eng. Gianni Bizzotto – Concrete S.r.l.</em></strong></p><ul><li>Institutional overview of the Sismicad structural calculation software.</li></ul><p><strong><em>Eng. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>Presentation of the Metal.Ri company and its MTR® System construction solution (MTR® A, MTR® C and MTR® T beams, MTR® Software);</li><li>Description of the SISMICAD model under study;</li><li>Input of MTR® lattice girders and related calculation options;</li><li>Creation of the FEM model and result checking;</li><li>Verification and design of MTR® beams in Sismicad and with the MTR® Software;</li><li>Brief description of some projects built with MTR® System.</li></ul>
                    HTML,
                    'fr' => <<<'HTML'
                    <p><strong>Metal.Ri</strong> et <strong>Concrete</strong> ensemble, avec le <strong>parrainage d'ISI - Ingegneria Sismica Italiana</strong>, pour un <strong>webinaire de formation en ligne</strong> sur les nouveaux systèmes constructifs et leur intégration dans son modèle de calcul.<br>Le webinaire présentera les instructions pour l'<strong>insertion des poutres MTR® dans un modèle structurel Sismicad</strong>, avec contrôle des résultats et vérifications finales à l'aide du <strong>logiciel MTR®</strong>.</p><p><strong>La participation au webinaire vous donnera droit à recevoir le logiciel MTR®</strong> pour Sismicad, grâce auquel il sera possible de commencer à concevoir avec la technologie MTR® et de réaliser des bâtiments plus sûrs, efficaces et durables.</p><p><strong>VENDREDI 16 FÉVRIER 2024</strong><br><strong>11 H 00</strong><br><strong>(Durée 40')</strong></p><p><strong><em>Ing. Gianni Bizzotto – Concrete S.r.l.</em></strong></p><ul><li>Présentation institutionnelle du logiciel de calcul de structures Sismicad.</li></ul><p><strong><em>Ing. Nicola dell'Olio – Metal.Ri S.r.l.</em></strong></p><ul><li>Présentation de l'entreprise Metal.Ri et de sa solution constructive MTR® System (poutres MTR® A, MTR® C, MTR® T, logiciel MTR®) ;</li><li>Description du modèle SISMICAD étudié ;</li><li>Saisie des poutres treillis MTR® et options de calcul associées ;</li><li>Création du modèle FEM et contrôle des résultats ;</li><li>Vérification et conception des poutres MTR® dans Sismicad et avec le logiciel MTR® ;</li><li>Brève description de quelques réalisations avec MTR® System.</li></ul>
                    HTML,
                ],
            ],
            7 => [
                'title' => [
                    'en' => 'Structural design and building construction: ADVANCED CONSTRUCTION SOLUTIONS WITH LATTICE GIRDER SYSTEMS',
                    'fr' => 'Conception structurelle et réalisation de bâtiments : SOLUTIONS CONSTRUCTIVES ÉVOLUÉES AVEC SYSTÈMES DE POUTRES TREILLIS',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>Metal.Ri's travelling training events continue, dedicated to the in-depth study of advanced construction solutions for structural design.</p><p>The next appointment is on <strong>Friday 29 November in Salerno</strong>, at the <strong>Order of Engineers of the Province of Salerno</strong>, in collaboration with the <strong>University of Salerno</strong> and its <strong>Department of Civil Engineering</strong>.<br>The event is part of the series of technical seminars "<strong>Structural design and building construction with lattice girder systems</strong>", an opportunity to discuss innovative construction technologies and encourage direct exchange between experts and professionals in the field.<br>Participating engineers will have the opportunity to earn <strong>3 CFP credits</strong>*.</p><p><strong>FRIDAY 29 NOVEMBER 2024</strong><br><strong>3:00 PM - 6:30 PM</strong><br><strong>Headquarters of the Order of Engineers of the Province of Salerno • Sala De Angelis</strong><br>Via Salvatore Marano • Salerno</p><ul><li>Historical and regulatory framework and mechanical behaviour of composite lattice girders</li><li>Design and verification of composite lattice girders</li><li>Computer-aided structural design of composite lattice girders</li><li>Experimental aspects related to execution</li><li>Application examples: guidance for designers and works supervisors</li></ul><p>*Engineers up to date with the payment of their professional register fee are granted 3 CFP credits pursuant to the Regulation on continuing professional development. Online booking is required for this purpose.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Les événements de formation itinérants de Metal.Ri se poursuivent, dédiés à l'approfondissement et à l'étude de solutions constructives avancées pour la conception structurelle.</p><p>Le prochain rendez-vous est <strong>vendredi 29 novembre à Salerne</strong>, au siège de l'<strong>Ordre des Ingénieurs de la province de Salerne</strong>, en collaboration avec l'<strong>Université de Salerne</strong> et le <strong>Département de Génie Civil</strong>.<br>L'événement fait partie du cycle de séminaires techniques « <strong>Conception structurelle et réalisation de bâtiments avec systèmes de poutres treillis</strong> », une occasion de discuter des technologies innovantes dans la construction et de favoriser l'échange direct entre experts et professionnels du secteur.<br>Les ingénieurs participants auront l'opportunité d'obtenir <strong>3 crédits CFP</strong>*.</p><p><strong>VENDREDI 29 NOVEMBRE 2024</strong><br><strong>15 H 00 - 18 H 30</strong><br><strong>Siège de l'Ordre des Ingénieurs de la province de Salerne • Salle De Angelis</strong><br>Via Salvatore Marano • Salerne</p><ul><li>Cadre historique et normatif et comportement mécanique des poutres treillis mixtes</li><li>Conception et vérification des poutres treillis mixtes</li><li>La conception structurelle assistée par ordinateur des poutres treillis mixtes</li><li>Aspects expérimentaux liés à l'exécution</li><li>Exemples d'application : indications pour les concepteurs et les directeurs de travaux</li></ul><p>*Les ingénieurs à jour du paiement de leur cotisation à l'ordre se voient reconnaître 3 crédits CFP au sens du Règlement pour la mise à jour des compétences professionnelles. La réservation en ligne est requise à cette fin.</p>
                    HTML,
                ],
            ],
            8 => [
                'title' => [
                    'en' => 'Structural design and building construction: ADVANCED CONSTRUCTION SOLUTIONS WITH LATTICE GIRDER SYSTEMS',
                    'fr' => 'Conception structurelle et réalisation de bâtiments : SOLUTIONS CONSTRUCTIVES ÉVOLUÉES AVEC SYSTÈMES DE POUTRES TREILLIS',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>The travelling training events involving several Italian cities continue: this time it's Lecce!</p><p>The Order of Engineers of the Province of Lecce, in collaboration with our company Metal.Ri, organises the technical seminar "<strong>Structural design and building construction: ADVANCED CONSTRUCTION SOLUTIONS WITH LATTICE GIRDER SYSTEMS</strong>".<br>The event is aimed at engineers, who will be granted <strong>3 CFP credits</strong> for attending, but also at all other professionals in the field, from surveyors to architects.</p><p><strong>THURSDAY 30 MAY 2024</strong><br><strong>3:30 PM - 6:30 PM</strong><br><strong>Ecotekne Campus • ROOM Y-1</strong><br>Department of Innovation Engineering • University of Salento<br>Via Monteroni • Lecce</p><ul><li>Computer-aided structural design of composite lattice girders</li><li>Experimental aspects related to cyclic testing of composite lattice girders</li><li>Design and verification of composite lattice girders</li><li>Application examples: guidance for designers and works supervisors</li></ul>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Les événements de formation itinérants qui traversent plusieurs villes italiennes continuent : c'est au tour de Lecce !</p><p>L'Ordre des Ingénieurs de la province de Lecce, en collaboration avec notre entreprise Metal.Ri, organise le séminaire technique « <strong>Conception structurelle et réalisation de bâtiments : SOLUTIONS CONSTRUCTIVES ÉVOLUÉES AVEC SYSTÈMES DE POUTRES TREILLIS</strong> ».<br>L'événement s'adresse aux ingénieurs, qui se verront reconnaître <strong>3 crédits CFP</strong> pour leur participation, mais aussi à tous les autres professionnels du secteur, des géomètres aux architectes.</p><p><strong>JEUDI 30 MAI 2024</strong><br><strong>15 H 30 - 18 H 30</strong><br><strong>Campus Ecotekne • SALLE Y-1</strong><br>Département d'Ingénierie de l'Innovation • Université du Salento<br>Via Monteroni • Lecce</p><ul><li>La conception structurelle assistée par ordinateur des poutres treillis mixtes</li><li>Aspects expérimentaux liés à l'exécution d'essais cycliques sur poutres treillis mixtes</li><li>Conception et vérification des poutres treillis mixtes</li><li>Exemples d'application : indications pour les concepteurs et les directeurs de travaux</li></ul>
                    HTML,
                ],
            ],
            9 => [
                'title' => [
                    'en' => 'Structural design and building construction: ADVANCED CONSTRUCTION SOLUTIONS WITH LATTICE GIRDER SYSTEMS',
                    'fr' => 'Conception structurelle et réalisation de bâtiments : SOLUTIONS CONSTRUCTIVES ÉVOLUÉES AVEC SYSTÈMES DE POUTRES TREILLIS',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>The Order of Engineers of the Province of Matera, in collaboration with our company Metal.Ri, organises the technical seminar "<strong>Structural design and building construction: ADVANCED CONSTRUCTION SOLUTIONS WITH LATTICE GIRDER SYSTEMS</strong>".<br>The event is aimed at engineers, architects and surveyors.<br><strong>For attending the seminar, 3 CFP credits will be granted by all the provincial professional orders involved.</strong></p><p><strong>FRIDAY 1 MARCH 2024</strong><br><strong>3:00 PM<br></strong>Hotel del Campo<br>Via Lucrezio 1 • Matera</p><ul><li>Computer-aided structural design of composite lattice girders</li><li>Experimental aspects related to cyclic testing of composite lattice girders</li><li>Design and verification of composite lattice girders</li><li>Application examples: guidance for designers and works supervisors</li></ul><p>Participants will be registered in chronological order on the day of the seminar at the entrance of the hall, up to a maximum of 200.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>L'Ordre des Ingénieurs de la province de Matera, en collaboration avec notre entreprise Metal.Ri, organise le séminaire technique « <strong>Conception structurelle et réalisation de bâtiments : SOLUTIONS CONSTRUCTIVES ÉVOLUÉES AVEC SYSTÈMES DE POUTRES TREILLIS</strong> ».<br>L'événement s'adresse aux ingénieurs, architectes et géomètres.<br><strong>La participation au séminaire donnera droit à 3 crédits CFP reconnus par tous les ordres professionnels provinciaux concernés.</strong></p><p><strong>VENDREDI 1ER MARS 2024</strong><br><strong>15 H 00<br></strong>Hotel del Campo<br>Via Lucrezio 1 • Matera</p><ul><li>La conception structurelle assistée par ordinateur des poutres treillis mixtes</li><li>Aspects expérimentaux liés à l'exécution d'essais cycliques sur poutres treillis mixtes</li><li>Conception et vérification des poutres treillis mixtes</li><li>Exemples d'application : indications pour les concepteurs et les directeurs de travaux</li></ul><p>Les participants seront enregistrés par ordre chronologique le jour du séminaire à l'entrée de la salle, dans la limite de 200 personnes.</p>
                    HTML,
                ],
            ],
            10 => [
                'title' => [
                    'en' => 'Lightweight steel-concrete composite prefabrication for construction site industrialisation: from design to installation',
                    'fr' => "La préfabrication légère à structure mixte acier-béton pour l'industrialisation du chantier : de la conception à la mise en œuvre",
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p><strong>Thursday 20 June</strong><br><strong>Provveditorato Alle Opere Pubbliche Per La Puglia</strong><br>Corso Antonio De Tullio, 1, 70122 Bari</p><p><strong>3 CFP credits for Engineers<br>5 CFP credits for Architects<br></strong><br></p><p>The course "<strong>Lightweight steel-concrete composite prefabrication for construction site industrialisation: from design to installation</strong>" aims to provide participants with technical and practical knowledge of the lightweight prefabrication of <strong>lattice girder</strong> systems.</p><p>Prefabrication has become a construction technology that most effectively pursues the objectives set by the current NTC 2018 standards, as it moves manual activities from the construction site to the factory, where construction processes are standardised, automated, controlled and certified.</p><p>The entry into force of the Technical Standards for Construction marked a radical change in design practice, introducing a new approach based no longer on uncertain assumptions but on deterministic ones of "proven reliability", and a construction process that demands strict conformity of what is built to what has been designed in detail. The essential condition is that the design solution be perfectly calibrated and correspond to the actual ultimate load-bearing capacity of the structural system, whose achievement must be guaranteed by the actual availability of the resources that each individual construction component must possess.<br>In other words, the structural solution must be correctly designed and built in accordance with the construction specifications set out in the design documents.<br>This need directs the structural products market towards certified solutions, i.e. solutions built with clearly identified, rigorously controlled construction processes that perfectly match the design specifications formulated with mathematical simulation models of "proven reliability", i.e. clearly and unambiguously identifiable in the international technical literature.</p><p>During the site visit, applications of the system will be shown in order to fully understand the structural, design and economic advantages of lattice girders in the construction of an entire building.</p><p><strong>PROGRAMME</strong></p><p>Design and verification of composite lattice girders</p><ul><li>Historical and technological background</li><li>Structural behaviour – Stage I (installation)</li><li>Structural behaviour – Stage II (completed structure)</li><li>Flexural behaviour</li><li>Shear behaviour</li><li>Local checks</li></ul><p>Computer-aided structural design of composite lattice girders</p><ul><li>The regulatory context;</li><li>Construction stages and the production process;</li><li>Design requirements;</li><li>Software solutions for computer-aided structural design.</li></ul><p>Experimental aspects related to cyclic testing of composite lattice girders</p><ul><li>Description of the experimental set-up</li><li>Instrumentation and processing of the acquired signals</li><li>Experimental results</li></ul><p>Application examples in modern building</p><ul><li>Authorisations for use;</li><li>Application examples;</li><li>Documentation accompanying the supply.</li></ul>
                    HTML,
                    'fr' => <<<'HTML'
                    <p><strong>Jeudi 20 juin</strong><br><strong>Provveditorato Alle Opere Pubbliche Per La Puglia</strong><br>Corso Antonio De Tullio, 1, 70122 Bari</p><p><strong>3 crédits CFP pour les Ingénieurs<br>5 crédits CFP pour les Architectes<br></strong><br></p><p>Le cours « <strong>La préfabrication légère à structure mixte acier-béton pour l'industrialisation du chantier : de la conception à la mise en œuvre</strong> » a pour objectif de fournir aux participants des notions techniques et pratiques concernant la préfabrication légère des systèmes de <strong>poutres treillis</strong>.</p><p>La solution préfabriquée est devenue une technologie constructive qui poursuit avec le plus d'efficacité les objectifs fixés par les normes NTC 2018 en vigueur, car elle transfère les activités manuelles du chantier vers l'usine, où les processus de construction sont standardisés, automatisés, contrôlés et certifiés.</p><p>L'entrée en vigueur des Normes Techniques pour les Constructions a marqué un changement radical dans la pratique du projet, en introduisant une nouvelle approche fondée non plus sur des hypothèses aléatoires mais sur des hypothèses déterministes de « fiabilité éprouvée », et un processus de réalisation qui impose une conformité rigoureuse de l'ouvrage réalisé à ce qui a été conçu en détail. La condition indispensable est que la solution de projet soit parfaitement calibrée et corresponde à la capacité résistante ultime effective du système structurel, dont l'atteinte doit être garantie par la disponibilité effective des ressources que chaque composant constructif doit posséder.<br>Autrement dit, la solution structurelle doit être correctement conçue et réalisée conformément aux spécifications constructives indiquées dans les documents de projet.<br>Cette exigence oriente l'attention du marché des produits structurels vers des solutions certifiées, c'est-à-dire réalisées avec des processus de construction clairement identifiés, rigoureusement contrôlés et parfaitement conformes aux spécifications de projet formulées avec des modèles mathématiques de simulation de « fiabilité éprouvée », c'est-à-dire clairement et sans équivoque identifiables dans la littérature technique internationale.</p><p>Lors de la visite de chantier, les applications du système seront présentées afin de bien comprendre les avantages structurels, conceptuels et économiques des poutres treillis dans la réalisation de l'ensemble du bâtiment.</p><p><strong>PROGRAMME</strong></p><p>Conception et vérification des poutres treillis mixtes</p><ul><li>Cadre historique et technologique</li><li>Comportement structurel – Phase I (mise en œuvre)</li><li>Comportement structurel – Phase II (structure complète)</li><li>Comportement en flexion</li><li>Comportement à l'effort tranchant</li><li>Vérifications locales</li></ul><p>La conception structurelle assistée par ordinateur des poutres treillis mixtes</p><ul><li>Le contexte normatif ;</li><li>Les phases de construction et le processus de production ;</li><li>Les exigences de conception ;</li><li>Les solutions logicielles pour la conception structurelle assistée.</li></ul><p>Aspects expérimentaux liés à l'exécution d'essais cycliques sur poutres treillis mixtes</p><ul><li>Description du dispositif expérimental</li><li>Instrumentation et traitement des signaux acquis</li><li>Résultats expérimentaux</li></ul><p>Exemples d'application dans la construction moderne</p><ul><li>Autorisations d'emploi ;</li><li>Exemples d'application ;</li><li>Documentation accompagnant la fourniture.</li></ul>
                    HTML,
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, array<string, string>>>
     */
    private function videos(): array
    {
        return [
            1 => [
                'title' => [
                    'en' => 'MTR® System and its construction solutions',
                    'fr' => 'MTR® System et les solutions constructives',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>MTR® System represents the most reliable solution in the field of steel-concrete composite structures.<br>Innovative in its morphology, MTR® System consists of three types of beams, MTR® A, MTR® T and MTR® C, and a dedicated calculation software.<br><br>Structural design with MTR® System suits any intended use. Every building can be designed and built exploiting the technical characteristics of a single type of MTR® Beam, or combining several types.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>MTR® System représente ce qu'il y a de plus fiable dans le secteur des structures mixtes acier-béton.<br>Innovant dans sa morphologie, MTR® System se compose de trois types de poutres, MTR® A, MTR® T et MTR® C, et d'un logiciel de calcul dédié.<br><br>La conception structurelle avec MTR® System s'adapte à toute destination d'usage. Chaque bâtiment peut être conçu et réalisé en exploitant les caractéristiques techniques d'un seul type de Poutre MTR®, ou en prévoyant l'utilisation de plusieurs types.</p>
                    HTML,
                ],
            ],
            2 => [
                'title' => [
                    'en' => 'Construction of the Vèrdica residential complex (Bari)',
                    'fr' => 'Réalisation du complexe résidentiel Vèrdica (Bari)',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>A new large-scale multi-storey residential development: 27,500 sqm over 17 floor decks.<br>The Vèrdica building - Poggiofranco (Bari), an innovative project in terms of construction and architecture founded on building ethics, once again features MTR® System solutions!<br>As always, we guaranteed:</p><ul><li>fast execution of the works (structure completed in just 9 months);</li><li>maximum safety during installation at height;</li><li>architectural freedom for a better layout of the interior spaces.</li></ul><p><br></p><p><br></p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Une nouvelle réalisation résidentielle à plusieurs étages aux grands chiffres : 27 500 m² répartis sur 17 planchers.<br>L'immeuble Vèrdica - Poggiofranco (Bari), un projet innovant du point de vue constructif et architectural fondé sur l'éthique du bâtiment, met une fois de plus à l'honneur les solutions MTR® System !<br>Nous avons garanti comme toujours :</p><ul><li>rapidité d'exécution des travaux (structure achevée en seulement 9 mois) ;</li><li>sécurité maximale pendant la pose en hauteur ;</li><li>liberté architecturale pour une meilleure distribution des espaces intérieurs.</li></ul><p><br></p><p><br></p>
                    HTML,
                ],
            ],
            3 => [
                'title' => [
                    'en' => 'Construction of an industrial facility - GLS plant, Matera',
                    'fr' => "Construction d'un bâtiment industriel - Site GLS, Matera",
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>Construction of the new GLS facility in Matera: a large 6,000 sqm logistics hub over 4 floor decks, built using MTR® C and MTR® A beams with long linear and shaped spans.</p><p><br></p><p><br><br></p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Réalisation du nouveau site GLS à Matera : un grand pôle logistique de 6 000 m², réparti sur 4 planchers, avec l'emploi de poutres MTR® C et MTR® A à grandes portées linéaires et profilées.</p><p><br></p><p><br><br></p>
                    HTML,
                ],
            ],
            4 => [
                'title' => [
                    'en' => 'Construction of the Borgo Felice residential complex (Bari)',
                    'fr' => 'Réalisation du complexe résidentiel Borgo Felice (Bari)',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>Borgo Felice is a large residential complex rising in the city of Bari. A total area of almost 40,000 sqm over 9 floor decks, which will house apartments of various sizes and underground parking.<br>To guarantee construction speed, reduced timescales and maximum architectural freedom, MTR® T beams were used: combined with precast floor slabs, they are the ideal solution for construction site industrialisation and the development of off-site building.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>Borgo Felice est un grand complexe résidentiel qui s'élève dans la ville de Bari. Une surface totale de près de 40 000 m² répartis sur 9 planchers, qui accueilleront des appartements de différentes surfaces et des parkings souterrains.<br>Pour garantir la rapidité de construction, des délais réduits et une liberté architecturale maximale, les poutres MTR® T ont été employées : associées aux planchers préfabriqués, elles représentent la solution idéale pour l'industrialisation du chantier et le développement de la construction hors site.</p>
                    HTML,
                ],
            ],
            5 => [
                'title' => [
                    'en' => 'Construction speed with MTR® System',
                    'fr' => 'Rapidité de construction avec MTR® System',
                ],
            ],
            6 => [
                'title' => [
                    'en' => 'Simplified design with the help of our calculation software',
                    'fr' => 'Conception simplifiée grâce à notre logiciel de calcul',
                ],
                'description' => [
                    'en' => <<<'HTML'
                    <p>Structural design with the MTR® technology is easy to implement thanks to the <strong>MTR® applications</strong> developed by our <strong>partner company INFO.MTR</strong>. INFO.MTR's goal is to provide an open digital infrastructure offering <strong>easy-to-use, safe and high-quality design services for the MTR® technology</strong>, ensuring a transparent and open relationship with structural designers.</p>
                    HTML,
                    'fr' => <<<'HTML'
                    <p>La conception structurelle avec la technologie MTR® est facilement réalisable grâce aux <strong>applications MTR®</strong> développées par l'<strong>entreprise partenaire INFO.MTR</strong>. L'objectif d'INFO.MTR est de fournir une infrastructure numérique ouverte, capable d'offrir des <strong>services de conception pour la technologie MTR® faciles à utiliser, sûrs et de qualité</strong>, garantissant une relation transparente et ouverte avec les ingénieurs structure.</p>
                    HTML,
                ],
            ],
            7 => [
                'title' => [
                    'en' => 'MTR® P Column: structural innovation for high-performance construction',
                    'fr' => 'Poteau MTR® P : innovation structurelle pour des constructions à hautes performances',
                ],
            ],
            8 => [
                'title' => [
                    'en' => 'WEBINAR: Design MTR® lattice girders with Sismicad',
                    'fr' => 'WEBINAIRE : Concevez les poutres treillis MTR® avec Sismicad',
                ],
            ],
            9 => [
                'title' => [
                    'en' => 'WEBINAR: Design MTR® lattice girders with PRO_SAP',
                    'fr' => 'WEBINAIRE : Concevez les poutres treillis MTR® avec PRO_SAP',
                ],
            ],
            10 => [
                'title' => [
                    'en' => 'WEBINAR: Design MTR® lattice girders with CDS Win',
                    'fr' => 'WEBINAIRE : Concevez les poutres treillis MTR® avec CDS Win',
                ],
            ],
            11 => [
                'title' => [
                    'en' => 'WEBINAR: MTR® System lightweight prefabrication for residential and industrial building',
                    'fr' => 'WEBINAIRE : La préfabrication légère de MTR® System pour le bâtiment résidentiel et industriel',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, array<string, string>>>
     */
    private function downloads(): array
    {
        return [
            2 => ['title' => ['en' => 'Metal.Ri Company Profile', 'fr' => "Profil d'entreprise Metal.Ri"]],
            3 => ['title' => ['en' => 'CE marking for structural steel components', 'fr' => 'Marquage CE des composants structuraux en acier']],
            4 => ['title' => ['en' => 'CE marking for precast concrete products', 'fr' => 'Marquage CE des produits préfabriqués en béton']],
            5 => ['title' => ['en' => 'Category A classification', 'fr' => 'Appartenance à la catégorie A']],
            6 => ['title' => ['en' => 'Company quality management system certification', 'fr' => "Certification du système de qualité de l'entreprise"]],
            7 => ['title' => ['en' => 'Welding process certification', 'fr' => 'Certification des procédés de soudage']],
            8 => ['title' => ['en' => 'ReMade in Italy certification', 'fr' => 'Certification ReMade in Italy']],
            9 => ['title' => ['en' => 'Product and production process qualifications', 'fr' => 'Qualifications du produit et du processus de production']],
            10 => ['title' => ['en' => 'MTR® A technical data sheet with predalle slab (concrete base)', 'fr' => 'Fiche technique MTR® A avec prédalle (fond en béton)']],
            11 => ['title' => ['en' => 'MTR® A technical data sheet with predalle slab (clay base)', 'fr' => 'Fiche technique MTR® A avec prédalle (fond en terre cuite)']],
            12 => ['title' => ['en' => 'MTR® A technical data sheet with hollow-core slab', 'fr' => 'Fiche technique MTR® A avec plancher alvéolé']],
            13 => ['title' => ['en' => 'MTR® A technical data sheet with lattice joists and EPS floor', 'fr' => 'Fiche technique MTR® A avec poutrelles treillis et plancher en EPS']],
            14 => ['title' => ['en' => 'MTR® A technical data sheet with lattice joists and clay-block floor', 'fr' => 'Fiche technique MTR® A avec poutrelles treillis et plancher en terre cuite']],
            15 => ['title' => ['en' => 'Upstand MTR® A technical data sheet with predalle slab', 'fr' => 'Fiche technique MTR® A rehaussée avec prédalle']],
            16 => ['title' => ['en' => 'Upstand MTR® A technical data sheet with hollow-core slab', 'fr' => 'Fiche technique MTR® A rehaussée avec plancher alvéolé']],
            17 => ['title' => ['en' => 'Upstand MTR® A technical data sheet with lattice joists and EPS floor', 'fr' => 'Fiche technique MTR® A rehaussée avec poutrelles treillis et plancher en EPS']],
            18 => ['title' => ['en' => 'Downstand MTR® A technical data sheet with predalle slab', 'fr' => 'Fiche technique MTR® A en retombée avec prédalle']],
            19 => ['title' => ['en' => 'Downstand MTR® A technical data sheet with hollow-core slab', 'fr' => 'Fiche technique MTR® A en retombée avec plancher alvéolé']],
            20 => ['title' => ['en' => 'Downstand MTR® A technical data sheet with lattice joists and EPS floor', 'fr' => 'Fiche technique MTR® A en retombée avec poutrelles treillis et plancher en EPS']],
            21 => ['title' => ['en' => 'MTR® T technical data sheet on formwork with predalle slab floor', 'fr' => 'Fiche technique MTR® T sur coffrage avec plancher à prédalles']],
            22 => ['title' => ['en' => 'MTR® T technical data sheet on predalle slab floor', 'fr' => 'Fiche technique MTR® T sur plancher à prédalles']],
            23 => ['title' => ['en' => 'MTR® T technical data sheet with lattice joists and EPS floor', 'fr' => 'Fiche technique MTR® T avec poutrelles treillis et plancher en EPS']],
            24 => ['title' => ['en' => 'MTR® C technical data sheet with predalle slab', 'fr' => 'Fiche technique MTR® C avec prédalle']],
            25 => ['title' => ['en' => 'MTR® C technical data sheet with hollow-core slab', 'fr' => 'Fiche technique MTR® C avec plancher alvéolé']],
            27 => ['title' => ['en' => 'Metal.Ri Catalogue', 'fr' => 'Catalogue Metal.Ri']],
            28 => ['title' => ['en' => 'MTR® P Column brochure', 'fr' => 'Brochure Poteau MTR® P']],
            29 => ['title' => ['en' => 'Publication in the MDPI scientific journal “Applied Sciences”', 'fr' => 'Publication dans la revue scientifique « Applied Sciences » de MDPI']],
            30 => ['title' => ['en' => 'BIM objects', 'fr' => 'Objets BIM']],
            31 => ['title' => ['en' => 'MTR® A beam technical data sheet', 'fr' => 'Fiche technique poutre MTR® A']],
            32 => ['title' => ['en' => 'MTR® T beam technical data sheet', 'fr' => 'Fiche technique poutre MTR® T']],
            33 => ['title' => ['en' => 'MTR® C beam technical data sheet', 'fr' => 'Fiche technique poutre MTR® C']],
            34 => ['title' => ['en' => 'Imprese Edili - SAIE 2023 special issue', 'fr' => 'Imprese Edili - Spécial SAIE 2023']],
            35 => ['title' => ['en' => 'Imprese Edili - December 2025', 'fr' => 'Imprese Edili - Décembre 2025']],
            36 => ['title' => ['en' => 'Osservatorio Abitare - October 2023', 'fr' => 'Osservatorio Abitare - Octobre 2023']],
            37 => ['title' => ['en' => 'Lo Strutturista - September 2019', 'fr' => 'Lo Strutturista - Septembre 2019']],
            38 => ['title' => ['en' => 'Lo Strutturista - April 2026', 'fr' => 'Lo Strutturista - Avril 2026']],
        ];
    }
}
