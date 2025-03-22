<?php

namespace Model;

class Producto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'descripcion', 'subcategoriaId'];
    protected static $tabla = 'productos';  


    public $id;
    public $nombre;
    public $descripcion;
    public $subcategoriaId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->subcategoriaId = $args['subcategoriaId'] ?? '';
    }

    // Validar formulario   
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->descripcion) {
            self::$alertas['error'][] = 'La descripcion es obligatoria';
        }

        if(!$this->subcategoriaId) {
            self::$alertas['error'][] = 'La subcategoria es obligatoria';
        }

        return self::$alertas;
    }
}