<?php

namespace Model;
#[\AllowDynamicProperties]

// CLASE PADRE
class ActiveRecord {

    // BASE DE DATOS
    protected static $conexion; // Static porque no require una nueva instancia, siempre son las mismas credenciales
    protected static $columnasDB = [];
    protected static $tabla = '';

    // ALERTAS
    protected static $alertas = []; 

    // PROPIEDAD CON LAS COLUMNAS A BUSCAR
    protected static $buscarColumns = []; 

    // Definir la conexion a la base de datos
    public static function setDB($database) {
        self::$conexion = $database; // Self hace referencia a los atributos estaticos de esta misma clase
    }

    public static function getDbConnection() {
        return self::$conexion; 
    }

    // Setear un tipo de Alerta
    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Obtener las alertas
    public static function getAlertas() {
        return static::$alertas;
    }

    // Validación que se hereda en modelos
    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria (Active Record)
    public static function consultarSQL($query) {
        // Consultar la BD
        $resultado = self::$conexion->query($query);

        // Iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // Liberar la memoria
        $resultado->free();

        // Retornar los resultados
        return $array;
    }

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro) {
        // Creando objeto de la clase actual
        $objeto = new static;

        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) {  // Revisar de un objeto que una propiedad exista (ya sea la llave o el valor)
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos() {
        $atributos = [];

        // Recorriendo el arreglo columnasDB 
        foreach (static::$columnasDB as $columna) {
            if($columna == 'id') continue; // Ignoramos el campo de ID ya que se agrega automatico
            $atributos[$columna] = $this->$columna;
        }
        
        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];

        // Recorriendo el arreglo como un arreglo asociativo (tanto llave como valor)
        foreach($atributos as $key => $value) {
            // Convertir cadenas vacías a null
            if ($value === '' || $value === null) {
                $sanitizado[$key] = null;
            } else {
                $sanitizado[$key] = self::$conexion->escape_string($value);
            }
        }    

        return $sanitizado;
    }

    // Sincroniza el objeto en memoria con los cambios realizados por el usuario
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {  // Revisar de un objeto que una propiedad exista
                $this->$key = $value;
            }
        }
    }

    // Registros - CRUD
    public function guardar() {
        $resultado = '';
        if(!is_null($this->id)){ 
            // Actualizar registro
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    // Obtener todos los registros
    public static function all($orden = 'ASC') {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id $orden";
        $resultado = self::consultarSQL($query);
        return $resultado; 
    }

    // Busca un registro por su ID
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = $id";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado); // Retorna el primer objeto del arreglo de objetos
    }

    // Obtener determinado numero de registros
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT $limite"; 
        $resultado = self::consultarSQL($query);
        return $resultado; // Retorna un arreglo de objetos 
    }

    // Paginar los registros
    public static function paginar($por_pagina, $offset) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT $por_pagina OFFSET $offset" ;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Método where que retorna un único objeto (el primero)
    public static function where($columna, $valor) {
        $valor = self::$conexion->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE $columna = '$valor' LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Método whereField que devuelve TODOS los objetos que cumplen la condición
    public static function whereField($campo, $valor) {
        $valor = self::$conexion->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE $campo = '$valor'";
        return self::consultarSQL($query);
    }

    public static function whereIn($columna, $valores) {
        if(empty($valores)) return [];
        
        $valoresEscapados = array_map(function($valor) {
            return self::$conexion->escape_string($valor);
        }, $valores);
        
        $query = "SELECT * FROM " . static::$tabla . " WHERE $columna IN ('" . implode("','", $valoresEscapados) . "')";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busqueda Where con Múltiples opciones
    public static function whereArray($array = [], $order = '') {
        $query = "SELECT * FROM " . static::$tabla;
    
        // Construir WHERE solo si hay condiciones
        if (!empty($array)) {
            $query .= " WHERE ";
            $conditions = [];
            foreach ($array as $key => $value) {
                $conditions[] = "$key = '" . self::$conexion->escape_string($value) . "'";
            }
            $query .= implode(' AND ', $conditions);
        }
        
        // Agregar ORDER BY si se especifica
        if (!empty($order)) {
            $query .= " ORDER BY " . $order;
        }
        
        return self::consultarSQL($query);
    }

    // Retornar los registros por un orden
    public static function ordenar($columna, $orden) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY $columna $orden"; 
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Retornar por orden y con un limite
    public static function ordenarLimite($columna, $orden, $limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY $columna $orden LIMIT $limite"; 
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Traer un total de registros
    public static function total($columna = '', $valor = '') {
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        if($columna) {
            $query .= " WHERE $columna = $valor";
        }
        $resultado = self::$conexion->query($query);
        $total = $resultado->fetch_array();

        return array_shift($total);
    }

    // Total de Registros con un Array Where
    public static function totalArray($array = []) {
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ";
        foreach($array as $key => $value) {
            if($key == array_key_last($array)) {
                $query .= " $key = '$value' ";
            } else {
                $query .= " $key = '$value' AND ";
            }
        }
        $resultado = self::$conexion->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    // Crear un nuevo registro
    public function crear() {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $columnas = [];
        $filas = [];

        // Reemplazar los valores NULL por la palabra 'NULL' en la consulta
        foreach ($atributos as $key => $value) {
            $columnas[] = $key;
            if ($value === null) {
                $valores[] = "NULL";
            } else {
                $valores[] = "'" . self::$conexion->escape_string($value) . "'";
            }
        }
    
        $columnasStr = join(', ', $columnas);
        $valoresStr = join(', ', $valores);

        // Reemplazar las comillas adicionales antes de insertar en la consulta
        $query = "INSERT INTO " . static::$tabla . " ($columnasStr) VALUES ($valoresStr)";

        // Ejecutar la consulta
        $resultado = self::$conexion->query($query); 

        if($resultado) {
            // Asignar el id generado al objeto
            $this->id = self::$conexion->insert_id;
        }

        return $resultado;
    }

    // Actualizar un registro
    public function actualizar(){
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach($atributos as $key => $value) {
            // Manejar valores NULL
            if ($value === null) {
                $valores[] = "$key = NULL";
            } else {
                $valores[] = "$key = '" . self::$conexion->escape_string($value) . "'";
            }
        }

        // Consulta SQL
        $filas =  join(', ', $valores); // Crear un string a partir de las llaves y valores del arreglo
        $id_sanitizado = self::$conexion->escape_string($this->id); // Escapar el id para evitar inyección SQL

        $query = "UPDATE " . static::$tabla . " SET $filas WHERE id = $id_sanitizado LIMIT 1";

        // Actualizar BD
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    // Eliminar un registro por su ID
    public function eliminar() {
        // Escapar el id para evitar inyección SQL
        $id_sanitizado = self::$conexion->escape_string($this->id);

        $query = "DELETE FROM " . static::$tabla . " WHERE id = $id_sanitizado LIMIT 1";
        $resultado = self::$conexion->query($query);
        return $resultado;
    }

    // Subida de archivos
    public function setImagen($imagen) {
        // Verificar si la propiedad ya tiene un ID (indica que es una actualización)
        if(!is_null($this->id)) {
            $this->borrarImagen();
        }

        // Asignar el nuevo nombre de la imagen
        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    // Elimina el archivo
    public function borrarImagen() {
        // Verificar si el archivo existe y es un archivo real 
        $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
        if($existeArchivo) {
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }
    
    public static function totalCondiciones($condiciones = []) {
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla;
        
        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(' AND ', $condiciones);
        }
        
        $resultado = self::$conexion->query($query);
        $total = $resultado->fetch_assoc();
        return (int)$total['total'];
    }
    

    public static function metodoSQL($params = []) {
        $default = [
            'condiciones' => [],
            'orden' => 'id ASC',
            'limite' => null,
            'offset' => null
        ];
        
        $params = array_merge($default, $params);
    
        $query = "SELECT * FROM " . static::$tabla;
        
        // Construir condiciones
        if (!empty($params['condiciones'])) {
            $query .= " WHERE " . implode(' AND ', $params['condiciones']);
        }
        
        // Orden
        $query .= " ORDER BY " . $params['orden'];
        
        // Límite y offset
        if ($params['limite']) {
            $query .= " LIMIT " . $params['limite'];
            if ($params['offset']) {
                $query .= " OFFSET " . $params['offset'];
            }
        }
    
        return self::consultarSQL($query);
    }

    // Buscar un termino en la base de datos
    public static function buscar($termino) {
        $condiciones = [];
        $termino = self::$conexion->escape_string($termino);
        
        // Verificar si el modelo tiene columnas de búsqueda definidas
        if (!isset(static::$buscarColumns) || empty(static::$buscarColumns)) {
            return $condiciones;
        }
        
        // Generar condiciones de búsqueda
        $buscarConditions = [];
        foreach (static::$buscarColumns as $columna) {
            $buscarConditions[] = "$columna LIKE '%$termino%'";
        }
        
        $condiciones[] = "(" . implode(' OR ', $buscarConditions) . ")";
        
        return $condiciones;
    }

    public static function max($columna, $condiciones = []) {
        $query = "SELECT MAX($columna) as max FROM " . static::$tabla;
        
        if (!empty($condiciones)) {
            $where = [];
            foreach ($condiciones as $key => $value) {
                $where[] = "$key = '" . self::$conexion->escape_string($value) . "'";
            }
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $resultado = self::$conexion->query($query);
        $max = $resultado->fetch_assoc();
        return $max['max'] ?? 0;
    }     
}