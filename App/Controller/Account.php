<?php

namespace App\Controller;

use App\Model\User as UserModel;
use Core\Controller;

class Account extends Controller
{
    /**
     * Validate if email is available (AJAX) for a new sign up
     */
    public function validateEmailAction()
    {
        $isValid = ! UserModel::emailExists($_GET['email'], $_GET['ignore_id'] ?? null);

        header('Content-type: application/json');
        echo json_encode($isValid);
    }
}