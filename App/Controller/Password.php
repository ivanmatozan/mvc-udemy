<?php

namespace App\Controller;

use Core\Controller;
use Core\View;
use App\Model\User;

class Password extends Controller
{
    /**
     * Show the forgotten password page
     *
     * @return void
     */
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html.twig');
    }

    /**
     * Send the reset password link to the supplied email
     *
     * @return void
     */
    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);

        View::renderTemplate('Password/reset-requested.html.twig');
    }

    /**
     * Show the reset password form
     *
     * @return void
     */
    public function resetAction()
    {
        $token = $this->routeParams['token'];

        $user = $this->getUserOrExit($token);

        View::renderTemplate('Password/reset.html.twig', [
            'token' => $token
        ]);
    }

    /**
     * Reset the user's password
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        $token = $_POST['token'];

        $user = $this->getUserOrExit($token);

        if ($user->resetPassword($_POST['password'])) {
            View::renderTemplate('Password/reset-success.html.twig');
        } else {
            View::renderTemplate('Password/reset.html.twig', [
                'token' => $token,
                'errors' => $user->getErrors()
            ]);
        }
    }

    /**
     * Find the User model associated with the password reset token,
     * or end the request with a message
     *
     * @param string $token Password reset token sent to user
     *
     * @return mixed User object if found and token hasn't expired, null otherwise
     */
    protected function getUserOrExit(string $token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        }

        View::renderTemplate('Password/token-expired.html.twig');

        exit;
    }
}