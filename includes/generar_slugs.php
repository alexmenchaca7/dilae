<?php
require_once __DIR__ . '/../includes/app.php'; // Ruta corregida

use Model\Categoria;
use Model\Subcategoria;
use Model\Producto;

// Generar slugs para categorías existentes
$categorias = Categoria::all();
foreach ($categorias as $categoria) {
    if (empty($categoria->slug)) {
        $categoria->slug = Categoria::crearSlug($categoria->nombre);
        $categoria->guardar();
    }
}

// Generar slugs para subcategorías existentes
$subcategorias = Subcategoria::all();
foreach ($subcategorias as $subcategoria) {
    if (empty($subcategoria->slug)) {
        $subcategoria->slug = Subcategoria::crearSlug($subcategoria->nombre);
        $subcategoria->guardar();
    }
}

// Generar slugs para productos existentes
$productos = Producto::all();
foreach ($productos as $producto) {
    if (empty($producto->slug)) {
        $producto->slug = Producto::crearSlug($producto->nombre);
        $producto->guardar();
    }
}

echo "¡Slugs generados exitosamente para todos los registros existentes!";