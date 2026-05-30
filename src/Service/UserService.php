<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function register(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $plainPassword)
        );
        $this->em->persist($user);
        $this->em->flush();
    }
}
