<?php

namespace Controllers;
use MVC\Router;
use Model\Atributo;
use Model\Producto;
use Model\Categoria;
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

        // Obtener atributos numéricos de la categoría/subcategoría actual
        $numericAttributes = [];
        if ($categoriaObj) {
            $sql = "SELECT DISTINCT a.* 
                    FROM atributos a
                    INNER JOIN productos_atributos pa ON a.id = pa.atributoId
                    INNER JOIN productos p ON pa.productoId = p.id
                    WHERE p.categoriaId = '{$categoriaObj->id}' ";
            if ($subcategoriaObj) {
                $sql .= " AND p.subcategoriaId = '{$subcategoriaObj->id}' ";
            }
            $sql .= " AND a.tipo = 'numero'";
            $numericAttributes = Atributo::consultarSQL($sql) ?: [];
        }

        // Procesar filtros numéricos
        foreach ($numericAttributes as $attr) {
            $minParam = 'min_' . $attr->id;
            $maxParam = 'max_' . $attr->id;
            $min = isset($_GET[$minParam]) ? (float)$_GET[$minParam] : null;
            $max = isset($_GET[$maxParam]) ? (float)$_GET[$maxParam] : null;

            if ($min !== null || $max !== null) {
                $cond = "EXISTS (SELECT 1 FROM producto_atributo WHERE productoId = productos.id AND atributoId = '{$attr->id}' ";
                $conditions = [];
                if ($min !== null) {
                    $conditions[] = "valor_numero >= {$min}";
                }
                if ($max !== null) {
                    $conditions[] = "valor_numero <= {$max}";
                }
                $cond .= " AND " . implode(' AND ', $conditions) . ")";
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

        // Parámetro de búsqueda
        $busqueda = $_GET['busqueda'] ?? '';
        if($busqueda) {
            $condiciones = array_merge($condiciones, Producto::buscar($busqueda));
        }

        $productos = Producto::metodoSQL([
            'condiciones' => $condiciones,
            'orden' => 'nombre ASC'
        ]);

        foreach ($productos as $producto) {
            $producto->imagen_principal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id);
            $producto->atributos = self::obtenerAtributosPrincipales($producto->id);
            $producto->categoria = $producto->categoria();
            $producto->subcategoria = $producto->subcategoria();
        }

        // Construir título dinámico
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
                    'valores' => [],
                    'unidad' => $atributo->unidad
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
                    'valores' => []
                ];
            }
            
            $valor = !empty($pa->valor_texto) ? $pa->valor_texto : $pa->valor_numero;
            $atributos[$atributo->nombre]['valores'][] = $valor;
        }
        
        return $atributos;
    }
}