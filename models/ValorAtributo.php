<?php

namespace Model;

class ValorAtributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'valor_texto', 'valor_numero', 'atributoId', 'productoId'];
    protected static $tabla = 'valores_atributos';  


    public $id;
    public $valor_texto;
    public $valor_numero;
    public $atributoId;
    public $productoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->valor_texto = $args['valor_texto'] ?? '';
        $this->valor_numero = $args['valor_numero'] ?? '';
        $this->atributoId = $args['atributoId'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
    }
}