<?php

namespace Model;

class Atributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'tipo'];
    protected static $tabla = 'atributos';  


    public $id;
    public $nombre;
    public $tipo;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? '';
    }
}