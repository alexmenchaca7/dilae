<?php

namespace Controllers;

use MVC\Router;
use Model\Atributo;
use Model\Categoria;
use Classes\Paginacion;
use Model\Subcategoria;
use Model\CategoriaAtributo;
use Model\SubcategoriaAtributo;

class CategoriasController {

    public static function index(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        // Paginación
        $pagina_actual = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/categorias?page=1');
        }

        $registros_por_pagina = 5;
        $total = Categoria::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/categorias?page=1');
        }

        // Obtener las categorías por paginación
        $categorias = Categoria::paginar($registros_por_pagina, $paginacion->offset());

        // Agrupar subcategorías por categoría
        $subcategoriasPorCategoria = [];
        $subcategorias = Subcategoria::all();
        foreach ($subcategorias as $subcategoria) {
            $subcategoriasPorCategoria[$subcategoria->categoriaId][] = $subcategoria;
        }

        $router->render('admin/categorias/index', [
            'titulo' => 'Categorias',
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'paginacion' => $paginacion->paginacion()
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }
    
        $categoria = new Categoria;
        $alertas = [];
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
            }
    
            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();
    
            if (empty($alertas)) {
                $resultado = $categoria->guardar();
    
                if ($resultado) {
                    // Si se han seleccionado atributos (array de IDs)
                    if (isset($_POST['atributos']) && !empty($_POST['atributos'])) {
                        foreach ($_POST['atributos'] as $atributoId) {
                            if (!empty($atributoId)) {
                                $catAtributo = new CategoriaAtributo([
                                    'categoriaId' => $categoria->id,
                                    'atributoId'  => $atributoId
                                ]);
                                $catAtributo->guardar();
                            }
                        }
                    }
                    header('Location: /admin/categorias');
                }
            }
        }
    
        // Se asume que el listado completo de atributos se obtiene así:
        $atributos = Atributo::all();
    
        $router->render('admin/categorias/crear', [
            'titulo' => 'Registrar Categoria',
            'alertas' => $alertas,
            'categoria' => $categoria,
            'atributos' => $atributos  // Lista de atributos para el select
        ], 'admin-layout');
    }


    public static function editar(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        $alertas = [];
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            header('Location: /admin/categorias');
        }

        $categoria = Categoria::find($id);
        if (!$categoria) {
            header('Location: /admin/categorias');
        }

        // Obtener IDs de atributos asociados previamente y cargar objetos para prellenar el formulario
        $oldAtributoIds = CategoriaAtributo::getAtributosPorCategoria($categoria->id);
        $atributosAsociados = [];
        foreach ($oldAtributoIds as $atributoId) {
            $atributosAsociados[] = Atributo::find($atributoId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
            }

            $categoria->sincronizar($_POST);
            $alertas = $categoria->validar();

            if (empty($alertas)) {
                $resultado = $categoria->guardar();

                if ($resultado) {
                    // Eliminar todas las asociaciones previas
                    CategoriaAtributo::eliminarPorCategoria($categoria->id);

                    // Procesar el array de atributos seleccionados
                    if (isset($_POST['atributos']) && !empty($_POST['atributos'])) {
                        foreach ($_POST['atributos'] as $atributoId) {
                            if (!empty($atributoId)) {
                                $catAtributo = new CategoriaAtributo([
                                    'categoriaId' => $categoria->id,
                                    'atributoId'  => $atributoId
                                ]);
                                $catAtributo->guardar();
                            }
                        }
                    }
                    header('Location: /admin/categorias');
                }
            }
        }

        // Obtener lista completa de atributos para el select
        $atributos = Atributo::all();

        $router->render('admin/categorias/editar', [
            'titulo'             => 'Actualizar Categoria',
            'alertas'            => $alertas,
            'categoria'          => $categoria,
            'atributosAsociados' => $atributosAsociados,  // Atributos actualmente asignados
            'atributos'          => $atributos           // Lista completa para el select
        ], 'admin-layout');
    }
    
    public static function eliminar(Router $router) {  
        if (!is_auth()) {
            header('Location: /login');
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
            }
            
            $id = $_POST['id'];
            $categoria = Categoria::find($id);
    
            if (!$categoria) {
                header('Location: /admin/categorias');
            }
            
            // Obtener subcategorías asociadas utilizando whereField (devuelve un array de objetos)
            $subcategorias = \Model\Subcategoria::whereField('categoriaId', $categoria->id);
            
            if (!empty($subcategorias)) {
                foreach ($subcategorias as $subcategoria) {
                    // Eliminar asociaciones de cada subcategoría
                    \Model\SubcategoriaAtributo::eliminarPorSubcategoria($subcategoria->id);
                    // Eliminar la subcategoría
                    $subcategoria->eliminar();
                }
            }
            
            // Eliminar las asociaciones de atributos asignados directamente a la categoría
            CategoriaAtributo::eliminarPorCategoria($categoria->id);
    
            // Eliminar la categoría
            $resultado = $categoria->eliminar();
    
            if ($resultado) {
                header('Location: /admin/categorias');
            }
        }
    }    
}