<?php

namespace Model;

class CategoriaAtributo extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'categoriaId', 'atributoId', 'posicion'];
    protected static $tabla = 'categoria_atributos';  


    public $id;
    public $categoriaId;
    public $atributoId;
    public $posicion;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->categoriaId = $args['categoriaId'] ?? null;
        $this->atributoId = $args['atributoId'] ?? null;
        $this->posicion = $args['posicion'] ?? 0;
    }

    // Validar: por ejemplo, que tanto la categoría como el atributo existan
    public function validar() {
        if(!$this->categoriaId) {
            self::$alertas['error'][] = 'La categoria es obligatoria';
        }
        if(!$this->atributoId) {
            self::$alertas['error'][] = 'El atributo es obligatorio';
        }
        return self::$alertas;
    }

    // Obtiene los IDs de los atributos asociados a la categoría
    public static function getAtributosPorCategoria($categoriaId) {
        $categoriaId = self::$conexion->escape_string($categoriaId);
        $query = "SELECT atributoId 
                FROM " . static::$tabla . " 
                WHERE categoriaId = '$categoriaId' 
                ORDER BY posicion ASC"; // Agregar orden
                
        $resultado = self::$conexion->query($query);
        $atributos = [];
        while($row = $resultado->fetch_assoc()) {
            $atributos[] = $row['atributoId'];
        }
        return $atributos;
    }

    // Elimina las asociaciones para la categoría dada
    public static function eliminarPorCategoria($categoriaId) {
        $categoriaId = self::$conexion->escape_string($categoriaId);
        $query = "DELETE FROM " . static::$tabla . " WHERE categoriaId = '$categoriaId'";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    public static function normalizarPosiciones($categoriaId) {
        $relaciones = self::whereArray(['categoriaId' => $categoriaId], 'posicion ASC');
        $posicion = 1;
        
        foreach ($relaciones as $relacion) {
            if($relacion->posicion != $posicion) {
                $relacion->posicion = $posicion;
                $relacion->guardar();
            }
            $posicion++;
        }
    }
}