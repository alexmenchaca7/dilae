<?php

namespace Model;

class ProductoAtributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'valor_texto', 'valor_numero', 'productoId', 'atributoId'];
    protected static $tabla = 'productos_atributos';  


    public $id;
    public $valor_texto;
    public $valor_numero;
    public $productoId;
    public $atributoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->valor_texto = $args['valor_texto'] ?? '';
        $this->valor_numero = $args['valor_numero'] ?? '';
        $this->productoId = $args['productoId'] ?? '';
        $this->atributoId = $args['atributoId'] ?? '';
    }

    public function getValor() {
        return $this->valor_numero !== '' ? $this->valor_numero : $this->valor_texto;
    }
}