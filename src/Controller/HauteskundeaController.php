<?php

namespace App\Controller;

use App\Entity\Hauteskundea;
use App\Form\HauteskundeaType;
use App\Repository\HauteskundeaRepository;
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
        #[MapQueryParameter] string $sort = 'izena',
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

    #[Route('/new', name: 'app_hauteskundea_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hauteskundea = new Hauteskundea();
        $form = $this->createHauteskundeaForm($hauteskundea);
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
            $entityManager->flush();
            $this->addFlash('success', 'Datuak ongi gorde dira.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->renderBlock('hauteskundea/edit.html.twig', 'stream_success', [
                    'hauteskundea' => $hauteskundea,
                ]);
            }
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

        return $this->createForm(HauteskundeaType::class, $hauteskundea, [
            'action' => $hauteskundea->getId() ? $this->generateUrl('app_hauteskundea_edit', ['id' => $hauteskundea->getId()]) : $this->generateUrl('app_hauteskundea_new'),
        ]);
    }
}
