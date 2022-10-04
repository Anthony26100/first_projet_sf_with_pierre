<?php

namespace App\Controller\Frontend;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Classe Main Controller pour Page d'Accueil.
 */
class MainController extends AbstractController
{
    public function __construct(
        private ArticleRepository $repoArticle
    ) {
    }

    /**
     * Affiche la Page d'Accueil.
     *
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // Récupère tous les Articles
        $articles = $this->repoArticle->findLatestArticleWithLimit(6);

        return $this->render('Frontend/Home/index.html.twig', [
            'articles' => $articles,
            'currentPage' => 'home',
        ]);
    }
}
