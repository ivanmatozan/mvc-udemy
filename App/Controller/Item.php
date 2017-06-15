<?php

namespace App\Controller;

use Core\View;

/**
 * Class Items Controller
 */
class Item extends Authenticated
{
    /**
     * Items index
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Item/index.html.twig');
    }
}