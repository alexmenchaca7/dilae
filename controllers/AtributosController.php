<?php

namespace Controllers;

use MVC\Router;
use Model\Atributo;
use Classes\Paginacion;
use Model\ProductoAtributo;
use Model\CategoriaAtributo;
use Model\SubcategoriaAtributo;

class AtributosController {

    public static function index(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        // Busqueda
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        
        // Validar página
        if($pagina_actual < 1) {
            header('Location: /admin/atributos?page=1');
            exit();
        }

        // Configuración paginación
        $registros_por_pagina = 10;
        $condiciones = [];

        // Usar método del modelo para buscar
        if(!empty($busqueda)) {
            $condiciones = Atributo::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Atributo::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        // Validar paginas totales
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /admin/atributos?page=1');
            exit();
        }

        // Obtener registros
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset(),
        ];

        $atributos = Atributo::metodoSQL($params);

        // Renderizar vista
        $router->render('admin/atributos/index', [
            'titulo' => 'Atributos',
            'atributos' => $atributos,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }


    public static function crear(Router $router) {
        if (!is_auth()) {
            header('Location: /login');
        }

        $atributo = new Atributo;
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Convertir checkbox a booleano
            $_POST['espacio_unidad'] = isset($_POST['espacio_unidad']) ? 1 : 0;

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
            // Convertir checkbox a booleano
            $_POST['espacio_unidad'] = isset($_POST['espacio_unidad']) ? 1 : 0;
            
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
            if (!is_auth()) {
                header('Location: /login');
            }

            $id = $_POST['id'];
            $atributo = Atributo::find($id);

            if (!$atributo) {
                header('Location: /admin/atributos');
            }

            // Eliminar relaciones en todas las tablas usando whereField
            $relaciones = CategoriaAtributo::whereField('atributoId', $id);
            foreach($relaciones as $relacion) {
                $relacion->eliminar();
            }

            $relacionesSub = SubcategoriaAtributo::whereField('atributoId', $id);
            foreach($relacionesSub as $relacion) {
                $relacion->eliminar();
            }

            $relacionesProd = ProductoAtributo::whereField('atributoId', $id);
            foreach($relacionesProd as $relacion) {
                $relacion->eliminar();
            }

            // Eliminar el atributo
            $resultado = $atributo->eliminar();


            if ($resultado) {
                header('Location: /admin/atributos');
            }
        }
    }
}
