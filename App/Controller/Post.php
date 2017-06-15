<?php

namespace App\Controller;

use Core\Controller;
use Core\View;
use App\Model\Post as PostModel;

/**
 * Class Post controller
 */
class Post extends Controller
{
    /**
     * Show index page
     *
     * @return void
     */
    public function indexAction()
    {
        $posts = PostModel::getAll();

        View::renderTemplate('Post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * Show add new page
     *
     * @return void
     */
    public function addNewAction()
    {
        echo 'Hello from the addNew action in the Post controller';
    }

    /**
     * Show the edit page
     *
     * @return void
     */
    public function editAction()
    {
        echo 'Hello from the edit action in the Post controller';
        echo '<p>Route parameters: <pre>' .
            htmlspecialchars(print_r($this->routeParams, true)) . '</pre></p>';
    }
}