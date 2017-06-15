<?php

namespace App\Controller;

use Core\Controller;

/**
 *  Authenticated base Controller
 */
abstract class Authenticated extends Controller
{
    /**
     * Require the user to be authenticated before giving
     * access to all methods in the controller
     *
     * @return void
     */
    protected function before()
    {
        $this->requireLogin();
    }
}