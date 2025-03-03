<?php

namespace Model;

class ProductoProyecto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'productoId', 'proyectoId'];
    protected static $tabla = 'productos_proyectos';  


    public $id;
    public $productoId;
    public $proyectoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->productoId = $args['productoId'] ?? '';
        $this->proyectoId = $args['proyectoId'] ?? '';
    }
}