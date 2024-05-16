<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PasaiaRoleService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getRoles($nan): array
    {
        $langileRepository = $this->em->getRepository(User::class);

        return $langileRepository->findOneBy(['NA' => $nan])->getRoles();
    }
}
