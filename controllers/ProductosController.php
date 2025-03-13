<?php

namespace Controllers;
use MVC\Router;

class ProductosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $router->render('admin/productos/index', [
            'titulo' => 'Productos'
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $alertas = [];

        $router->render('admin/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas
        ], 'admin-layout');
    }
}