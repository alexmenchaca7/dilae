<?php

namespace Model;

class ImagenProducto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'url', 'productoId'];
    protected static $tabla = 'imagenes_producto';  


    public $id;
    public $url;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->url = $args['url'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }

    public static function obtenerPrincipalPorProductoId(int $productoId) {
        // Busca la primera imagen asociada a ese productoId, ordenada por ID (o como prefieras)
        $query = "SELECT * FROM " . self::$tabla . " WHERE productoId = " . self::$conexion->escape_string($productoId) . " ORDER BY id ASC LIMIT 1";
        $resultado = self::consultarSQL($query); // Usa el método heredado de ActiveRecord
        return array_shift($resultado); // Devuelve el objeto ImagenProducto o null si no hay
    }
}