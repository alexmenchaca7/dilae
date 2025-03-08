<?php

namespace Controllers;

use Model\Categoria;
use Model\Subcategoria;
use MVC\Router;

class CategoriasController {

    public static function index(Router $router) {

        // Obtener todas las categorías
        $categorias = Categoria::all();

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
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria
        ], 'admin-layout');
    }


    public static function crear(Router $router) {

        // Creando una nueva instancia de Categoria
        $categoria = new Categoria;

        // Manejo de alertas
        $alertas = [];

        // Ejecutar el codigo despues de que el usuario envia el formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
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