<?php

namespace Model;

class FichaProducto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'url', 'productoId'];
    protected static $tabla = 'fichas_producto';  


    public $id;
    public $url;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->url = $args['url'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }
}