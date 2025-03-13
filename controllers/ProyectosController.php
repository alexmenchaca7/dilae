<?php

namespace Controllers;
use MVC\Router;

class ProyectosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }
        
        $router->render('admin/proyectos/index', [
            'titulo' => 'Proyectos'
        ], 'admin-layout');
    }
}