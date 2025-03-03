<?php

namespace Model;

class Administrador extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'correo', 'pass'];
    protected static $tabla = 'administradores';  


    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $pass;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->correo = $args['correo'] ?? '';
        $this->pass = $args['pass'] ?? '';
    }
}