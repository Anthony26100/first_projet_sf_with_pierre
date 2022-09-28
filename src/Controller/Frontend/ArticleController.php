<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Comment;
use App\Data\SearchData;
use App\Form\CommentType;
use App\Form\SearchArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route("/article")]
class ArticleController extends AbstractController
{
    public function __construct(
        private CommentRepository $commentRepository,
        private ArticleRepository $articleRepository
    ) {
    }

    #[Route('/liste', name: 'app.article.index')]
    public function listArticle(Request $request): Response|JsonResponse
    {
        $data = new SearchData;
        $page = $request->get('page', 1);
        $data->setPage($page);

        $form = $this->createForm(SearchArticleType::class, $data);
        $form->handleRequest($request);

        $articles = $this->articleRepository->findSearchData($data);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('Components/_articles.html.twig', [
                    'articles' => $articles
                ]),
                'sortable' => $this->renderView('Components/_sortable.html.twig', [
                    'articles' => $articles
                ]),
                'count' => $this->renderView('Components/_count.html.twig', [
                    'articles' => $articles
                ]),
                'pagination' => $this->renderView('Components/_pagination.html.twig', [
                    'articles' => $articles,
                ]),
                'pages' => ceil($articles->getTotalItemCount() / $articles->getItemNumberPerPage()) // Renvoie le nombres de pages pour enlever le btn "Voir plus"

            ]);
        }

        return $this->renderForm('Frontend/Article/liste.html.twig', [
            'articles' => $articles,
            'form' => $form,
        ]);
    }


    #[Route('/details/{slug}', name: 'user.article.detail', methods: ['GET', 'POST'])]
    public function detailArticle(?Article $article, Request $request, Security $security): Response|RedirectResponse
    {
        if (!$article instanceof Article) {
            $this->addFlash('error', 'Article not found');

            return $this->redirectToRoute('home');
        }

        $comments = $this->commentRepository->findByArticle($article->getId(), true);
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
