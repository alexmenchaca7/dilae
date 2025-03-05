<?php

namespace Controllers;
use MVC\Router;

class ProyectosController {

    public static function index(Router $router) {
        $router->render('admin/proyectos/index', [
            'titulo' => 'Proyectos'
        ], 'admin-layout');
    }
}