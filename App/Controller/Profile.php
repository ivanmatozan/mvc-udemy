<?php

namespace App\Controller;

use App\Auth;
use App\Flash;
use Core\View;

/**
 * Profile controller
 */
class Profile extends Authenticated
{

    /**
     * Before filter - called before each action method
     *
     * @return void
     */
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    /**
     * Show the profile
     *
     * @return void
     */
    public function showAction()
    {
        View::renderTemplate('Profile/show.html.twig', [
            'user' => $this->user
        ]);
    }

    /**
     * Show the form for editing the profile
     *
     * @return void
     */
    public function editAction()
    {
        View::renderTemplate('Profile/edit.html.twig', [
            'user' => $this->user
        ]);
    }

    /**
     * Update the profile
     *
     * @return void
     */
    public function updateAction()
    {
        if ($this->user->updateProfile($_POST)) {
            Flash::addMessage('Changed saved');

            $this->redirect('/profile/show');
        }

        View::renderTemplate('Profile/edit.html.twig', [
            'errors' => $this->user->getErrors()
        ]);
    }
}