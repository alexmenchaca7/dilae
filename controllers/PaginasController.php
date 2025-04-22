<?php

namespace Controllers;
use MVC\Router;
use Model\Atributo;
use Model\Producto;
use Model\Categoria;
use Classes\Paginacion;
use Model\Subcategoria;
use Model\FichaProducto;
use Model\ImagenProducto;
use Model\ProductoAtributo;

class PaginasController {
    public static function index(Router $router) {

        $inicio = true;

        $router->render('paginas/index', [
            'inicio' => $inicio
        ]);
    }

    public static function productos(Router $router, $categoria_slug = null, $subcategoria_slug = null) {
        $categorias = Categoria::ordenar('posicion', 'ASC');

        foreach ($categorias as $categoria) {
            $categoria->subcategorias = Subcategoria::metodoSQL([
                'condiciones' => ["categoriaId = '{$categoria->id}'"],
                'orden' => 'posicion ASC'
            ]);
        }

        $condiciones = [];
        $categoriaObj = null;
        $subcategoriaObj = null;

        // Filtrar por slugs
        if ($categoria_slug) {
            $categoriaObj = Categoria::where('slug', $categoria_slug);
            if ($categoriaObj) {
                $condiciones[] = "categoriaId = '{$categoriaObj->id}'";
                
                if ($subcategoria_slug) {
                    $subcategoriaObj = Subcategoria::where('slug', $subcategoria_slug);
                    if ($subcategoriaObj && $subcategoriaObj->categoriaId == $categoriaObj->id) {
                        $condiciones[] = "subcategoriaId = '{$subcategoriaObj->id}'";
                    }
                }
            }
        }

        // Obtener atributos numéricos
        $numericAttributes = [];
        if ($categoriaObj) {
            $sql = "SELECT DISTINCT a.* 
                    FROM atributos a
                    INNER JOIN productos_atributos pa ON a.id = pa.atributoId
                    INNER JOIN productos p ON pa.productoId = p.id
                    WHERE a.tipo = 'numero'";
            if ($categoriaObj) {
                $sql .= " AND p.categoriaId = '{$categoriaObj->id}'";
                if ($subcategoriaObj) {
                    $sql .= " AND p.subcategoriaId = '{$subcategoriaObj->id}'";
                }
            }
            $numericAttributes = Atributo::consultarSQL($sql) ?: [];
        }

        // Antes del procesamiento de filtros:
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'min_') === 0 || strpos($key, 'max_') === 0) {
                $_GET[$key] = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                if ($_GET[$key] === '') {
                    unset($_GET[$key]); // Eliminar parámetros vacíos
                }
            }
        }

        // Procesar filtros numéricos
        foreach ($numericAttributes as $attr) {
            $minParam = 'min_' . $attr->id;
            $maxParam = 'max_' . $attr->id;
            $min = isset($_GET[$minParam]) ? (float)$_GET[$minParam] : null;
            $max = isset($_GET[$maxParam]) ? (float)$_GET[$maxParam] : null;
        
            if ($min !== null || $max !== null) {
                $cond = "EXISTS (SELECT 1 FROM productos_atributos WHERE productoId = productos.id AND atributoId = '{$attr->id}' ";
                $conditions = [];
                if ($min !== null) {
                    $conditions[] = "valor_numero >= {$min}";
                }
                if ($max !== null) {
                    $conditions[] = "valor_numero <= {$max}";
                }
                if (!empty($conditions)) {
                    $cond .= " AND " . implode(' AND ', $conditions);
                }

                $cond .= ")";
                $condiciones[] = $cond;
            }
        }       

        // Procesar ordenamiento
        $orden = $_GET['orden'] ?? 'nombre_asc';
        switch ($orden) {
            case 'nombre_asc':
                $ordenSQL = 'nombre ASC';
                break;
            case 'nombre_desc':
                $ordenSQL = 'nombre DESC';
                break;
            default:
                $ordenSQL = 'nombre ASC';
        }

        // Búsqueda
        $busqueda = $_GET['busqueda'] ?? '';
        if($busqueda) {
            $condiciones = array_merge($condiciones, Producto::buscar($busqueda));
        }

        // Paginación
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        if($pagina_actual < 1) {
            header('Location: /productos?page=1');
            exit();
        }

        $registros_por_pagina = 12;
        $total = Producto::totalCondiciones($condiciones);
        
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1) {
            header('Location: /productos?page=1');
            exit();
        }

        // Obtener productos con paginación
        $productos = Producto::metodoSQL([
            'condiciones' => $condiciones,
            'orden' => $ordenSQL,
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset()
        ]);

        foreach ($productos as $producto) {
            $producto->imagen_principal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->atributos = self::obtenerAtributosPrincipales($producto->id);
            $producto->categoria = $producto->categoria();
            $producto->subcategoria = $producto->subcategoria();
        }

        $titulo = 'Productos';
        if ($subcategoriaObj) {
            $titulo = $subcategoriaObj->nombre;
        } elseif ($categoriaObj) {
            $titulo = $categoriaObj->nombre;
        }

        $router->render('paginas/productos', [
            'titulo' => $titulo,
            'categorias' => $categorias,
            'productos' => $productos,
            'categoria_slug' => $categoria_slug,
            'subcategoria_slug' => $subcategoria_slug,
            'busqueda' => $busqueda,
            'numericAttributes' => $numericAttributes,
            'paginacion' => $paginacion,
            'categoria_slug' => $categoria_slug,
            'subcategoria_slug' => $subcategoria_slug
        ]);
    }

    public static function producto(Router $router, $categoria_slug = null, $subcategoria_slug = null, $producto_slug = null) {
        if (!$producto_slug || !$categoria_slug) {
            header('Location: /productos');
            exit;
        }

        $producto = Producto::where('slug', $producto_slug);
        if (!$producto) {
            header('Location: /productos');
            exit;
        }

        // Validar jerarquía de slugs
        $categoria = $producto->categoria();
        $subcategoria = $producto->subcategoria();
        
        if ($categoria->slug !== $categoria_slug || 
            ($subcategoria && $subcategoria->slug !== $subcategoria_slug)) {
            header("Location: /productos/{$categoria->slug}/{$subcategoria->slug}/{$producto->slug}");
            exit;
        }

        // Obtener datos
        $categorias = Categoria::ordenar('posicion', 'ASC');
        foreach ($categorias as $cat) {
            $cat->subcategorias = Subcategoria::metodoSQL([
                'condiciones' => ["categoriaId = '{$cat->id}'"],
                'orden' => 'posicion ASC'
            ]);
        }

        $producto->imagenes = ImagenProducto::whereField('productoId', $producto->id);
        $producto->atributos = self::obtenerAtributosDetallados($producto->id);
        $producto->fichas = FichaProducto::whereField('productoId', $producto->id);

        $router->render('paginas/producto', [
            'titulo' => $producto->nombre,
            'producto' => $producto,
            'categorias' => $categorias,
            'categoriaId' => $producto->categoriaId,
            'subcategoriaId' => $producto->subcategoriaId,
            'categoria_slug' => $categoria_slug,  
            'subcategoria_slug' => $subcategoria_slug  
        ]);
    }

    public static function nosotros(Router $router) {

        $titulo = 'Nosotros';

        $router->render('paginas/nosotros', [
            'titulo' => $titulo,
        ]);
    }

    public static function proyectos(Router $router) {

        $titulo = 'Proyectos';

        $router->render('paginas/proyectos', [
            'titulo' => $titulo
        ]);
    }

    public static function blog(Router $router) {

        $titulo = 'Blog';

        $router->render('paginas/blog', [
            'titulo' => $titulo
        ]);
    }

    public static function contacto(Router $router) {

        $titulo = 'Contacto';

        $router->render('paginas/contacto', [
            'titulo' => $titulo
        ]);
    }


    private static function obtenerAtributosPrincipales($productoId) {
        $atributos = [];
        $productoAtributos = ProductoAtributo::whereField('productoId', $productoId);
        
        foreach($productoAtributos as $pa) {
            $atributo = Atributo::find($pa->atributoId);
            if(!$atributo) continue;
            
            if(!isset($atributos[$atributo->nombre])) {
                $atributos[$atributo->nombre] = [
                    'tipo' => $atributo->tipo, 
                    'unidad' => $atributo->unidad,
                    'espacio_unidad' => $atributo->espacio_unidad,
                    'valores' => []
                ];
            }
            
            $valor = !empty($pa->valor_texto) ? $pa->valor_texto : $pa->valor_numero;
            $atributos[$atributo->nombre]['valores'][] = $valor;
        }
        
        return $atributos;
    }

    private static function obtenerAtributosDetallados($productoId) {
        $atributos = [];
        $productoAtributos = ProductoAtributo::whereField('productoId', $productoId);
        
        foreach($productoAtributos as $pa) {
            $atributo = Atributo::find($pa->atributoId);
            if(!$atributo) continue;
            
            if(!isset($atributos[$atributo->nombre])) {
                $atributos[$atributo->nombre] = [
                    'tipo' => $atributo->tipo, 
                    'unidad' => $atributo->unidad,
                    'espacio_unidad' => $atributo->espacio_unidad,
                    'valores' => []
                ];
            }
            
            $valor = !empty($pa->valor_texto) ? $pa->valor_texto : $pa->valor_numero;
            $atributos[$atributo->nombre]['valores'][] = $valor;
        }
        
        return $atributos;
    }
}