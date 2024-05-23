<?php

namespace App\Twig\Components\Form;

use App\Entity\Hauteskundea;
use App\Form\HauteskundeaWizardType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class HauteskundeaFormWizard extends AbstractController
{
    use ComponentWithFormTrait; // Hau aktibatzen bada live validation egitean Trix editorea desagertzen da, beraz, desaktibatu
    use DefaultActionTrait;

    #[LiveProp]
    public ?Hauteskundea $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        $hauteskundea = $this->initialFormData ?? new Hauteskundea();

        return $this->createForm(HauteskundeaWizardType::class, $hauteskundea, [
            'action' => $hauteskundea->getId() ? $this->generateUrl('app_hauteskundea_edit', ['id' => $hauteskundea->getId()]) : $this->generateUrl('app_hauteskundea_wizard'),
        ]);
    }
}
