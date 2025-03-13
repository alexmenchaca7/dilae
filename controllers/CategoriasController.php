<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Categoria;
use Model\Subcategoria;
use MVC\Router;

class CategoriasController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Paginación
        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if(!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/categorias?page=1');
        }

        $registros_por_pagina = 5;
        $total = Categoria::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/categorias?page=1');
        }

        // Obtener las categorias por paginacion
        $categorias = Categoria::paginar($registros_por_pagina, $paginacion->offset());

        // Crear un array para almacenar las subcategorías agrupadas por categoriaId
        $subcategoriasPorCategoria = [];

        // Obtener todas las subcategorías
        $subcategorias = Subcategoria::all();

        // Agrupar las subcategorías por categoriaId
        foreach ($subcategorias as $subcategoria) {
            $subcategoriasPorCategoria[$subcategoria->categoriaId][] = $subcategoria;
        }

        // Pasar las categorías y las subcategorías agrupadas a la vista
        $router->render('admin/categorias/index', [
            'titulo' => 'Categorias',
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }


    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Creando una nueva instancia de Categoria
        $categoria = new Categoria;

        // Manejo de alertas
        $alertas = [];

        // Ejecutar el codigo despues de que el usuario envia el formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }

            $categoria->sincronizar($_POST);

            // Validar
            $alertas = $categoria->validar();

            // Guardar el registro
            if(empty($alertas)) {

                // Guardar en la BD
                $resultado = $categoria->guardar();

                if($resultado) {
                    header('Location: /admin/categorias');
                }
            }
        }

        $router->render('admin/categorias/crear', [
            'titulo' => 'Registrar Categoria',
            'alertas' => $alertas,
            'categoria' => $categoria
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Manejo de alertas
        $alertas = [];
        
        // Validar ID
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/categorias');
        }

        // Obtener categoria a editar
        $categoria = Categoria::find($id);

        if(!$categoria) {
            header('Location: /admin/categorias');
        }

        // Ejecutar el codigo despues de que el usuario envia el formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }

            $categoria->sincronizar($_POST);

            // Validar
            $alertas = $categoria->validar();

            // Guardar el registro
            if(empty($alertas)) {

                // Guardar en la BD
                $resultado = $categoria->guardar();

                if($resultado) {
                    header('Location: /admin/categorias');
                }
            }
        }
                

        $router->render('admin/categorias/editar', [
            'titulo' => 'Actualizar Categoria',
            'alertas' => $alertas,
            'categoria' => $categoria
        ], 'admin-layout');
    }

    public static function eliminar() {  
        if(!is_auth()) {
            header('Location: /login');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $categoria = Categoria::find($id);

            // Validar que sea una categoria existente
            if(!isset($categoria)) {
                header('Location: /admin/categorias');
            }

            // Eliminar de la BD
            $resultado = $categoria->eliminar();

            if($resultado) {
                header('Location: /admin/categorias');
            }
        }
    }
}