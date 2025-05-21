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
use Model\CategoriaAtributo;
use Model\SubcategoriaAtributo;

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

        // Guardar GET original antes de modificaciones
        $originalGet = $_GET;

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
            // Obtener atributos ordenados según posición en categoría/subcategoría
            $producto->atributos = self::obtenerAtributosPrincipales(
                $producto->id,
                $categoriaObj ? $categoriaObj->id : null,
                $subcategoriaObj ? $subcategoriaObj->id : null
            );
            $producto->categoria = $producto->categoria();
            $producto->subcategoria = $producto->subcategoria();
        }

        $titulo = 'Productos';
        if ($subcategoriaObj) {
            $titulo = $subcategoriaObj->nombre;
        } elseif ($categoriaObj) {
            $titulo = $categoriaObj->nombre;
        }

        // Redirección para limpiar URL si hubo cambios
        if ($_GET != $originalGet) {
            $path = '/productos';
            if ($categoria_slug) {
                $path .= '/' . $categoria_slug;
                if ($subcategoria_slug) {
                    $path .= '/' . $subcategoria_slug;
                }
            }
            $query = http_build_query($_GET);
            $redirectUrl = $path . ($query ? '?'.$query : '');
            header("Location: $redirectUrl");
            exit;
        }

        // Calcular URL limpia para el botón
        $cleanParams = array_filter($_GET, function($key) {
            return strpos($key, 'min_') !== 0 && strpos($key, 'max_') !== 0;
        }, ARRAY_FILTER_USE_KEY);
        
        $cleanPath = '/productos';
        if ($categoria_slug) {
            $cleanPath .= '/' . $categoria_slug;
            if ($subcategoria_slug) {
                $cleanPath .= '/' . $subcategoria_slug;
            }
        }
        $cleanQuery = http_build_query($cleanParams);
        $cleanUrl = $cleanPath . ($cleanQuery ? '?'.$cleanQuery : '');

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Es una petición AJAX
            ob_start();
            // Incluir un "partial" para la lista de productos
            include __DIR__ . '/../views/paginas/_lista-productos.php';
            $productos_html = ob_get_clean();
        
            ob_start();
            // Incluir un "partial" para la paginación
            include __DIR__ . '/../views/paginas/_paginacion.php';
            $paginacion_html = ob_get_clean();
        
            header('Content-Type: application/json');
            echo json_encode([
                'productos_html' => $productos_html,
                'paginacion_html' => $paginacion_html,
                'titulo' => $titulo // Opcional, si quieres actualizar el título de la página dinámicamente
            ]);
            exit; // Importante para no renderizar el resto de la plantilla
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
            'subcategoria_slug' => $subcategoria_slug,
            'cleanUrl' => $cleanUrl
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

        $producto->imagenes = ImagenProducto::whereArray(
            ['productoId' => $producto->id], 
            'posicion ASC'  // <- Ordenar por posición
        );
        
        // Atributos ordenados en detalle
        $producto->atributos = self::obtenerAtributosDetallados(
            $producto->id,
            $producto->categoriaId,
            $producto->subcategoriaId
        );
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


    private static function obtenerAtributosPrincipales($productoId, $categoriaId = null, $subcategoriaId = null) {
        $raw = [];
        $productoAtributos = ProductoAtributo::whereField('productoId', $productoId);
        foreach ($productoAtributos as $pa) {
            $atributo = Atributo::find($pa->atributoId);
            if (!$atributo) continue;
            $id = $atributo->id;
            if (!isset($raw[$id])) {
                $raw[$id] = [
                    'nombre' => $atributo->nombre,
                    'tipo' => $atributo->tipo,
                    'unidad' => $atributo->unidad,
                    'espacio_unidad' => $atributo->espacio_unidad,
                    'valores' => []
                ];
            }
            $valor = !empty($pa->valor_texto) ? $pa->valor_texto : $pa->valor_numero;
            $raw[$id]['valores'][] = $valor;
        }

        // Determinar orden según subcategoría o categoría
        if ($subcategoriaId) {
            $ordenIds = SubcategoriaAtributo::getAtributosPorSubcategoria($subcategoriaId);
        } elseif ($categoriaId) {
            $ordenIds = CategoriaAtributo::getAtributosPorCategoria($categoriaId);
        } else {
            $ordenIds = array_keys($raw);
        }

        // Armar arreglo ordenado
        $atributosOrdenados = [];
        foreach ($ordenIds as $id) {
            if (isset($raw[$id])) {
                $nombre = $raw[$id]['nombre'];
                $atributosOrdenados[$nombre] = $raw[$id];
            }
        }
        // Agregar atributos sin posición definida al final
        foreach ($raw as $id => $data) {
            if (!in_array($id, $ordenIds)) {
                $atributosOrdenados[$data['nombre']] = $data;
            }
        }

        return $atributosOrdenados;
    }

    private static function obtenerAtributosDetallados($productoId, $categoriaId = null, $subcategoriaId = null) {
        // Lógica idéntica a obtenerAtributosPrincipales
        return self::obtenerAtributosPrincipales($productoId, $categoriaId, $subcategoriaId);
    }
}