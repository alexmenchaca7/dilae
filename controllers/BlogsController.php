<?php

namespace Controllers;

use MVC\Router;

class BlogsController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $router->render('admin/blogs/index', [
            'titulo' => 'Blogs',
        ], 'admin-layout');
    }
}