<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/admin/user')]
class UserController extends AbstractController
{

    public function __construct( 
        private UserRepository $repoUser)
    {
    }


    #[Route('', name:'admin.user.index')]
    public function indexUser(): Response
    {
        // Récupérer tous les Users
        $users = $this->repoUser->findAll();
        
        return $this->render('Backend/User/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/edit', name:'admin.user.edit')]
    public function editUser(?User $user, Request $request): Response|RedirectResponse
    {
        // Gere l'erreur
        if(!$user instanceof User)
        {
            $this->addFlash('error', 'L\'id de l\'utilisateur n\'existe pas.');
            return $this->redirectToRoute('admin.user.index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request); // Ecoute la requete et renvoie moi sil y en a
        
        if ($form->isSubmitted() && $form->isValid())
        { 
            // On envoie la modification du user en bdd
            $this->repoUser->add($user, true);

            // On ajoute un message de succès
            $this->addFlash('success', 'User modifié avec succes');
            
            // On redirige vers la page de liste users
            return $this->redirectToRoute('admin.user.index');
        }

        return $this->renderForm('Backend/User/edit.html.twig', [
            'form' => $form,
            'user' => $user
        ]);

    }

    #[Route('/{id}/delete', name: 'admin.user.delete', methods: ['POST'])]
    public function delete(?User $user, Request $request): Response|RedirectResponse
    {
        if(!$user instanceof User)
        {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin.user.index');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->get('_token'))) {
            $this->repoUser->remove($user, true);

            $this->addFlash('success', 'User deleted successfully.');

            return $this->redirectToRoute('admin.user.index');
        }

    }

}
