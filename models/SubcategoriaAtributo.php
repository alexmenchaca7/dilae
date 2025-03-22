<?php

namespace Model;

class SubcategoriaAtributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'subcategoriaId', 'atributoId'];
    protected static $tabla = 'subcategoria_atributos';  


    public $id;
    public $subcategoriaId;
    public $atributoId;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->subcategoriaId = $args['subcategoriaId'] ?? null;
        $this->atributoId = $args['atributoId'] ?? null;
    }

    // Validar: por ejemplo, que tanto la subcategoría como el atributo existan
    public function validar() {
        if(!$this->subcategoriaId) {
            self::$alertas['error'][] = 'La subcategoria es obligatoria';
        }
        if(!$this->atributoId) {
            self::$alertas['error'][] = 'El atributo es obligatorio';
        }
        return self::$alertas;
    }

    // Obtiene los IDs de los atributos asociados a la subcategoría
    public static function getAtributosPorSubcategoria($subcategoriaId) {
        $subcategoriaId = self::$conexion->escape_string($subcategoriaId);
        $query = "SELECT atributoId FROM " . static::$tabla . " WHERE subcategoriaId = '$subcategoriaId'";
        $resultado = self::$conexion->query($query);
        $atributos = [];
        while($row = $resultado->fetch_assoc()) {
            $atributos[] = $row['atributoId'];
        }
        return $atributos;
    }
    
    // Elimina las asociaciones para la subcategoría dada
    public static function eliminarPorSubcategoria($subcategoriaId) {
        $subcategoriaId = self::$conexion->escape_string($subcategoriaId);
        $query = "DELETE FROM " . static::$tabla . " WHERE subcategoriaId = '$subcategoriaId'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }
    
}