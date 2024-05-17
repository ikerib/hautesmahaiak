<?php

namespace App\Controller;

use App\Entity\Herritarra;
use App\Repository\HauteskundeaRepository;
use App\Repository\HerritarraRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('app_kontsulta', ['_locale' => 'eu']);
    }

    #[Route('/healthcheck', name: 'app_healthcheck')]
    public function healthcheck(LoggerInterface $logger): Response
    {
        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            $scope->setContext('fun', [
                'healthcheck' => 'yes',
            ]);
        });
        $logger->error('healthcheck => My custom logged error.');
        throw new \RuntimeException('healthcheck => Example exception.');

        return new Response(
            '<html><body>Hello World</body></html>'
        );
    }

    #[Route('/kontsulta/{_locale}/', name: 'app_kontsulta')]
    public function kontsulta(
        Request $request,
        HerritarraRepository $herritarraRepository,
        HauteskundeaRepository $hauteskundeaRepository,
        TranslatorInterface $translator
    ): Response {
        $mahaia = new Herritarra();
        $locale = $request->getLocale();

        $form = $this->createFormBuilder($mahaia)
            ->add('ident', TextType::class, [
                'label' => $translator->trans('NAN / DNI', [], 'messages'),
                'required' => true,
                'attr' => [
                    'placeholder' => '12345678X',
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 pl-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                ]]
            )
            ->add('jaioteguna', DateType::class, [
                'label' => $translator->trans('Jaiotze-data / Fecha Nacimiento', [], 'messages'),
                'widget' => 'single_text',
                'html5' => false,
                'placeholder' => 'YYYY-MM-DD',
                'help_attr' => [
                    'class' => 'mt-0important text-sm text-gray-500 dark:text-gray-400',
                ],
                'attr' => [
                    'autocomplete' => false,
                    'data-controller' => 'datepicker',
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 pl-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                ],
                'label_attr' => [
                ],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => $translator->trans('Bilatu / Buscar', [], 'messages')])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mahaiak = $herritarraRepository->findBy(['ident' => $mahaia->getIdent(), 'jaioteguna' => $mahaia->getJaioteguna(), 'active' => '1']);

            return $this->render('default/index.html.twig', [
                'form' => $form,
                'mahaiak' => $mahaiak,
                'hauteskundea' => $hauteskundeaRepository->getActive(),
            ]);
        }

        return $this->render('default/index.html.twig', [
            'form' => $form,
            'hauteskundea' => $hauteskundeaRepository->getActive(),
        ]);
    }
}
