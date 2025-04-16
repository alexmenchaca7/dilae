<?php

namespace Model;

class Atributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'nombre', 'tipo', 'unidad'];
    protected static $tabla = 'atributos';  

    // Propiedad con las columnas a buscar
    protected static $buscarColumns = ['nombre', 'tipo', 'unidad'];


    public $id;
    public $nombre;
    public $tipo;
    public $unidad;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? 'texto';
        $this->unidad = $args['unidad'] ?? '';
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
        if ($this->tipo === 'numero' && !is_numeric($valor)) {
            $this->alertas['error'][] = "El valor debe ser numérico";
        }
        
        return self::$alertas;
    }
}