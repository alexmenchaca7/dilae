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
}