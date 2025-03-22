<?php

namespace Controllers;

use Model\Producto;
use MVC\Router;
use Model\Subcategoria;

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

        // Creando una nueva instancia de producto
        $producto = new Producto;

        // Manejo de errores
        $alertas = [];

        // Obteniendo todas las subcategorias
        $subcategorias = Subcategoria::all();

        // Ejecutar el codigo despues de que el usuario haya enviado el formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }

            $producto->sincronizar($_POST);

            // Validar
            $alertas = $producto->validar();

            // Guardar el registro
            if(empty($alertas)) {

                // Guardar en la BD
                $resultado = $producto->guardar();

                if($resultado) {
                    header('Location: /admin/categorias');
                }
            }
        }

        $router->render('admin/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas,
            'subcategorias' => $subcategorias,
            'producto' => $producto
        ], 'admin-layout');
    }
}