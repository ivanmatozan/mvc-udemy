<?php

namespace App\Controller;

use Core\Controller;
use Core\View;

class Home extends Controller
{
    /**
     * Before filter
     */
    protected function before()
    {
//        echo '(before)';
//        return false;
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
//        echo '(after)';
    }

    /**
     * Show index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Home/index.html.twig');
    }
}