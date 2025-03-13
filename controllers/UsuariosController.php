<?php

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\Usuario;
use Classes\Paginacion;

class UsuariosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Paginación
        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if(!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/usuarios?page=1');
        }

        $registros_por_pagina = 10;
        $total = Usuario::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/usuarios?page=1');
        }

        // Obtener los usuarios por paginacion
        $usuarios = Usuario::paginar($registros_por_pagina, $paginacion->offset());

        // Pasar los usuarios a la vista
        $router->render('admin/usuarios/index', [
            'titulo' => 'Usuarios',
            'usuarios' => $usuarios,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarRegistro();

            if(empty($alertas)) {
                $usuario->crearToken(); // Crear un token para la configuración de la contraseña
                $resultado = $usuario->guardar();

                if($resultado) {
                    // Enviar el email de configuración de contraseña
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    // Redirigir a la vista de confirmación
                    header('Location: /admin/usuarios/crear?confirmacion=1');
                    exit();
                }
            }
        }

        $router->render('admin/usuarios/crear', [
            'titulo' => 'Registrar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $alertas = [];
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/usuarios');
        }

        $usuario = Usuario::find($id);

        if(!$usuario) {
            header('Location: /admin/usuarios');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarRegistro();

            if(empty($alertas)) {
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /admin/usuarios');
                }
            }
        }

        $router->render('admin/usuarios/editar', [
            'titulo' => 'Actualizar Usuario',
            'alertas' => $alertas,
            'usuario' => $usuario
        ], 'admin-layout');
    }

    public static function eliminar() {
        if(!is_auth()) {
            header('Location: /login');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $usuario = Usuario::find($id);

            if(!isset($usuario)) {
                header('Location: /admin/usuarios');
            }

            $resultado = $usuario->eliminar();

            if($resultado) {
                header('Location: /admin/usuarios');
            }
        }
    }
}