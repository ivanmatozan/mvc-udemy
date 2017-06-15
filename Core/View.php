<?php

namespace Core;

use App\Auth;
use App\Flash;

/**
 * Class View
 */
class View
{
    /**
     * Render a view file
     *
     * @param string $view The view file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @throws \Exception
     *
     * @return void
     */
    public static function render(string $view, array $args = [])
    {
        extract($args, EXTR_SKIP);

        $file = "../App/View/$view"; // Relative to Core directory

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("''$file'' not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate(string $template, array $args = [])
    {
        echo static::getTemplate($template, $args);
    }

    /**
     * Get the contents of a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return string
     */
    public static function getTemplate(string $template, array $args = []): string
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/View');
            $twig = new \Twig_Environment($loader, ['debug' => true]);
            $twig->addExtension(new \Twig_Extension_Debug());

            $twig->addGlobal('currentUser', Auth::getUser());
            $twig->addGlobal('flashMessages', Flash::getMessages());
        }

        return $twig->render($template, $args);
    }
}