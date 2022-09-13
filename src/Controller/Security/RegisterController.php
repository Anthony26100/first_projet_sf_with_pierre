<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController {

  #[Route('/register', name:'register')]
  public function register(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher, 
    UserRepository $repo) : Response|RedirectResponse
  {
    $users = new User();

    $form = $this->createForm(RegisterFormType::class, $users);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) { 
      $users->setPassword(
        $userPasswordHasher->hashPassword(
          $users,
          $form->get('password')->getData()
        )
      );

      $repo->add($users, true);

      $this->addFlash('success', 'Inscription réussite');

      return $this->redirectToRoute('login');
    }

    return $this->renderForm('Security/register.html.twig', [
      'form' => $form
    ]);
  }
}