<?php
    // 1. Cargar el archivo de inicialización de la aplicación (app.php)
    require_once __DIR__ . '/../includes/app.php';  // Esto carga Dotenv, ActiveRecord, funciones, database.php y establece la BD.

    use Model\Producto;
    use Model\Categoria;
    use Model\Subcategoria;
    use Model\ActiveRecord; // Importar ActiveRecord para acceder a getDbConnection

    header('Content-Type: application/json');

    $term = $_GET['term'] ?? '';
    $results = [];

    if (empty(trim($term)) || strlen(trim($term)) < 2) {
        echo json_encode($results);
        exit;
    }

    // 2. Acceder a la conexión DB
    $db_connection = ActiveRecord::getDbConnection(); // Usaremos el método que añadiremos a ActiveRecord

    if (!$db_connection) {
        error_log("Error crítico: No se pudo obtener la conexión a la BD en global_search.php");
        echo json_encode(['error' => 'Error interno del servidor.']); // Mensaje genérico al usuario
        http_response_code(500);
        exit;
    }

    $escapedTerm = $db_connection->escape_string(trim($term));
    $lowerTerm = mb_strtolower($escapedTerm, 'UTF-8');

    $limitPerType = 4; // Límite de resultados por cada tipo

    // --- 1. Buscar Productos ---
    // La consulta de productos ahora incluye JOINs para obtener los slugs de categoría y subcategoría directamente.
    // El método Producto::buscar() que tenías es para el filtro en la página de productos,
    // aquí haremos una consulta más directa para el autocompletado global.
    $queryProductos = "SELECT p.id, p.nombre, p.slug, 
                            c.slug as categoria_slug_prod, 
                            s.slug as subcategoria_slug_prod
                    FROM productos p
                    LEFT JOIN categorias c ON p.categoriaId = c.id
                    LEFT JOIN subcategorias s ON p.subcategoriaId = s.id
                    WHERE (
                        LOWER(p.nombre) LIKE '%{$lowerTerm}%' 
                        OR LOWER(p.descripcion) LIKE '%{$lowerTerm}%' 
                        OR LOWER(p.sku) LIKE '%{$lowerTerm}%' /* Asumiendo que tienes p.sku */
                        OR EXISTS ( /* Búsqueda en atributos del producto */
                            SELECT 1
                            FROM productos_atributos pa
                            WHERE pa.productoId = p.id
                            AND (
                                LOWER(pa.valor_texto) LIKE '%{$lowerTerm}%' 
                                OR LOWER(CAST(pa.valor_numero AS CHAR)) LIKE '%{$lowerTerm}%'
                            )
                        )
                    )
                    ORDER BY p.nombre ASC
                    LIMIT {$limitPerType}";

    $productos = Producto::consultarSQL($queryProductos); // Producto::consultarSQL usará ActiveRecord::crearObjeto

    foreach ($productos as $producto) {
        // Gracias a AllowDynamicProperties, $producto->categoria_slug_prod y $producto->subcategoria_slug_prod estarán disponibles
        $categoriaSlugProd = $producto->categoria_slug_prod ?? 'sin-categoria';
        $subcategoriaSlugProd = $producto->subcategoria_slug_prod ?? null;

        $url = "/productos/";
        $url .= $categoriaSlugProd . "/";
        if ($subcategoriaSlugProd) {
            $url .= $subcategoriaSlugProd . "/";
        }
        $url .= $producto->slug;

        $results[] = [
            'label' => $producto->nombre,
            'url'   => $url,
            'type'  => 'Producto'
        ];
    }

    // --- 2. Buscar Categorías ---
    $queryCategorias = "SELECT id, nombre, slug 
                        FROM categorias
                        WHERE LOWER(nombre) LIKE '%{$lowerTerm}%'
                        ORDER BY nombre ASC
                        LIMIT {$limitPerType}";
    $categorias = Categoria::consultarSQL($queryCategorias);

    foreach ($categorias as $categoria) {
        $results[] = [
            'label' => $categoria->nombre,
            'url'   => "/productos/" . $categoria->slug,
            'type'  => 'Categoría'
        ];
    }

    // --- 3. Buscar Subcategorías ---
    $querySubcategorias = "SELECT s.id, s.nombre, s.slug AS subcategoria_slug, 
                                c.slug AS categoria_slug, c.nombre AS categoria_nombre_parent
                        FROM subcategorias s
                        INNER JOIN categorias c ON s.categoriaId = c.id
                        WHERE LOWER(s.nombre) LIKE '%{$lowerTerm}%'
                        ORDER BY s.nombre ASC
                        LIMIT {$limitPerType}";
    $subcategoriasData = Subcategoria::consultarSQL($querySubcategorias); 

    foreach ($subcategoriasData as $sub) {
        // $sub->categoria_slug y $sub->categoria_nombre_parent deberían estar disponibles
        $subNombre = $sub->nombre ?? 'N/A';
        $catSlug = $sub->categoria_slug ?? 'sin-categoria';
        $subSlug = $sub->subcategoria_slug ?? 'sin-subcategoria';
        $catNombreParent = $sub->categoria_nombre_parent ?? 'Categoría Desc.'; // Nombre de la categoría padre

        $results[] = [
            'label' => $subNombre . " (en " . $catNombreParent . ")",
            'url'   => "/productos/" . $catSlug . "/" . $subSlug,
            'type'  => 'Subcategoría'
        ];
    }

    // Opcional: Limitar el total de resultados
    // $results = array_slice($results, 0, 10); 

    echo json_encode($results);
    exit;
?>