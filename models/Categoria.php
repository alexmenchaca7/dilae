<?php

namespace Model;

class Categoria extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'slug'];
    protected static $tabla = 'categorias';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre'];


    public $id;
    public $nombre;
    public $slug;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->slug = $args['slug'] ?? '';
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

        // Verificar que el slug sea único
        $existe = self::where('slug', $this->slug);
        if($existe && $existe->id != $this->id) {
            self::$alertas['error'][] = 'El slug ya existe';
        }

        return self::$alertas;
    }

    public static function buscar($termino) {
        $condiciones = [];
    
        if (!empty($termino)) {
            $termino = self::$conexion->escape_string($termino);
            $terminoLower = mb_strtolower($termino, 'UTF-8');
            
            // Búsqueda case-insensitive en categorías y subcategorías
            $condiciones[] = "(
                LOWER(nombre) LIKE '%$terminoLower%' 
                OR id IN (
                    SELECT categoriaId 
                    FROM subcategorias 
                    WHERE LOWER(nombre) LIKE '%$terminoLower%'
                )
            )";
        }
        
        return $condiciones;
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
}