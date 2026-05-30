<?php

namespace App\Service;

use App\Entity\Contacts;
use Doctrine\ORM\EntityManagerInterface;

class ContactService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(Contacts $contact): void
    {
        $this->em->persist($contact);
        $this->em->flush();
    }
}
