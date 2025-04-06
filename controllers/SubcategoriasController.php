<?php

namespace Controllers;

use MVC\Router;
use Model\Atributo;
use Model\Categoria;
use Model\Subcategoria;
use Model\SubcategoriaAtributo;

class SubcategoriasController {
    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }
    
        $subcategoria = new Subcategoria;
        $alertas = [];
    
        // Obtener todas las categorías para el select de categoría principal
        $categorias = Categoria::all();
        // Obtener todos los atributos existentes para asignarlos
        $atributos = Atributo::all();
    
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }
    
            $subcategoria->sincronizar($_POST);
            $alertas = $subcategoria->validar();
    
            if(empty($alertas)) {
                $resultado = $subcategoria->guardar();
    
                if($resultado) {
                    // Procesar el array de atributos seleccionados
                    if(isset($_POST['atributos']) && !empty($_POST['atributos'])) {
                        foreach($_POST['atributos'] as $atributoId) {
                            if(!empty($atributoId)) {
                                $subCatAtributo = new SubcategoriaAtributo([
                                    'subcategoriaId' => $subcategoria->id,
                                    'atributoId'     => $atributoId
                                ]);
                                $subCatAtributo->guardar();
                            }
                        }
                    }
                    header('Location: /admin/categorias');
                }
            }
        }

        // Obtener atributos disponibles
        $atributosDisponibles = $atributos; // Todos disponibles al crear
    
        $router->render('admin/subcategorias/crear', [
            'titulo'      => 'Registrar Subcategoría',
            'alertas'     => $alertas,
            'subcategoria'=> $subcategoria,
            'categorias'  => $categorias,
            'atributos'   => $atributos,
            'atributosDisponibles' => $atributosDisponibles
        ], 'admin-layout');
    }    

    public static function editar(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }
    
        $alertas = [];
        
        // Validar ID de la subcategoría a editar
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        if(!$id) {
            header('Location: /admin/categorias');
        }
    
        // Obtener la subcategoría a editar
        $subcategoria = Subcategoria::find($id);
        if(!$subcategoria) {
            header('Location: /admin/categorias');
        }
    
        // Obtener todas las categorías
        $categorias = Categoria::all();
        // Obtener todos los atributos existentes (para el select)
        $atributos = \Model\Atributo::all();
    
        // Obtener IDs de atributos asociados previamente
        $oldAtributoIds = \Model\SubcategoriaAtributo::getAtributosPorSubcategoria($subcategoria->id);
        // Cargar objetos Atributo asociados para prellenar el formulario
        $atributosAsociados = [];
        foreach ($oldAtributoIds as $atributoId) {
            $atributosAsociados[] = \Model\Atributo::find($atributoId);
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }
    
            $subcategoria->sincronizar($_POST);
            $alertas = $subcategoria->validar();
    
            if(empty($alertas)) {
                $resultado = $subcategoria->guardar();
    
                if($resultado) {
                    // Eliminar las asociaciones previas
                    \Model\SubcategoriaAtributo::eliminarPorSubcategoria($subcategoria->id);
    
                    // Procesar el array de atributos seleccionados
                    if(isset($_POST['atributos']) && !empty($_POST['atributos'])) {
                        foreach($_POST['atributos'] as $atributoId) {
                            if(!empty($atributoId)) {
                                $subCatAtributo = new \Model\SubcategoriaAtributo([
                                    'subcategoriaId' => $subcategoria->id,
                                    'atributoId'     => $atributoId
                                ]);
                                $subCatAtributo->guardar();
                            }
                        }
                    }
                    header('Location: /admin/categorias');
                }
            }
        }

        // Obtener atributos disponibles
        $atributosDisponibles = array_filter($atributos, function($atributo) use ($atributosAsociados) {
            return !in_array($atributo->id, array_column($atributosAsociados, 'id'));
        });
        
        $router->render('admin/subcategorias/editar', [
            'titulo'             => 'Actualizar Subcategoría',
            'alertas'            => $alertas,
            'subcategoria'       => $subcategoria,
            'categorias'         => $categorias,
            'atributosAsociados' => $atributosAsociados,  // Atributos ya asignados
            'atributos'          => $atributos,           // Lista completa para el select
            'atributosDisponibles' => $atributosDisponibles
        ], 'admin-layout');
    }    

    public static function eliminar() {
        if (!is_auth()) {
            header('Location: /login');
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
            }
            
            $id = $_POST['id'];
            $subcategoria = Subcategoria::find($id);
    
            // Validar que la subcategoría exista
            if (!$subcategoria) {
                header('Location: /admin/categorias');
            }
    
            // Primero, eliminar las asociaciones en la tabla subcategoria_atributos
            SubcategoriaAtributo::eliminarPorSubcategoria($subcategoria->id);
    
            // Luego, eliminar la subcategoría
            $resultado = $subcategoria->eliminar();
    
            if ($resultado) {
                header('Location: /admin/categorias');
            }
        }
    }    
}