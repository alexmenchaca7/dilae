<?php

namespace Model;

class Blog extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'tipo', 'unidad', 'espacio_unidad'];
    protected static $tabla = 'blogs';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'tipo', 'unidad'];


    public $id;
    public $nombre;
    public $tipo;
    public $unidad;
    public $espacio_unidad;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? 'texto';
        $this->unidad = $args['unidad'] ?? '';
        $this->espacio_unidad = $args['espacio_unidad'] ?? 0;
        
    }


    // Validar formulario
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre del atributo es obligatorio';
        }
        if(!$this->tipo) {
            self::$alertas['error'][] = 'El tipo del atributo es obligatorio';
        }
        return self::$alertas;
    }
}