<?php

namespace Controllers;
use MVC\Router;

class ProductosController {

    public static function index(Router $router) {
        $router->render('admin/productos/index', [
            'titulo' => 'Productos'
        ], 'admin-layout');
    }

    public static function crear(Router $router) {

        $alertas = [];

        $router->render('admin/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas
        ], 'admin-layout');
    }
}