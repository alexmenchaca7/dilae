<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\Producto;
use Model\Categoria;
use Model\Subcategoria;
use Model\ImagenProducto;
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

                    header('Location: /admin/productos');
                }
            }
        }

        $router->render('admin/productos/crear', [
            'titulo' => 'Registrar Producto',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'producto' => $producto
        ], 'admin-layout');
    }
}