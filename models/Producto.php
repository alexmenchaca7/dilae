<?php

namespace Model;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'slug', 'descripcion', 'categoriaId', 'subcategoriaId'];
    protected static $tabla = 'productos';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumnasDirectas = ['nombre', 'descripcion']; 

    public $id;
    public $nombre;
    public $slug;
    public $descripcion;
    public $categoriaId;
    public $subcategoriaId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->slug = $args['slug'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->categoriaId = $args['categoriaId'] ?? '';
        $this->subcategoriaId = $args['subcategoriaId'] ?? null;
    }

    // Validar formulario   
    public function validar() {
        // Generar slug automáticamente si no existe
        if(!$this->slug) {
            $this->slug = self::crearSlug($this->nombre);
        }

        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(strlen($this->nombre) > 100) { // Ejemplo de validación de longitud
            self::$alertas['error'][] = 'El nombre no puede exceder los 100 caracteres.';
        }

        if(!$this->descripcion) {
            self::$alertas['error'][] = 'La descripcion es obligatoria';
        }

        if (!$this->categoriaId) {
            self::$alertas['error'][] = 'La categoría es obligatoria';
        }

        // Verificar que el slug sea único
        $existe = self::where('slug', $this->slug);
        if($existe && $existe->id != $this->id) {
            self::$alertas['error'][] = 'El slug ya existe';
        }

        return self::$alertas;
    }

    // Método para crear slugs
    public static function crearSlug($texto) {
        // Mapa de caracteres especiales
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n', 'ü' => 'u', 'Ü' => 'u'
        ];
        
        // Convertir caracteres especiales
        $texto = strtr($texto, $map);
        
        // Transliterar otros caracteres acentuados
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        
        // Eliminar caracteres no alfanuméricos
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $texto);
        
        // Convertir a minúsculas y limpiar
        $slug = strtolower(trim($slug, '-'));
        
        // Si está vacío, generar slug aleatorio
        if(empty($slug)) {
            $slug = 'producto-' . uniqid();
        }
        
        return $slug;
    }

    public function categoria() {
        return Categoria::find($this->categoriaId) ?? new Categoria(['nombre' => 'Sin categoría', 'slug' => 'sin-categoria']);
    }    
    
    public function subcategoria() {
        return $this->subcategoriaId ? Subcategoria::find($this->subcategoriaId) : null;
    }   
    
    public static function buscar($termino) {
        $condicionesGenerales = [];
        $terminoGeneral = trim($termino);

        if (empty($terminoGeneral)) {
            return $condicionesGenerales; // No hay término, no hay condiciones.
        }

        // Dividir el término de búsqueda en palabras individuales
        // Esto permite una búsqueda tipo "AND" entre palabras (ej. "rojo metal" busca productos con "rojo" Y "metal")
        $palabrasBusqueda = explode(' ', $terminoGeneral);
        $palabrasBusqueda = array_filter($palabrasBusqueda); // Eliminar elementos vacíos

        if (empty($palabrasBusqueda)) {
            return $condicionesGenerales;
        }

        foreach ($palabrasBusqueda as $palabra) {
            $palabraEscapada = self::$conexion->escape_string($palabra);
            $palabraLower = mb_strtolower($palabraEscapada, 'UTF-8');

            $condicionesParaEstaPalabra = [];

            // 1. Búsqueda en columnas directas de la tabla 'productos'
            if (!empty(static::$buscarColumnasDirectas)) {
                foreach (static::$buscarColumnasDirectas as $columna) {
                    // Es importante usar el alias de la tabla 'productos' si otras partes de la query lo usan,
                    // o si hay riesgo de ambigüedad con otras tablas (aunque con EXISTS es menos problemático).
                    // Por ahora, asumimos que la query principal selecciona de `productos`.
                    $condicionesParaEstaPalabra[] = "LOWER(productos.{$columna}) LIKE '%" . $palabraLower . "%'";
                }
            }

            // 2. Búsqueda en atributos del producto (tabla 'productos_atributos')
            // Se usa un SUBQUERY con EXISTS para verificar si algún atributo del producto coincide.
            // `productos.id` se refiere al ID del producto de la consulta principal.
            $condicionesParaEstaPalabra[] = "EXISTS (
                SELECT 1
                FROM productos_atributos pa
                WHERE pa.productoId = productos.id
                AND (
                    LOWER(pa.valor_texto) LIKE '%" . $palabraLower . "%' 
                    OR LOWER(CAST(pa.valor_numero AS CHAR)) LIKE '%" . $palabraLower . "%'
                    /* Si tienes otros campos de valor en productos_atributos (ej. valor_booleano como texto), añádelos aquí */
                )
            )";
            
            // Unimos las condiciones para ESTA palabra con OR:
            // La palabra debe estar en el nombre O en la descripción O en un atributo.
            if (!empty($condicionesParaEstaPalabra)) {
                $condicionesGenerales[] = "(" . implode(' OR ', $condicionesParaEstaPalabra) . ")";
            }
        }
        
        // Las condiciones de cada palabra se unen con AND implícitamente 
        // por cómo `metodoSQL` o `totalCondiciones` usan el array de condiciones.
        // Ejemplo: (nombre LIKE %pal1% OR attr LIKE %pal1%) AND (nombre LIKE %pal2% OR attr LIKE %pal2%)
        return $condicionesGenerales;
    }
}