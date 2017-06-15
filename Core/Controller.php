<?php

namespace Core;

use App\Auth;
use App\Flash;

/**
 * Class Base Controller
 */
abstract class Controller
{
    /**
     * Parameters from the matched route
     *
     * @var array
     */
    protected $routeParams = [];

    /**
     * Controller constructor
     *
     * @param array $routeParams Parameters from the router
     */
    public function __construct(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @throws \Exception
     *
     * @return void
     */
    public function __call(string $name, array $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in Controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before an action method
     *
     * @return void
     */
    protected function before()
    {

    }

    /**
     * After filter - called after an action method
     *
     * @return void
     */
    protected function after()
    {

    }

    /**
     * Redirect to different page
     *
     * @param string $url The relative URL
     *
     * @return void
     */
    public function redirect(string $url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }

    /**
     * Require the user to be logged in before giving access to the requested page.
     * Remember the requested page for later, then redirect to the login page.
     *
     * @return void
     */
    public function requireLogin()
    {
        if (!Auth::getUser()) {
            Flash::addMessage('Please login to access that page', Flash::INFO);

            Auth::rememberRequestPage();

            $this->redirect('/login');
        }
    }
}