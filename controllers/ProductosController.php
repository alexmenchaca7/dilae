<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Atributo;
use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use Model\ImagenProducto;
use Model\ProductoAtributo;
use Model\CategoriaAtributo;
use Model\SubcategoriaAtributo;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductosController {
    public static function index(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

        $router->render('admin/productos/index', [
            'titulo' => 'Productos'
        ], 'admin-layout');
    }

    public static function crear(Router $router) {
        if(!is_auth()) {
            header('Location: /login');
        }

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
        $subcategoriasAtributos = SubcategoriaAtributo::all();
        foreach ($subcategoriasAtributos as $sa) {
            $relacionesAtributos['subcategorias'][$sa->subcategoriaId][] = $sa->atributoId; 
        }

        // Cargar relaciones subcategoría-atributo
        $subcategoriasAtributos = SubcategoriaAtributo::all();
        foreach ($subcategoriasAtributos as $sa) {
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

            // Validar que los atributos agregados pertenezcan a la categoría/subcategoría
            if(isset($_POST['atributos']) && is_array($_POST['atributos'])) {
                $atributosPermitidos = self::obtenerAtributosDisponibles(
                    $producto->categoriaId, 
                    $producto->subcategoriaId ?? null
                );

                // Convertir a array de IDs para validación
                $atributosPermitidosIds = array_map(function($atributo) {
                    return (string)$atributo->id; // Forzar conversión a string
                }, $atributosPermitidos);

                foreach($_POST['atributos'] as $atributoId => $valores) {
                    // Convertir el ID a string para comparación
                    $atributoIdStr = (string)$atributoId;

                    // Verificar que el atributo esté permitido
                    if(!in_array($atributoIdStr, $atributosPermitidosIds)) {
                        $alertas['error'][] = 'Uno o más atributos no pertenecen a la categoría/subcategoría seleccionada';
                        break;
                    }

                    // Validar cada valor del atributo
                    foreach((array)$valores as $valor) {
                        if(!empty($valor)) {
                            $atributo = Atributo::find($atributoId);
                            if(!$atributo) continue;
                            
                            $alertasAtributo = $atributo->validarValor($valor);
                            
                            if(!empty($alertasAtributo['error'])) {
                                $alertas['error'] = array_merge($alertas['error'] ?? [], $alertasAtributo['error']);
                            }
                        }
                    }
                }
            }

            // Validar imágenes
            $imagenes = [];
            foreach($_FILES as $key => $file) {
                if(strpos($key, 'imagenes_') === 0 && !empty($file['name'][0])) {
                    if ($file['error'][0] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'][0])) {
                        $imagenes[] = [
                            'tmp' => $file['tmp_name'][0],
                            'name' => $file['name'][0]
                        ];
                    }
                }
            }

            if(count($imagenes) === 0) {
                $alertas['error'][] = 'Debes subir al menos una imagen del producto';
            } elseif(count($imagenes) > 5) {
                $alertas['error'][] = 'Máximo 5 imágenes permitidas por producto';
            }

            // Procesar imágenes solo si no hay errores
            if(empty($alertas['error'])) {
                $resultado = $producto->guardar();

                if($resultado) {
                    $manager = new ImageManager(new Driver());
                    $carpetaFinal = '../public/img/productos';
                    
                    if(!is_dir($carpetaFinal)) mkdir($carpetaFinal, 0755, true);

                    // Procesar todas las imágenes
                    foreach($imagenes as $imagenData) {
                        $nombreUnico = md5(uniqid(rand(), true));
                        
                        try {
                            // Asegúrate de pasar solo la ruta del archivo temporal
                            $imagen = $manager->read($imagenData['tmp']); // Cambio clave aquí
                            
                            $imagen->cover(800, 800);
                            $imagen->toPng()->save("$carpetaFinal/$nombreUnico.png");
                            $imagen->toWebp()->save("$carpetaFinal/$nombreUnico.webp");
                            
                            $imagenProducto = new ImagenProducto([
                                'url' => $nombreUnico,
                                'productoId' => $producto->id
                            ]);
                            $imagenProducto->guardar();
                            
                        } catch (Exception $e) {
                            // Registra el error pero permite continuar
                            error_log("Error procesando imagen: " . $e->getMessage());
                            $alertas['error'][] = 'Error al procesar una de las imágenes';
                            continue;
                        }
                    }

                    // Guardar atributos
                    if(isset($_POST['atributos']) && is_array($_POST['atributos'])) {
                        // Eliminar atributos anteriores
                        if($producto->id) {
                            $atributosAnteriores = ProductoAtributo::whereField('productoId', $producto->id);
                            if(is_array($atributosAnteriores)) {
                                foreach($atributosAnteriores as $atributoAnterior) {
                                    $atributoAnterior->eliminar();
                                }
                            }
                        }

                        foreach($_POST['atributos'] as $atributoId => $valor) {
                            if(!empty($valor)) {
                                $atributo = Atributo::find($atributoId);
                                if(!$atributo) continue;
                                
                                $alertasAtributo = $atributo->validarValor($valor);
                                
                                if(!empty($alertasAtributo['error'])) {
                                    $alertas['error'] = array_merge($alertas['error'] ?? [], $alertasAtributo['error']);
                                    continue;
                                }
                                
                                $productoAtributo = new ProductoAtributo([
                                    'productoId' => $producto->id,
                                    'atributoId' => $atributoId,
                                    'valor_texto' => $atributo->tipo === 'texto' ? $valor : '',
                                    'valor_numero' => $atributo->tipo === 'numero' ? $valor : ''
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
            'alertas' => $alertas,
            'producto' => $producto,
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'todosAtributos' => $todosAtributos,
            'relacionesAtributos' => $relacionesAtributos,
            'atributosDisponibles' => $atributosDisponibles,
        ], 'admin-layout');
    }

    private static function obtenerAtributosDisponibles($categoriaId, $subcategoriaId = null) {
        $atributosIds = [];

        // Determinar si la categoría tiene subcategorías
        $tieneSubcategorias = !empty(Subcategoria::where('categoriaId', $categoriaId));

        // Lógica principal
        if ($tieneSubcategorias) {
            // Si tiene subcategorías, solo usar atributos de la subcategoría (si existe)
            if ($subcategoriaId) {
                $atributosSubcategoria = SubcategoriaAtributo::where('subcategoriaId', $subcategoriaId);
                foreach ($atributosSubcategoria as $sa) {
                    $atributosIds[] = $sa->atributoId;
                }
            }
        } else {
            // Si no tiene subcategorías, usar atributos de la categoría
            $atributosCategoria = CategoriaAtributo::where('categoriaId', $categoriaId);
            foreach ($atributosCategoria as $ca) {
                $atributosIds[] = $ca->atributoId;
            }
        }

        // Eliminar duplicados y obtener objetos Atributo
        $atributosIdsUnicos = array_unique($atributosIds);
        return empty($atributosIdsUnicos) ? [] : Atributo::whereIn('id', $atributosIdsUnicos);
    }
}