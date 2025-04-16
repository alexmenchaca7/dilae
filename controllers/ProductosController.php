<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Atributo;
use Model\Producto;
use Model\Categoria;
use Classes\Paginacion;
use Model\Subcategoria;
use Model\ImagenProducto;
use Model\ProductoAtributo;
use Model\CategoriaAtributo;
use Model\SubcategoriaAtributo;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Model\FichaProducto; // Asegúrate de incluir el modelo de FichaProducto

class ProductosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        // Busqueda
        $busqueda = $_GET['busqueda'] ?? '';
        $pagina_actual = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        
        // Validar página
        if($pagina_actual < 1) {
            header('Location: /admin/productos?page=1');
            exit();
        }

        // Configuración paginación
        $registros_por_pagina = 10;
        $condiciones = [];

        // Buscar productos
        if(!empty($busqueda)) {
            $condiciones = Producto::buscar($busqueda);
        }

        // Obtener total de registros
        $total = Producto::totalCondiciones($condiciones);

        // Crear instancia de paginación
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);
        
        // Validar páginas totales
        if ($paginacion->total_paginas() < $pagina_actual && $pagina_actual > 1 && $total > 0) { // Añadir $total > 0
            header('Location: /admin/productos?page=1');
            exit();
        }

        // Obtener registros con relaciones
        $params = [
            'condiciones' => $condiciones,
            'orden' => 'id DESC',
            'limite' => $registros_por_pagina,
            'offset' => $paginacion->offset(),
        ];
        
        $productos = Producto::metodoSQL($params);

        // --- AÑADIR IMAGEN PRINCIPAL ---
        foreach ($productos as $producto) {
            // Llama al método estático que creaste en ImagenProducto
            $imagenPrincipal = ImagenProducto::obtenerPrincipalPorProductoId($producto->id); 
            
            // Asigna la URL a la propiedad 'imagen_principal' del objeto Producto
            // Si no hay imagen, asigna null
            $producto->imagen_principal = $imagenPrincipal ? $imagenPrincipal->url : null; 
        }

        // Obtener categorías y subcategorías para mostrar nombres
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $categoriasMap = [];
        $subcategoriasMap = [];
        
        foreach($categorias as $categoria) {
            $categoriasMap[$categoria->id] = $categoria;
        }
        foreach($subcategorias as $subcategoria) {
            $subcategoriasMap[$subcategoria->id] = $subcategoria;
        }

        $router->render('admin/productos/index', [
            'titulo' => 'Productos',
            'productos' => $productos,
            'categorias' => $categoriasMap,
            'subcategorias' => $subcategoriasMap,
            'paginacion' => $paginacion->paginacion(),
            'busqueda' => $busqueda
        ], 'admin-layout');
    }

    public static function verificarFicha() {
        if(!is_auth()) {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    
        $nombre = $_GET['nombre'] ?? '';
        $nombreSanitizado = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $nombre);
        $existe = FichaProducto::where('url', $nombreSanitizado);
        
        header('Content-Type: application/json');
        echo json_encode(['existe' => !empty($existe)]);
        exit;
    }    

    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $imagenes = [];
        $fichas = [];
        $producto = new Producto;
        $alertas = [];
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $subcategoriasPorCategoria = [];
        
        // Agrupar subcategorías por categoría
        foreach ($subcategorias as $subcategoria) {
            $subcategoriasPorCategoria[$subcategoria->categoriaId][] = $subcategoria;
        }
    
        // Obtener todos los atributos disponibles
        $todosAtributos = Atributo::all();
    
        // Precargar relaciones de atributos
        $relacionesAtributos = [
            'categorias' => [],
            'subcategorias' => []
        ];
    
        // Cargar relaciones categoría-atributo
        $categoriasAtributos = CategoriaAtributo::all();
        foreach ($categoriasAtributos as $ca) {
            if(!isset($relacionesAtributos['categorias'][$ca->categoriaId])) {
                $relacionesAtributos['categorias'][$ca->categoriaId] = [];
            }
            $relacionesAtributos['categorias'][$ca->categoriaId][] = $ca->atributoId;
        }
    
        // Cargar relaciones subcategoría-atributo
        $subcategoriasAtributos = SubcategoriaAtributo::all();
        foreach ($subcategoriasAtributos as $sa) {
            if(!isset($relacionesAtributos['subcategorias'][$sa->subcategoriaId])) {
                $relacionesAtributos['subcategorias'][$sa->subcategoriaId] = [];
            }
            $relacionesAtributos['subcategorias'][$sa->subcategoriaId][] = $sa->atributoId;
        }
    
        // Atributos disponibles inicialmente
        $atributosDisponibles = [];
        if($producto->categoriaId) {
            $atributosDisponibles = self::obtenerAtributosDisponibles(
                $producto->categoriaId, 
                $producto->subcategoriaId ?? null
            );
        }
    
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }
    
            $producto->sincronizar($_POST);
            $alertas = $producto->validar();
    
            // Validación de subcategoría
            if($producto->categoriaId) {
                $tieneSubcategorias = !empty($subcategoriasPorCategoria[$producto->categoriaId]);
                
                if($tieneSubcategorias && empty($producto->subcategoriaId)) {
                    $alertas['error'][] = 'Debes seleccionar una subcategoría para esta categoría';
                }
                
                if($producto->subcategoriaId) {
                    $subcategoriaValida = false;
                    foreach($subcategoriasPorCategoria[$producto->categoriaId] ?? [] as $subcat) {
                        if($subcat->id == $producto->subcategoriaId) {
                            $subcategoriaValida = true;
                            break;
                        }
                    }
                    
                    if(!$subcategoriaValida) {
                        $alertas['error'][] = 'La subcategoría seleccionada no pertenece a esta categoría';
                    }
                }
            }
    
            // Validar atributos
            if(isset($_POST['atributos']) && is_array($_POST['atributos'])) {
                $atributosPermitidos = self::obtenerAtributosDisponibles(
                    $producto->categoriaId, 
                    $producto->subcategoriaId ?? null
                );
    
                $atributosPermitidosIds = array_map(function($atributo) {
                    return (int)$atributo->id;
                }, $atributosPermitidos);
    
                foreach($_POST['atributos'] as $atributoId => $valores) {
                    $atributoIdInt = (int)$atributoId;
                    $atributo = Atributo::find($atributoIdInt);
    
                    // Verificar que el atributo esté permitido
                    if(!in_array($atributoIdInt, $atributosPermitidosIds)) {
                        $alertas['error'][] = 'El atributo "' . ($atributo ? $atributo->nombre : $atributoIdInt) . 
                            '" no pertenece a la categoría/subcategoría seleccionada';
                        continue;
                    }
    
                    // Filtrar y validar valores
                    $valores = array_filter((array)$valores, function($v) { 
                        return !empty(trim($v)); 
                    });
    
                    if(empty($valores)) {
                        $atributo = Atributo::find($atributoIdInt);
                        if ($atributo) { // Solo si el atributo es válido
                            $alertas['error'][] = "El atributo " . htmlspecialchars($atributo->nombre) . " requiere al menos un valor válido si se incluye.";
                        }
                        continue;
                    }
    
                    foreach($valores as $valor) {
                        $atributo = Atributo::find($atributoIdInt);
                        if(!$atributo) continue; // Saltar si el ID no es válido

                        $alertasAtributo = $atributo->validarValor($valor);
                        
                        if(!empty($alertasAtributo['error'])) {
                            $alertas['error'] = array_merge($alertas['error'] ?? [], $alertasAtributo['error']);
                        }
                    }
                }
            }
    
            // Validar imágenes
            $imagenes = [];
            if (!empty($_FILES['nuevas_imagenes']['name'][0])) {
                foreach ($_FILES['nuevas_imagenes']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['nuevas_imagenes']['error'][$index] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                        $imagenes[] = [
                            'tmp' => $tmpName,
                            'name' => $_FILES['nuevas_imagenes']['name'][$index]
                        ];
                    }
                }
            }
    
            if(count($imagenes) === 0 && empty($_POST['imagenes_existentes'])) { // Modificado: Solo requerir si no hay existentes
                $alertas['error'][] = 'Debes subir o mantener al menos una imagen del producto';
            } 
            // Calcular total de imágenes (existentes no eliminadas + nuevas)
            $existentes_no_eliminadas = count(array_diff($_POST['imagenes_existentes'] ?? [], $_POST['imagenes_eliminadas'] ?? []));
            if (($existentes_no_eliminadas + count($imagenes)) > 15) {
                $alertas['error'][] = 'Máximo 15 imágenes permitidas por producto';
            }

            // Validar fichas técnicas
            $fichas = [];
            $nombresFichas = [];
            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'nuevas_fichas') === 0 && !empty($file['name'][0])) { 
                    foreach($file['tmp_name'] as $index => $tmp_name) {
                        if ($file['error'][$index] === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                            $nombreOriginal = basename($file['name'][$index]);
                            $nombreSanitizado = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $nombreOriginal);
                            
                            // Verificar duplicados en esta misma subida
                            if(in_array($nombreSanitizado, $nombresFichas)) {
                                $alertas['error'][] = "La ficha '$nombreSanitizado' está duplicada en los archivos seleccionados";
                                continue;
                            }
                            
                            // Verificar en base de datos
                            $existeFicha = FichaProducto::where('url', $nombreSanitizado);
                            if (!empty($existeFicha)) {
                                $alertas['error'][] = "La ficha '$nombreSanitizado' ya existe en el sistema";
                            }

                            $fichas[] = [
                                'tmp' => $tmp_name,
                                'name' => $nombreSanitizado
                            ];
                            $nombresFichas[] = $nombreSanitizado;
                        }
                    }
                }
            }

            // Calcular total de fichas (existentes no eliminadas + nuevas)
            $fichas_existentes_no_eliminadas = count(array_diff($_POST['fichas_existentes'] ?? [], $_POST['fichas_eliminadas'] ?? []));
             if (($fichas_existentes_no_eliminadas + count($fichas)) > 15) {
                $alertas['error'][] = 'Máximo 15 fichas técnicas permitidas por producto';
            }
    
            if(empty($alertas['error'])) {
                $resultado = $producto->guardar();
    
                if($resultado) {
                    // Procesar imágenes
                    $manager = new ImageManager(new Driver());
                    $carpetaFinal = '../public/img/productos';
                    
                    if(!is_dir($carpetaFinal)) mkdir($carpetaFinal, 0755, true);
    
                    foreach($imagenes as $imagenData) {
                        $nombreUnico = md5(uniqid(rand(), true));
                        
                        try {
                            $imagen = $manager->read($imagenData['tmp']);

                            // Redimensionar manteniendo relación de aspecto
                            $imagen->contain(800, 800);
                            
                            // Guardar con calidad
                            $imagen->toWebp(85)->save("$carpetaFinal/$nombreUnico.webp");
                            $imagen->toPng()->save("$carpetaFinal/$nombreUnico.png");
                            
                            $imagenProducto = new ImagenProducto([
                                'url' => $nombreUnico,
                                'productoId' => $producto->id
                            ]);
                            $imagenProducto->guardar();
                            
                        } catch (Exception $e) {
                            error_log("Error procesando imagen: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una de las imágenes';
                            continue;
                        }
                    }

                    // Procesar fichas técnicas
                    $carpetaFichas = '../public/fichas';
                    
                    if (!is_dir($carpetaFichas)) mkdir($carpetaFichas, 0755, true);

                    foreach ($fichas as $fichaData) {
                        $nombreSanitizado = $fichaData['name'];
                        $rutaCompleta = "$carpetaFichas/$nombreSanitizado";
                        
                        // Verificar nuevamente por si hay duplicados
                        if(file_exists($rutaCompleta)) {
                            $alertas['error'][] = "La ficha '$nombreSanitizado' ya existe en el servidor";
                            continue;
                        }
                        
                        try {
                            move_uploaded_file($fichaData['tmp'], $rutaCompleta);
                            
                            $fichaProducto = new FichaProducto([
                                'url' => $nombreSanitizado,
                                'productoId' => $producto->id
                            ]);
                            $fichaProducto->guardar();
                            
                        } catch (Exception $e) {
                            error_log("Error procesando ficha técnica: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una de las fichas técnicas';
                            continue;
                        }
                    }
    
                    // Guardar atributos
                    if(isset($_POST['atributos']) && is_array($_POST['atributos'])) {
                        // Eliminar atributos anteriores
                        ProductoAtributo::eliminarTodos($producto->id);
    
                        foreach ($_POST['atributos'] as $atributoId => $valores) {
                            $atributoId = (int)$atributoId; // Convertir a entero
                            $atributo = Atributo::find($atributoId); // Usar $atributoId
                        
                            foreach ($valores as $valor) {
                                $productoAtributo = new ProductoAtributo([
                                    'productoId' => $producto->id,
                                    'atributoId' => $atributoId, // Usar $atributoId
                                    'valor_texto' => strtolower($atributo->tipo) === 'texto' ? $valor : '',
                                    'valor_numero' => strtolower($atributo->tipo) === 'numero' ? $valor : ''
                                ]);
                                $productoAtributo->guardar();
                            }
                        }                        
                    }
    
                    header('Location: /admin/productos');
                }
            }
        }
    
        $router->render('admin/productos/crear', [
            'titulo' => 'Registrar Producto',
            'imagenes' => $imagenes,
            'fichas' => $fichas,
            'alertas' => $alertas,
            'producto' => $producto,
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'todosAtributos' => $todosAtributos,
            'relacionesAtributos' => $relacionesAtributos,
            'atributosDisponibles' => $atributosDisponibles,
        ], 'admin-layout');
    }

    public static function editar(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
            exit;
        }

        $alertas = [];
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            header('Location: /admin/productos');
            exit;
        }

        $producto = Producto::find($id);
        if (!$producto) {
            header('Location: /admin/productos');
            exit;
        }

        // Obtener datos relacionados
        $atributosDisponibles = array_unique(
            self::obtenerAtributosDisponibles(
                $producto->categoriaId,
                $producto->subcategoriaId
            ),
            SORT_REGULAR
        );

        // Valores existentes de atributos
        $atributosValores = [];
        $productoAtributos = ProductoAtributo::whereField('productoId', $producto->id);
        foreach ($productoAtributos as $pa) {
            $valor = !empty($pa->valor_texto) ? $pa->valor_texto : $pa->valor_numero;
            $atributosValores[$pa->atributoId][] = $valor;
        }

        // Elementos existentes
        $imagenes = ImagenProducto::whereField('productoId', $producto->id);
        $fichas = FichaProducto::whereField('productoId', $producto->id);
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $subcategoriasPorCategoria = [];
        foreach ($subcategorias as $subcategoria) {
            $subcategoriasPorCategoria[$subcategoria->categoriaId][] = $subcategoria;
        }

        // Obtener todos los atributos
        $todosAtributos = Atributo::all();
        
        // Precargar relaciones de atributos (igual que en crear)
        $relacionesAtributos = [
            'categorias' => [],
            'subcategorias' => []
        ];
        
        // Cargar relaciones categoría-atributo
        $categoriasAtributos = CategoriaAtributo::all();
        foreach ($categoriasAtributos as $ca) {
            if(!isset($relacionesAtributos['categorias'][$ca->categoriaId])) {
                $relacionesAtributos['categorias'][$ca->categoriaId] = [];
            }
            $relacionesAtributos['categorias'][$ca->categoriaId][] = $ca->atributoId;
        }
        
        // Cargar relaciones subcategoría-atributo
        $subcategoriasAtributos = SubcategoriaAtributo::all();
        foreach ($subcategoriasAtributos as $sa) {
            if(!isset($relacionesAtributos['subcategorias'][$sa->subcategoriaId])) {
                $relacionesAtributos['subcategorias'][$sa->subcategoriaId] = [];
            }
            $relacionesAtributos['subcategorias'][$sa->subcategoriaId][] = $sa->atributoId;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }

            $producto->sincronizar($_POST);
            $alertas = $producto->validar();

            // Validar subcategoría
            if($producto->categoriaId && empty($producto->subcategoriaId)) {
                $tieneSubcategorias = !empty($subcategoriasPorCategoria[$producto->categoriaId]);
                if($tieneSubcategorias) {
                    $alertas['error'][] = 'Debes seleccionar una subcategoría';
                }
            }

            // Validar fichas técnicas
            $fichasNuevas = []; // Cambiamos el nombre para diferenciar
            $nombresFichas = [];
            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'nuevas_fichas') === 0 && !empty($file['name'][0])) {
                    foreach($file['tmp_name'] as $index => $tmp_name) {
                        if ($file['error'][$index] === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                            $nombreOriginal = basename($file['name'][$index]);
                            $nombreSanitizado = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $nombreOriginal);

                            // Verificar duplicados en la misma subida
                            if(in_array($nombreSanitizado, $nombresFichas)) {
                                $alertas['error'][] = "La ficha '$nombreSanitizado' está duplicada en los archivos seleccionados";
                                continue;
                            }

                            // Verificar en base de datos
                            $existeFicha = FichaProducto::where('url', $nombreSanitizado);
                            if (!empty($existeFicha)) {
                                $alertas['error'][] = "La ficha '$nombreSanitizado' ya existe en el sistema";
                            }

                            $fichasNuevas[] = [
                                'tmp' => $tmp_name,
                                'name' => $nombreSanitizado
                            ];
                            $nombresFichas[] = $nombreSanitizado;
                        }
                    }
                }
            }

            // Calcular total de fichas (existentes no eliminadas + nuevas)
            $fichas_existentes_no_eliminadas = count(array_diff($_POST['fichas_existentes'] ?? [], $_POST['fichas_eliminadas'] ?? []));
            if (($fichas_existentes_no_eliminadas + count($fichasNuevas)) > 15) {
                $alertas['error'][] = 'Máximo 15 fichas técnicas permitidas por producto';
            }

            if(!empty($alertas)) {
                $fichas = FichaProducto::whereField('productoId', $producto->id);
            } else {
                $resultado = $producto->guardar();

                if($resultado) {
                    // Eliminar fichas marcadas para eliminar
                    $fichasEliminadas = $_POST['fichas_eliminadas'] ?? [];
                    if (!empty($fichasEliminadas)) {
                        foreach ($fichasEliminadas as $id) {
                            $id = (int)$id;
                            $ficha = FichaProducto::find($id);
                            if ($ficha) {
                                $rutaFicha = "../public/fichas/{$ficha->url}";
                                if (file_exists($rutaFicha)) {
                                    unlink($rutaFicha);
                                }
                                $ficha->eliminar();
                            }
                        }
                    }
    
                    // Procesar NUEVAS imágenes
                    if(isset($_FILES['nuevas_imagenes'])) {
                        $manager = new ImageManager(new Driver());
                        $carpetaFinal = '../public/img/productos';
                        
                        foreach($_FILES['nuevas_imagenes']['tmp_name'] as $key => $tmp_name) {
                            if($_FILES['nuevas_imagenes']['error'][$key] === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                                $nombreUnico = md5(uniqid(rand(), true));
                                try {
                                    $imagen = $manager->read($tmp_name);
    
                                    // Redimensionar manteniendo relación de aspecto
                                    $imagen->contain(800, 800);
                                    
                                    // Guardar con calidad
                                    $imagen->toWebp(85)->save("$carpetaFinal/$nombreUnico.webp");
                                    $imagen->toPng()->save("$carpetaFinal/$nombreUnico.png");
                                    
                                    $imagenProducto = new ImagenProducto([
                                        'url' => $nombreUnico,
                                        'productoId' => $producto->id
                                    ]);
                                    $imagenProducto->guardar();
                                } catch (Exception $e) {
                                    error_log("Error procesando imagen: " . $e->getMessage());
                                    $alertas['error'][] = 'Error al procesar una de las imágenes'; // Mostrar error al usuario
                                }
                            }
                        }
                    }
    
                    // Procesar nuevas fichas técnicas
                    $carpetaFichas = '../public/fichas';

                    if (!is_dir($carpetaFichas)) mkdir($carpetaFichas, 0755, true);

                    foreach ($fichasNuevas as $fichaData) {
                        $nombreSanitizado = $fichaData['name'];
                        $rutaCompleta = "$carpetaFichas/$nombreSanitizado";

                        // Verificar nuevamente por duplicados (esto podría ser redundante pero es una capa extra)
                        if(file_exists($rutaCompleta)) {
                            $alertas['error'][] = "La ficha '$nombreSanitizado' ya existe en el servidor";
                            continue;
                        }

                        try {
                            move_uploaded_file($fichaData['tmp'], $rutaCompleta);

                            $fichaProducto = new FichaProducto([
                                'url' => $nombreSanitizado,
                                'productoId' => $producto->id
                            ]);
                            $fichaProducto->guardar();
                        } catch (Exception $e) {
                            error_log("Error procesando ficha técnica: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una de las fichas técnicas';
                            continue;
                        }
                    }
    
                    // Eliminar imagenes marcadas para eliminar
                    if(!empty($_POST['imagenes_eliminadas'])) {
                        foreach($_POST['imagenes_eliminadas'] as $id) {
                            $imagen = ImagenProducto::find($id);
                            if($imagen) $imagen->eliminar();
                        }
                    }
    
                    // Actualizar atributos
                    ProductoAtributo::eliminarTodos($producto->id);
                    if(isset($_POST['atributos'])) {
                        foreach ($_POST['atributos'] as $atributoId => $valores) {
                            $atributo = Atributo::find($atributoId);
                            foreach ($valores as $valor) {
                                $productoAtributo = new ProductoAtributo([
                                    'productoId' => $producto->id,
                                    'atributoId' => $atributoId,
                                    'valor_texto' => $atributo->tipo === 'texto' ? $valor : '',
                                    'valor_numero' => $atributo->tipo === 'numero' ? $valor : null
                                ]);
                                $productoAtributo->guardar();
                            }
                        }
                    }
    
                    header('Location: /admin/productos');
                    exit;
                }
            }
        }

        $router->render('admin/productos/editar', [
            'titulo' => 'Editar Producto',
            'alertas' => $alertas,
            'producto' => $producto,
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'atributosDisponibles' => $atributosDisponibles,
            'atributosValores' => $atributosValores,
            'imagenes' => $imagenes,
            'fichas' => $fichas,
            'todosAtributos' => $todosAtributos,          
            'relacionesAtributos' => $relacionesAtributos 
        ], 'admin-layout');
    }

    public static function eliminar() {  
        if(!is_auth()) {
            header('Location: /login');
        }
    
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
            }
            
            $id = $_POST['id'];
            $producto = Producto::find($id);
    
            if(!$producto) {
                header('Location: /admin/productos');
            }
            
            // Eliminar atributos
            ProductoAtributo::eliminarTodos($producto->id);
            
            // Eliminar imágenes
            $imagenes = ImagenProducto::whereField('productoId', $producto->id);
            foreach($imagenes as $imagen) {
                // Eliminar archivos físicos
                if(file_exists("../public/img/productos/{$imagen->url}.png")) {
                    unlink("../public/img/productos/{$imagen->url}.png");
                    unlink("../public/img/productos/{$imagen->url}.webp");
                }
                $imagen->eliminar();
            }
            
            // Eliminar fichas técnicas
            $fichas = FichaProducto::whereField('productoId', $producto->id);
            foreach($fichas as $ficha) {
                if(file_exists("../public/fichas/{$ficha->url}")) {
                    unlink("../public/fichas/{$ficha->url}");
                }
                $ficha->eliminar();
            }
            
            // Eliminar producto
            $resultado = $producto->eliminar();
    
            if($resultado) {
                header('Location: /admin/productos');
            }
        }
    }


    private static function obtenerAtributosDisponibles($categoriaId, $subcategoriaId = null) {
        $atributosIds = [];
        $categoriaId = (int)$categoriaId;
        $subcategoriaId = $subcategoriaId ? (int)$subcategoriaId : null;
    
        // 1. Siempre obtener atributos de la CATEGORÍA principal
        $categoriaAtributos = CategoriaAtributo::whereField('categoriaId', $categoriaId);
        foreach ($categoriaAtributos as $ca) {
            $atributosIds[] = (int)$ca->atributoId;
        }
    
        // 2. Si hay subcategoría, agregar sus atributos
        if ($subcategoriaId) {
            $subcategoriaAtributos = SubcategoriaAtributo::whereField('subcategoriaId', $subcategoriaId);
            foreach ($subcategoriaAtributos as $sa) {
                $atributosIds[] = (int)$sa->atributoId;
            }
        }
    
        $atributosIdsUnicos = array_unique($atributosIds);
        return Atributo::whereIn('id', $atributosIdsUnicos) ?? [];
    }
}