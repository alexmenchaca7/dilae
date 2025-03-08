<?php

namespace Controllers;

use Model\Categoria;
use Model\Subcategoria;
use MVC\Router;

class SubcategoriasController {
    public static function crear(Router $router) {

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

        $router->render('admin/subcategorias/crear', [
            'titulo' => 'Registrar Subcategoria',
            'alertas' => $alertas,
            'subcategoria' => $subcategoria,
            'categorias' => $categorias
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

        // Obtener subcategoria a editar
        $subcategoria = Subcategoria::find($id);

        if(!$subcategoria) {
            header('Location: /admin/categorias');
        }

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
                

        $router->render('admin/subcategorias/editar', [
            'titulo' => 'Actualizar Subcategoria',
            'alertas' => $alertas,
            'subcategoria' => $subcategoria,
            'categorias' => $categorias
        ], 'admin-layout');
    }
}