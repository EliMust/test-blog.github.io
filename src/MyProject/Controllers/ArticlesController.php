<?php
//контроллер статей, где мы получаем только 1 статью
namespace MyProject\Controllers;

use MyProject\Exception\InvalidArgumentException;
use MyProject\Exceptions\Forbidden;
use MyProject\Exceptions\NotFoundException;
use MyProject\Exception\UnauthorizedException;
use MyProject\Models\Articles\Article;
use MyProject\Models\Articles\Comments;
use MyProject\Models\Users\User;

class ArticlesController extends AbstractController
{
    public function view(int $articleId): void
    {
        $article = Article::getById($articleId);

        if ($article === null) {
            // Здесь обрабатываем ошибку
            throw new NotFoundException();
        }

        $this->view->renderHtml('articles/view.php', [
            'article' => $article
            //'author' => $articleAuthor
        ]);
    }

    public function comments(): void
    {
        if ($this->user === null) {
            throw new \MyProject\Exceptions\UnauthorizedException();
        }

        if(!empty($_POST)) {
            try {
                $comments = Comments::createComment($_POST, $this->user);
            } catch (\MyProject\Exceptions\InvalidArgumentException $e) {
                $this->view->renderHtml('articles/comments.php' , ['error' => $e->getMessage()]);
                return;
            }

            header('Location: /articles/' . $comments->getId(), true, 302); // /comments/
            exit();
        }

        $this->view->renderHtml('articles/comments.php');
    }

    //статья будет обновляться данными из POST-запроса->редактирование
    public function edit(int $articleId)
    {
        /** @var Article $article */
        $article = Article::getById($articleId);

        if ($article === null) {
            throw new NotFoundException();
        }

        if ($this->user === null) {
            throw new \MyProject\Exceptions\UnauthorizedException();
        }

        if(!$this->user->isAdmin()) {
            throw new Forbidden('Для редактирования статьи нужно обладать правами администратора');
        }

        if (!empty($_POST)) {
            try {
                $article->updateFromArray($_POST);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('articles/edit.php', ['error' => $e->getMessage(), 'article' => $article]);
                return;
            }

            header('Location: /articles' . $article->getId(), true, 302);
            exit();
        }

        $this->view->renderHtml('articles/edit.php', ['article' => $article]);
    }

    public function add(): void
    {
        //$author = User::getById(1);
        if ($this->user === null) {
            throw new \MyProject\Exceptions\UnauthorizedException();
        }

        if (!empty($_POST)) {
            try {
                $article = Article::createFromArray($_POST, $this->user);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('articles/add.php', ['error' => $e->getMessage()]);
                return;
            }

            header('Location: /articles/' . $article->getId(), true, 302);
            exit();
        }

        if (!$this->user->isAdmin()) {
            throw new Forbidden('Для добавления статьи нужно обладать правами администратора');
        }

        $this->view->renderHtml('articles/add.php');
    }

    //добавление комментария под статьей
    public function addComment(): void
    {
        if (!empty($_POST)) {
            try {
                $comment = Comment::add($_POST, $this->user);
            } catch (InvalidArgumentException $e) {
                $this->view->setVar('error', $e->getMessage());
                $this->view($_POST['articleId']);
                return;
            }

            header('Location: /articles/' . $_POST['articleId'] . '#comment' . $comment->getId(), true, 302);
            exit();
        }

    }

    public function delete(int $articleId)
    {
        $article = Article::getById($articleId);

        if($article === null) {
            echo 'Статья не найдена!';
        } else {
            $article->delete();
            echo 'Статья удалена!';
            var_dump($article);
        }
    }
}