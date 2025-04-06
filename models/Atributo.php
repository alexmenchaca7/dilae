<?php

namespace Model;

class Atributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'tipo'];
    protected static $tabla = 'atributos';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'tipo'];


    public $id;
    public $nombre;
    public $tipo;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? 'texto';
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

    public function validarValor($valor) {
        if($this->tipo === 'numero' && !is_numeric($valor)) {
            self::$alertas['error'][] = "El atributo {$this->nombre} debe ser numérico";
        }
        return self::$alertas;
    }
}