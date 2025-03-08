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

    public static function crearSubcategoria(Router $router) {

        // Creando una nueva instancia de Subcategoria
        $subcategoria = new Subcategoria;

        // Manejo de alertas
        $alertas = [];

        // Consulta para obtener todas las categorias
        $categorias = Categoria::all(); 

        // Ejecutar el codigo despues de que el usuario envia el formulario
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subcategoria->sincronizar($_POST);

            // Validar
            $alertas = $subcategoria->validar();

            // Guardar el registro
            if(empty($alertas)) {

                // Guardar en la BD
                $resultado = $subcategoria->guardar();

                if($resultado) {
                    header('Location: /admin/categorias');
                }
            }
        }

        $router->render('admin/categorias/subcategorias/crear', [
            'titulo' => 'Registrar Subcategoria',
            'alertas' => $alertas,
            'subcategoria' => $subcategoria,
            'categorias' => $categorias
        ], 'admin-layout');
    }

    public static function editar(Router $router) {

        // Creando una nueva instancia de Categoria
        $categoria = '';
        
        // Manejo de alertas
        $alertas = [];

        $router->render('admin/categorias/editar', [
            'titulo' => 'Editar Categoria',
            'alertas' => $alertas,
            'categoria' => $categoria
        ], 'admin-layout');
    }
}