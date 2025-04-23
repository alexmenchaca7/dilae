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

        // Busqueda
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        
        // Validar página
        if($pagina_actual < 1) {
            header('Location: /admin/categorias?page=1');
            exit();
        }

        // Configuración paginación
        $registros_por_pagina = 5;
        $condiciones = [];

        // Usar método del modelo para buscar
        if(!empty($busqueda)) {
            $busqueda = trim($busqueda);
            $condiciones = Categoria::buscar($busqueda);
            
            if(empty($condiciones)) {
                $condiciones[] = "1 = 0"; // Forzar resultado vacío si no hay término válido
            }
        }

        // Obtener total de registros
        $total = Categoria::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        // Validar paginas totales
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/categorias?page=1');
            exit();
        }

        // Obtener registros
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'posicion ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset(),
        ];
        
        $categorias = Categoria::metodoSQL($params);

        // Obtener subcategorías agrupadas
        $subcategorias = Subcategoria::ordenar('posicion', 'ASC');
        foreach ($subcategorias as $subcategoria) {
            $subcategoriasPorCategoria[$subcategoria->categoriaId][] = $subcategoria;
        }

        // Renderizar vista
        $router->render('admin/categorias/index', [
            'titulo' => 'Categorias',
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function moverArriba(Router $router) {
        if (!is_auth()) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $categoriaActual = Categoria::find($id);
            
            if ($categoriaActual) {
                // Buscar la categoría que está arriba
                $categoriaAnterior = Categoria::where('posicion', $categoriaActual->posicion - 1);
                
                if ($categoriaAnterior) {
                    // Intercambiar posiciones
                    $categoriaActual->posicion--;
                    $categoriaAnterior->posicion++;
                    
                    $categoriaActual->guardar();
                    $categoriaAnterior->guardar();
                }
            }
            exit;
        }
    }
    
    public static function moverAbajo(Router $router) {
        if (!is_auth()) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $categoriaActual = Categoria::find($id);
            
            if ($categoriaActual) {
                // Buscar la categoría que está abajo
                $categoriaSiguiente = Categoria::where('posicion', $categoriaActual->posicion + 1);
                
                if ($categoriaSiguiente) {
                    // Intercambiar posiciones
                    $categoriaActual->posicion++;
                    $categoriaSiguiente->posicion--;
                    
                    $categoriaActual->guardar();
                    $categoriaSiguiente->guardar();
                }
            }
            exit;
        }
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
                // Obtener la máxima posición actual
                $maxPosicion = (int)Categoria::max('posicion');
                $categoria->posicion = $maxPosicion + 1;
                
                $resultado = $categoria->guardar();
    
                if ($resultado) {
                    // Si se han seleccionado atributos (array de IDs)
                    if (isset($_POST['atributos']) && !empty($_POST['atributos'])) {
                        foreach ($_POST['atributos'] as $index => $atributoId) { // Agregar índice
                            if (!empty($atributoId)) {
                                $catAtributo = new CategoriaAtributo([
                                    'categoriaId' => $categoria->id,
                                    'atributoId'  => $atributoId,
                                    'posicion'    => $index + 1 // Nueva posición
                                ]);
                                $catAtributo->guardar();
                            }
                        }
                        // Normalizar posiciones
                        CategoriaAtributo::normalizarPosiciones($categoria->id);
                    }
                    header('Location: /admin/categorias');
                }
            }
        }
    
        // Se asume que el listado completo de atributos se obtiene así:
        $atributos = Atributo::all();
        $atributosDisponibles = $atributos; // Todos están disponibles al crear
    
        $router->render('admin/categorias/crear', [
            'titulo' => 'Registrar Categoria',
            'alertas' => $alertas,
            'categoria' => $categoria,
            'atributos' => $atributos,
            'atributosDisponibles' => $atributosDisponibles
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
                        foreach ($_POST['atributos'] as $index => $atributoId) { // Agregar índice
                            if (!empty($atributoId)) {
                                $catAtributo = new CategoriaAtributo([
                                    'categoriaId' => $categoria->id,
                                    'atributoId'  => $atributoId,
                                    'posicion'    => $index + 1 // Nueva posición
                                ]);
                                $catAtributo->guardar();
                            }
                        }
                        // Normalizar posiciones
                        CategoriaAtributo::normalizarPosiciones($categoria->id);
                    }
                    header('Location: /admin/categorias');
                }
            }
        }

        // Obtener lista completa de atributos para el select
        $atributos = Atributo::all();
        $atributosDisponibles = array_filter($atributos, function($atributo) use ($atributosAsociados) {
            return !in_array($atributo->id, array_column($atributosAsociados, 'id'));
        });

        $router->render('admin/categorias/editar', [
            'titulo'             => 'Actualizar Categoria',
            'alertas'            => $alertas,
            'categoria'          => $categoria,
            'atributosAsociados' => $atributosAsociados,  // Atributos actualmente asignados
            'atributos'          => $atributos,           // Lista completa para el select
            'atributosDisponibles' => $atributosDisponibles
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