<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

#[Route("/article")]
class ArticleController extends AbstractController
{
    public function __construct(
        private CommentRepository $commentRepository,
    ) {
    }

    #[Route('/details/{slug}', name: 'user.article.detail', methods: ['GET', 'POST'])]
    public function detailArticle(?Article $article, Request $request, Security $security): Response|RedirectResponse
    {
        if (!$article instanceof Article) {
            $this->addFlash('error', 'Article not found');

            return $this->redirectToRoute('home');
        }

        $comments = $this->commentRepository->findActiveByArticle($article->getId());
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setUser($security->getUser());
            $comment->setArticle($article);
            $comment->setActive(true);
            $this->commentRepository->add($comment, true);
            $this->addFlash('success', 'Commentaire Validé');

            return $this->redirectToRoute('user.article.detail', [
                'slug' => $article->getSlug(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Frontend/Article/details.html.twig', [
            'article' => $article,
            'form' => $form,
            'comments' => $comments,
        ]);
    }
}
