<?php

namespace Model;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'slug', 'descripcion', 'categoriaId', 'subcategoriaId'];
    protected static $tabla = 'productos';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre'];

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
}