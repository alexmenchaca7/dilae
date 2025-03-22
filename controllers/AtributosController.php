<?php

namespace Controllers;

use MVC\Router;
use Model\Atributo;

class AtributosController {
    // Listar todos los atributos
    public static function index(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        $atributos = Atributo::all();

        $router->render('admin/atributos/index', [
            'titulo' => 'Atributos',
            'atributos' => $atributos
        ], 'admin-layout');
    }

    // Crear un nuevo atributo
    public static function crear(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        $atributo = new Atributo;
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $atributo->sincronizar($_POST);
            $alertas = $atributo->validar();

            if (empty($alertas)) {
                $resultado = $atributo->guardar();
                if ($resultado) {
                    header('Location: /admin/atributos');
                }
            }
        }

        $router->render('admin/atributos/crear', [
            'titulo' => 'Crear Atributo',
            'alertas' => $alertas,
            'atributo' => $atributo
        ], 'admin-layout');
    }

    // Editar un atributo existente
    public static function editar(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        $alertas = [];
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            header('Location: /admin/atributos');
        }

        $atributo = Atributo::find($id);
        if (!$atributo) {
            header('Location: /admin/atributos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $atributo->sincronizar($_POST);
            $alertas = $atributo->validar();

            if (empty($alertas)) {
                $resultado = $atributo->guardar();
                if ($resultado) {
                    header('Location: /admin/atributos');
                }
            }
        }

        $router->render('admin/atributos/editar', [
            'titulo' => 'Editar Atributo',
            'alertas' => $alertas,
            'atributo' => $atributo
        ], 'admin-layout');
    }

    // Eliminar un atributo
    public static function eliminar(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $atributo = Atributo::find($id);
            if (!$atributo) {
                header('Location: /admin/atributos');
            }
            $resultado = $atributo->eliminar();
            if ($resultado) {
                header('Location: /admin/atributos');
            }
        }
    }
}
