<?php

namespace App\Core;

abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    protected function render(string $view, array $data = []): void
    {
        $this->view->render($view, $data);
    }

    protected function redirect(string $location): void
    {
        redirect($location);
    }

    protected function redirectTo(string $path): void
    {
        redirect_to($path);
    }

    protected function redirectBack(string $fallback = '/'): void
    {
        redirect_back($fallback);
    }
}
