<?php

namespace App\Controller;

use App\Auth;
use App\Flash;
use Core\Controller;
use Core\View;
use App\Model\User as UserModel;

/**
 * Login Controller
 */
class Login extends Controller
{
    /**
     * Show login screen
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Login/new.html.twig');
    }

    /**
     * Log in a user
     *
     * @return void
     */
    public function createAction()
    {
        $user = UserModel::authenticate($_POST['email'], $_POST['password']);

        $rememberMe = isset($_POST['remember_me']);

        if ($user) {
            Auth::login($user, $rememberMe);

            Flash::addMessage('Login successful');

            $this->redirect(Auth::getReturnToPage());
        } else {
            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);

            View::renderTemplate('Login/new.html.twig', [
                'email' => $_POST['email'],
                'rememberMe' => $rememberMe
            ]);
        }
    }

    /**
     * Log out a user
     *
     * @return void
     */
    public function destroyAction()
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }

    /**
     * Show a "logged out" message and redirects to the homepage.
     * Necessary to use flash messages as they use the session and at
     * the end of logout method (destroyAction) the session is destroyed
     * so a new action needs to be called in order to use the session.
     *
     * @return void
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful');

        $this->redirect('/');
    }
}