<?php

namespace App\Controller;

use App\Entity\Herritarra;
use App\Form\FitxategiaType;
use App\Form\HerritarraType;
use App\Form\OrdezkatuType;
use App\Repository\HauteskundeaRepository;
use App\Repository\HerritarraRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/kudeatu/{_locale}/herritarra')]
class HerritarraController extends AbstractController
{
    #[Route('/{hauteskundeaid}/index', name: 'app_herritarra_index', methods: ['GET'])]
    public function index(
        int $hauteskundeaid,
        HerritarraRepository $herritarraRepository,
        HauteskundeaRepository $hauteskundeaRepository,
        Request $request,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $sort = 'nombre',
        #[MapQueryParameter] string $sortDirection = 'ASC',
        #[MapQueryParameter] ?string $query = null,
        #[MapQueryParameter('dists')] array $searchDists = [],
        #[MapQueryParameter('seccs')] array $searchSeccs = [],
        #[MapQueryParameter('mesas')] array $searchMesas = [],
        #[MapQueryParameter('egoeras')] array $searchEgoeras = [-1, 0, 1],
    ): Response {
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($herritarraRepository->getAllQueryBuilder($query, $searchDists, $searchSeccs, $searchMesas, $searchEgoeras, $sort, $sortDirection)),
            $page,
            10
        );

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->renderBlock('herritarra/index.html.twig', 'stream_success', [
                'hauteskundea' => $hauteskundeaRepository->find($hauteskundeaid),
                'herritarras' => $pager,
                'sort' => $sort,
                'sortDirection' => $sortDirection,
                'dists' => $herritarraRepository->getAllDists($hauteskundeaid),
                'searchDists' => $searchDists,
                'searchSeccs' => $searchSeccs,
                'searchMesas' => $searchMesas,
                'searchEgoeras' => $searchEgoeras,
                'seccs' => $herritarraRepository->getAllSeccs($hauteskundeaid),
                'mesas' => $herritarraRepository->getAllMesas($hauteskundeaid),
            ]);
        }

        return $this->render('herritarra/index.html.twig', [
            'hauteskundea' => $hauteskundeaRepository->find($hauteskundeaid),
            'herritarras' => $pager,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'dists' => $herritarraRepository->getAllDists($hauteskundeaid),
            'searchDists' => $searchDists,
            'searchSeccs' => $searchSeccs,
            'searchMesas' => $searchMesas,
            'searchEgoeras' => $searchEgoeras,
            'seccs' => $herritarraRepository->getAllSeccs($hauteskundeaid),
            'mesas' => $herritarraRepository->getAllMesas($hauteskundeaid),
        ]);
    }

    #[Route('/dropzone/{hauteskundeaid}', name: 'app_herritarra_fitxategia', methods: ['GET', 'POST'])]
    public function dropzone(
        Request $request,
        int $hauteskundeaid,
        HerritarraRepository $herritarraRepository,
        HauteskundeaRepository $hauteskundeaRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $form = $this->createForm(FitxategiaType::class, null, [
            'action' => $this->generateUrl('app_herritarra_fitxategia', ['hauteskundeaid' => $hauteskundeaid]),
            'method' => 'POST',
            'csrf_protection' => false, // Disable CSRF protection
        ]);
        $form->handleRequest($request);
        $errors = $form->getErrors();
        if ($form->isSubmitted() && $form->isValid()) {
            $hauteskundea = $hauteskundeaRepository->find($hauteskundeaid);
            $fitxategia = $form->get('file')->getData();
            $fitxategia->move('/tmp', 'import.csv');
            $header = null;
            $data = [];
            if (($handle = fopen('/tmp/import.csv', 'r')) !== false) {
                while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
                    if (!$header) {
                        $header = $row;
                    } else {
                        $data[] = array_combine($header, $row);
                    }
                }
                fclose($handle);
            }

            try {
                foreach ($data as $row) {
                    $herritarra = new Herritarra();
                    $herritarra->setDist($row['DIST']);
                    $herritarra->setSecc($row['SECC']);
                    $herritarra->setMesa($row['MESA']);
                    $herritarra->setNlocal($row['NLOCAL']);
                    $herritarra->setNlocalb($row['NLOCALB']);
                    $herritarra->setHelbidea($row['DOMI1']);
                    $herritarra->setBarrutia($row['DOMI2']);
                    $herritarra->setCargofinal($row['CARGOFINAL']);
                    $herritarra->setCargo($this->setKarguaByCode($row['CARGOFINAL'], 'es'));
                    $herritarra->setKargua($this->setKarguaByCode($row['CARGOFINAL'], 'eus'));
                    $herritarra->setIdent($row['IDENT']);
                    $herritarra->setNombre($row['NOMBRE']);
                    $herritarra->setApellido1($row['APELLIDO1']);
                    $herritarra->setApellido2($row['APELLIDO2']);
                    $eguna = $row['Eguna'];
                    $herritarra->setEguna($eguna);
                    $hilabetea = $row['Hilabetea'];
                    $herritarra->setHilabetea($hilabetea);
                    $urtea = $row['Urtea'];
                    $herritarra->setUrtea($urtea);
                    $jaiteguna = $urtea.'-'.$hilabetea.'-'.$eguna;
                    $logger->info($row['IDENT'].'  -   '.$jaiteguna);
                    $herritarra->setJaioteguna(new \DateTime($jaiteguna));
                    $herritarra->setActive(in_array($row['CARGOFINAL'], ['P', 'PS1', 'PS2', 'V1', 'V1S1', 'V1S2', 'V2', 'V2S1', 'V2S2']) ? 1 : 0);
                    $herritarra->setHauteskundea($hauteskundea);
                    $entityManager->persist($herritarra);
                }
            } catch (\Exception $exception) {
                return $this->render('hauteskundea/csverror.html.twig', [
                    'e' => $exception->getMessage(),
                    'ident' => $row['IDENT'],
                    'herritarra' => $row['NOMBRE'].' '.$row['APELLIDO1'].' '.$row['APELLIDO2'],
                ]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            return $this->redirectToRoute('app_herritarra_index', ['hauteskundeaid' => $hauteskundeaid]);
        }

        return $this->render('herritarra/fitxategia.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function setKarguaByCode($code, $lang): ?string
    {
        $positions = [
            ['code' => 'P', 'eus' => 'Presidentea', 'es' => 'Presidente/a'],
            ['code' => 'PS1', 'eus' => 'Presidentea-Lehen ordezkoa', 'es' => 'Presidente/a-Primer/a suplente'],
            ['code' => 'PS2', 'eus' => 'Presidentea-Bigarren ordezkoa', 'es' => 'Presidente/a-Segundo/a suplente'],
            ['code' => 'V1', 'eus' => 'Lehen bokala', 'es' => 'Primer/a vocal'],
            ['code' => 'V1S1', 'eus' => 'Lehen bokala-Lehen ordezkoa', 'es' => 'Primer/a vocal-Primer/a suplente'],
            ['code' => 'V1S2', 'eus' => 'Lehen bokala-Bigarren ordezkoa', 'es' => 'Primer/a vocal-Segundo/a suplente'],
            ['code' => 'V2', 'eus' => 'Bigarren bokala', 'es' => 'Segundo/a vocal'],
            ['code' => 'V2S1', 'eus' => 'Bigarren bokala-Lehen ordezkoa', 'es' => 'Segundo/a vocal-Primer/a suplente'],
            ['code' => 'V2S2', 'eus' => 'Bigarren bokala-Bigarren ordezkoa', 'es' => 'Segundo/a vocal-Segundo/a suplente'],
        ];

        foreach ($positions as $position) {
            if ($position['code'] === $code) {
                return $position[$lang];
            }
        }

        return null; // Return null if code is not found
    }

    #[Route('/new', name: 'app_herritarra_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $herritarra = new Herritarra();
        $form = $this->createForm(HerritarraType::class, $herritarra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($herritarra);
            $entityManager->flush();

            return $this->redirectToRoute('app_herritarra_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('herritarra/new.html.twig', [
            'herritarra' => $herritarra,
            'form' => $form,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/{id}/ordezkatu/{hauteskundeaid}', name: 'app_herritarra_ordezkatu', methods: ['GET', 'POST'])]
    public function ordezkatu(
        Request $request,
        Herritarra $herritarra,
        int $hauteskundeaid,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $nextCode = $this->getNextCode($herritarra->getCargofinal());
        $nextHerritarra = $entityManager->getRepository(Herritarra::class)->findNextHerritarra($herritarra->getDist(), $herritarra->getSecc(), $herritarra->getMesa(), $nextCode);

        $form = $this->createForm(OrdezkatuType::class, $herritarra, [
            'action' => $this->generateUrl('app_herritarra_ordezkatu', ['id' => $herritarra->getId(), 'hauteskundeaid' => $hauteskundeaid]),
            'method' => 'POST',
            'secc' => $herritarra->getSecc(),
            'dist' => $herritarra->getDist(),
            'mesa' => $herritarra->getMesa(),
            'current_id' => $herritarra->getId(),
            'next_id' => $nextHerritarra->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Herritarra $nextHerritarra */
            $nextHerritarra = $form->get('NextHerritarra')->getData();

            // Baja
            $herritarra->setActive(-1); // Baja
            $entityManager->persist($herritarra);

            // Alta
            $nextHerritarra->setActive(1);
            $entityManager->persist($nextHerritarra);

            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('iibarguren@pasaia.net')
                ->to('iibarguren@pasaia.net')
                // ->cc('cc@example.com')
                // ->bcc('bcc@example.com')
                // ->replyTo('fabien@example.com')
                ->priority(Email::PRIORITY_HIGH)
                ->subject($herritarra->getHauteskundea().'-an ordezkapen berria.')
                ->htmlTemplate('herritarra/_email_ordezkatu.html.twig')
                ->locale('eu')
                ->context([
                    'herritarra' => $herritarra,
                    'nextHerritarra' => $nextHerritarra,
                ]);

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                throw new \RuntimeException($e->getMessage());
            }

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_herritarra_index', ['hauteskundeaid' => $hauteskundeaid], Response::HTTP_SEE_OTHER);
        }

        return $this->render('herritarra/ordezkatu.html.twig', [
            'herritarra' => $herritarra,
            'form' => $form->createView(),
        ]);
    }

    private function generateUserCodes($prefix, $count): array
    {
        $userCodes = [$prefix];
        for ($i = 1; $i <= $count; ++$i) {
            $userCodes[] = $prefix.'S'.$i;
        }

        return $userCodes;
    }

    private function getNextCode($code)
    {
        $userCodes = array_merge(
            $this->generateUserCodes('P', 30),
            $this->generateUserCodes('V1', 30),
            $this->generateUserCodes('V2', 30)
        );
        // Find the index of the current code
        $currentIndex = array_search($code, $userCodes, true);

        // If the current code is not found or it's the last one, return null
        if (false === $currentIndex || $currentIndex === count($userCodes) - 1) {
            return null;
        }

        // Return the next code
        return $userCodes[$currentIndex + 1];
    }

    #[Route('/{id}/edit', name: 'app_herritarra_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Herritarra $herritarra, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HerritarraType::class, $herritarra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_herritarra_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('herritarra/edit.html.twig', [
            'herritarra' => $herritarra,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_herritarra_delete', methods: ['POST'])]
    public function delete(Request $request, Herritarra $herritarra, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$herritarra->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($herritarra);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_herritarra_index', [], Response::HTTP_SEE_OTHER);
    }
}
