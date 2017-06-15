<?php

namespace App\Controller\Admin;

use Core\Controller;

class User extends Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // Make sure an admin is logged in for a example
//        return false;
    }

    /**
     * Show index page
     *
     * @return void
     */
    public function indexAction()
    {
        echo 'User admin index';
    }
}