<?php

namespace App\Controller;

use App\Entity\Hauteskundea;
use App\Form\HauteskundeaType;
use App\Form\HauteskundeaWizard1Type;
use App\Form\HauteskundeaWizard2Type;
use App\Form\HauteskundeaWizard3Type;
use App\Form\HauteskundeaWizard4Type;
use App\Form\HauteskundeaWizard5Type;
use App\Repository\HauteskundeaRepository;
use App\Utils\CsvHelper;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/kudeatu')]
#[Route('/kudeatu/{_locale}')]
class HauteskundeaController extends AbstractController
{
    #[Route('/', name: 'app_hauteskundea_index', methods: ['GET'])]
    public function index(
        HauteskundeaRepository $hauteskundeaRepository,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $sort = 'izena_eu',
        #[MapQueryParameter] string $sortDirection = 'ASC',
        #[MapQueryParameter] ?string $query = null
    ): Response {
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($hauteskundeaRepository->getAllQueryBuilder($query, $sort, $sortDirection)),
            $page,
            10
        );

        return $this->render('hauteskundea/index.html.twig', [
            'hauteskundeas' => $pager,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
        ]);
    }

    #[Route('/wizard', name: 'app_hauteskundea_wizard', methods: ['GET', 'POST'])]
    public function wizard1(Request $request, EntityManagerInterface $entityManager, #[MapQueryParameter] ?int $id = null): Response
    {
        $hauteskundea = null;
        if (null !== $id) {
            $hauteskundea = $entityManager->getRepository(Hauteskundea::class)->find($id);
        } else {
            $hauteskundea = new Hauteskundea();
        }

        $form = $this->createHauteskundeaForm($hauteskundea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_hauteskundea_wizard2', ['id' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/wizard1.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/wizard2', name: 'app_hauteskundea_wizard2', methods: ['GET', 'POST'])]
    public function wizard2(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager, CsvHelper $csvHelper): Response
    {
        $form = $this->createForm(HauteskundeaWizard2Type::class, $hauteskundea, [
            'action' => $this->generateUrl('app_hauteskundea_wizard2', ['id' => $hauteskundea->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_hauteskundea_wizard3', ['id' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/wizard2.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/wizard3', name: 'app_hauteskundea_wizard3', methods: ['GET', 'POST'])]
    public function wizard3(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager, CsvHelper $csvHelper): Response
    {
        $form = $this->createForm(HauteskundeaWizard3Type::class, $hauteskundea, [
            'action' => $this->generateUrl('app_hauteskundea_wizard3', ['id' => $hauteskundea->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_hauteskundea_wizard4', ['id' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/wizard3.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/wizard4', name: 'app_hauteskundea_wizard4', methods: ['GET', 'POST'])]
    public function wizard4(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager, CsvHelper $csvHelper): Response
    {
        $form = $this->createForm(HauteskundeaWizard4Type::class, $hauteskundea, [
            'action' => $this->generateUrl('app_hauteskundea_wizard4', ['id' => $hauteskundea->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_hauteskundea_wizard5', ['id' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/wizard4.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/wizard5', name: 'app_hauteskundea_wizard5', methods: ['GET', 'POST'])]
    public function wizard5(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager, CsvHelper $csvHelper): Response
    {
        $form = $this->createForm(HauteskundeaWizard5Type::class, $hauteskundea, [
            'action' => $this->generateUrl('app_hauteskundea_wizard5', ['id' => $hauteskundea->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            $erroldaFile = $form['errolda']->getData();
            $zozketaFile = $form['zozketa']->getData();

            $responseErrolda = $csvHelper->importErrolda($erroldaFile);
            if ('error' === $responseErrolda['result']) {
                return $this->render('hauteskundea/csverror.html.twig', $responseErrolda);
            }
            $responseZozketa = $csvHelper->importZozketa($zozketaFile, $hauteskundea->getId());
            if ('error' === $responseZozketa['result']) {
                return $this->render('hauteskundea/csverror.html.twig', $responseZozketa);
            }

            $responseMerge = $csvHelper->mergeCsv($hauteskundea->getId());
            if ('error' === $responseMerge['result']) {
                return $this->render('hauteskundea/csverror.html.twig', $responseMerge);
            }

            try {
                $csvHelper->hustuErrolda();
            } catch (Exception $e) {
                return $this->render('hauteskundea/csverror.html.twig', [
                    'exception' => $e->getMessage(),
                    'ident' => '',
                    'herritarra' => '',
                ]);
            }

            return $this->redirectToRoute('app_hauteskundea_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/wizard5.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_hauteskundea_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hauteskundea = new Hauteskundea();
        $form = $this->createForm(HauteskundeaType::class, $hauteskundea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $form = $this->createHauteskundeaForm();

                return $this->renderBlock('hauteskundea/new.html.twig', 'stream_success', [
                    'form' => $form,
                    'hauteskundea' => $hauteskundea,
                ]);
            }

            return $this->redirectToRoute('app_hauteskundea_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/new.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_hauteskundea_show', methods: ['GET'])]
    public function show(Hauteskundea $hauteskundea): Response
    {
        return $this->render('hauteskundea/show.html.twig', [
            'hauteskundea' => $hauteskundea,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hauteskundea_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createHauteskundeaForm($hauteskundea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hauteskundea);
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            }

            return $this->redirectToRoute('app_hauteskundea_wizard2', ['id' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hauteskundea/edit.html.twig', [
            'hauteskundea' => $hauteskundea,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_hauteskundea_delete', methods: ['POST'])]
    public function delete(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hauteskundea->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($hauteskundea);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hauteskundea_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/hustu', name: 'app_hauteskundea_hustu', methods: ['POST'])]
    public function hustu(Request $request, Hauteskundea $hauteskundea, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hauteskundea->getId(), $request->getPayload()->get('_token'))) {
            foreach ($hauteskundea->getHerritarrak() as $herritarra) {
                $entityManager->remove($herritarra);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_herritarra_index', ['hauteskundeaid' => $hauteskundea->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/csverror', name: 'app_herritarra_csv_error', methods: ['GET'])]
    public function csverror(): Response
    {
        return $this->render('hauteskundea/csverror.html.twig', []);
    }

    private function createHauteskundeaForm(?Hauteskundea $hauteskundea = null): FormInterface
    {
        $hauteskundea = $hauteskundea ?? new Hauteskundea();

        return $this->createForm(HauteskundeaWizard1Type::class, $hauteskundea, [
            'action' => $hauteskundea->getId() ? $this->generateUrl('app_hauteskundea_edit', ['id' => $hauteskundea->getId()]) : $this->generateUrl('app_hauteskundea_wizard'),
        ]);
    }
}
