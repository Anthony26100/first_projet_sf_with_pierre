<?php

namespace App\Controller\Backend;

use App\Entity\Article;
use App\Entity\Comment;
use App\Data\SearchData;
use App\Form\ArticleType;

use App\Form\SearchArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class ArticleController extends AbstractController
{

    public function __construct(
        private ArticleRepository $repoArticle,
        private CommentRepository $commentRepository
    ) {
    }

    // #Route commentaire du routage
    #[Route('', name: 'admin')]
    public function index(Request $request): Response|JsonResponse
    {
        $data = new SearchData;
        $page = $request->get('page', 1);
        $data->setPage($page);

        $form = $this->createForm(SearchArticleType::class, $data);
        $form->handleRequest($request);

        $articles = $this->repoArticle->findSearchData($data, false); // false pour afficher tout nos articles cote admin

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('Components/_articles.html.twig', [
                    'articles' => $articles,
                    'admin' => true
                ]),
                'sortable' => $this->renderView('Components/_sortable.html.twig', [
                    'articles' => $articles,
                    'admin' => true
                ]),
                'count' => $this->renderView('Components/_count.html.twig', [
                    'articles' => $articles,
                    'admin' => true
                ]),
                'pagination' => $this->renderView('Components/_pagination.html.twig', [
                    'articles' => $articles,
                    'admin' => true
                ]),
                'pages' => ceil($articles->getTotalItemCount() / $articles->getItemNumberPerPage()) // Renvoie le nombres de pages pour enlever le btn "Voir plus"

            ]);
        }

        return $this->renderForm('Backend/index.html.twig', [
            'articles' => $articles,
            'form' => $form,
            // 'currentPage' => 'articles'
        ]);
    }


    #[Route('/article/create', name: 'admin.article.create')]
    public function createArticle(Request $request, Security $security): Response|RedirectResponse
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setUser($security->getUser());

            $this->repoArticle->add($article, true);

            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('admin');
        }

        return $this->render('Backend/Article/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/edit/{id}', name: 'admin.article.edit', methods: 'GET|POST')]
    public function editArticle($id, Request $request)
    {
        $article = $this->repoArticle->find($id);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->repoArticle->add($article, true);
            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('admin');
        }

        return $this->render('Backend/Article/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/delete/{id}', name: 'admin.article.delete', methods: 'DELETE|POST')]
    public function deleteArticle($id, Article $article, Request $request)

    {
        $article = $this->repoArticle->find($id);

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->get("_token"))) {
            $this->repoArticle->remove($article, true);

            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('admin');
    }

    // Correction
    #[Route('/article/{slug}/comments', name: 'admin.article.comments', methods: ['GET'])]
    public function adminComments(?Article $article): Response|RedirectResponse
    {
        // Condition n'est pas une instance de la table article
        if (!$article instanceof Article) {
            $this->addFlash('error', 'Article non trouvé');
            // On redirige 
            return $this->redirectToRoute('admin');
        }

        $comments = $this->commentRepository->findByArticle($article->getId());

        // Condition s'y pas de commentaire
        if (!$comments) {
            $this->addFlash('error', 'Aucun commentaire trouvé pour cet article');

            return $this->redirectToRoute('admin');
        }

        return $this->render('Backend/Comment/index.html.twig', [
            'comments' => $comments,
            'article' => $article
        ]);
    }

    #[Route('/{id}/comments/delete', name: 'admin.comments.delete', methods: ['POST', 'DELETE'])]
    public function deleteComment(?Comment $comment, Request $request): RedirectResponse
    {
        if (!$comment instanceof Comment) {
            $this->addFlash('error', 'Aucun commentaire');
            return $this->redirectToRoute('admin.article.comments');
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->get('_token'))) {
            $this->commentRepository->remove($comment, true);
            $this->addFlash('success', 'Commentaires supprimé avec succès !');

            return $this->redirectToRoute('admin.article.comments', [
                'slug' => $comment->getArticle()->getSlug(),
            ]);
        }

        return $this->redirectToRoute('admin.article.comments', [
            'slug' => $comment->getArticle()->getSlug(),
        ]);
    }


    #[Route("/comments/switch/{id}", name: 'admin.comments.switch', methods: ['GET'])]
    public function switchVisibilityComments(?Comment $comment): Response
    {
        if (!$comment instanceof Comment) {
            return new Response('Commentaire non trouvé', 404);
        }

        $comment->setActive(!$comment->isActive());
        $this->commentRepository->add($comment, true);

        return new Response('Commentaire changé avec succès', 201);
    }

    #[Route('/switch/{id}', name: 'app.switch.admin', methods: ['GET'])]
    public function switchVisibilityArt(?Article $article): Response
    {
        if ($article instanceof Article) {
            $article->setActive(!$article->isActive());
            $this->repoArticle->add($article, true);
            return new Response('Visibility change with success', 201);
        }



        return new Response('Article non trouvée', 404);
    }
}
