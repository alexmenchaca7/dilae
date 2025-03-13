<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class UsuariosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Obtener todos los usuarios
        $usuarios = Usuario::all();

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/index', [
            'titulo' => 'Categorias',
            'usuarios' => $usuarios
        ], 'admin-layout');
    }
}