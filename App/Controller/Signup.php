<?php

namespace App\Controller;

use Core\Controller;
use Core\View;
use App\Model\User as UserModel;

/**
 * Signup Controller
 */
class Signup extends Controller
{
    /**
     * Show sign up page
     */
    public function newAction()
    {
        View::renderTemplate('Signup/new.html.twig');
    }

    /**
     * Sign up new user
     *
     * @return void
     */
    public function create()
    {
        $user = new UserModel($_POST);

        if ($user->save()) {
            $user->sendActivationEmail();
            $this->redirect('/signup/success');
        } else {
            View::renderTemplate('Signup/new.html.twig', [
                'user' => $user,
                'errors' => $user->getErrors()
            ]);
        }
    }

    /**
     * Show the sign up success page
     *
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Signup/success.html.twig');
    }

    /**
     * Activate new account
     *
     * @return void
     */
    public function activateAction()
    {
        UserModel::activate($this->routeParams['token']);

        $this->redirect('/signup/activated');
    }

    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html.twig');
    }
}