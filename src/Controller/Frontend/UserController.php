<?php

namespace App\Controller\Frontend;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    #[Route('/compte', name: 'app.user.compte')]
    public function compte(Security $security): Response
    {
        $user = $security->getUser(); // recuperer utilisateur connecter
       

        return $this->render('Frontend/Compte/index.html.twig', [
            'user' => $user,
        ]);
    }
}
