<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/article")]
class ArticleController extends AbstractController
{

    #[Route('/details/{slug}', name: 'user.article.detail', methods: 'GET')]
    public function detailArticle(?Article $article): Response|RedirectResponse
    {
        if (!$article instanceof Article) {
            $this->addFlash('error', 'Article not found');

            return $this->redirectToRoute('home');
        }

        return $this->render('Frontend/Article/details.html.twig', [
            'article' => $article,
        ]);
    }
}
