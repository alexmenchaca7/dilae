<?php

namespace Model;

class Subcategoria extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'categoriaId'];
    protected static $tabla = 'subcategorias';  


    public $id;
    public $nombre;
    public $categoriaId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->categoriaId = $args['categoriaId'] ?? '';
    }

    // Validar formulario   
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->categoriaId) {
            self::$alertas['error'][] = 'La categoria es obligatoria';
        }

        return self::$alertas;
    }
}