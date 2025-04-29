<?php

namespace Model;

class ImagenProducto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'url', 'productoId', 'posicion'];
    protected static $tabla = 'imagenes_producto';  


    public $id;
    public $url;
    public $productoId;
    public $posicion;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->url = $args['url'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
        $this->posicion = $args['posicion'] ?? 0;
    }

    public static function obtenerPrincipalPorProductoId(int $productoId) {
        $query = "SELECT * FROM " . self::$tabla . " 
                  WHERE productoId = " . self::$conexion->escape_string($productoId) . " 
                  ORDER BY posicion ASC LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }
}