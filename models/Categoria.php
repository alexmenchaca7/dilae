<?php

namespace Model;

class Categoria extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre'];
    protected static $tabla = 'categorias';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre'];


    public $id;
    public $nombre;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
    }

    // Validar formulario
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        return self::$alertas;
    }

    public static function buscar($termino) {
        $condiciones = [];
        $termino = self::$conexion->escape_string($termino);
        
        // Búsqueda en categorías y subcategorías relacionadas
        $condiciones[] = "nombre LIKE '%$termino%' OR id IN (
            SELECT categoriaId 
            FROM subcategorias 
            WHERE nombre LIKE '%$termino%'
        )";
        
        return $condiciones;
    }
}