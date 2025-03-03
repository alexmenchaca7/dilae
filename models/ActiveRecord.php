<?php

namespace Model;

// CLASE PADRE
class ActiveRecord {

    /** BASE DE DATOS **/
    // Static porque no require una nueva instancia, siempre son las mismas credenciales
    protected static $conexion; 

    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = [];
    protected static $tabla = '';


    /** ERRORES **/
    // Arreglo estatico con mensajes de errores
    protected static $errores = []; 


    // Definir la conexion a la base de datos
    public static function setDB($database) {
        self::$conexion = $database; // Self hace referencia a los atributos estaticos de esta misma clase
    }


    public function guardar() {
        if(!is_null($this->id)){ // is_null: Determina si una variable es null
            // Actualizar registro
            $this->actualizar();
        } else {
            // Crear nuevo registro
            $this->crear();
        }
    }


    // Crear un registro
    public function crear() {

        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $columnas = join(', ', array_keys($atributos)); // Crear un string a partir de las llaves del arreglo
        $filas = join("', '", array_values($atributos)); // Crear un string a partir de los valores del arreglo

        // Insertar los en la base de datos
        $query = "INSERT INTO " . static::$tabla . " ($columnas) VALUES ('$filas')";

        $resultado = self::$conexion->query($query); // Self hace referencia a los atributos estaticos de la misma clase

        // Redireccionar al usuario
        if ($resultado) {
            header('Location: /admin?resultado=1');
        }
    }


    // Actualizar un registro
    public function actualizar(){

        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "$key = '$value'";
        }

        $filas =  join(', ', $valores); // Crear un string a partir de las llaves y valores del arreglo
        $id_sanitizado = self::$conexion->escape_string($this->id); // Escapar el id para evitar inyección SQL

        $query = "UPDATE " . static::$tabla . " SET $filas WHERE id = $id_sanitizado LIMIT 1";

        $resultado = self::$conexion->query($query);

        // Redireccionar al usuario
        if ($resultado) {
            header('Location: /admin?resultado=2');
        }
    }


    // Eliminar un registro
    public function eliminar() {
        $id_sanitizado = self::$conexion->escape_string($this->id); // Escapar el id para evitar inyección SQL

        $query = "DELETE FROM " . static::$tabla . " WHERE id = $id_sanitizado LIMIT 1";

        $resultado = self::$conexion->query($query);

        // Redireccionar al usuario
        if ($resultado) {
            $this->borrarImagen();
            header('Location: /admin?resultado=3');
        }
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


    // Sanitizar los atributos de la BD
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];

        // Recorriendo el arreglo como un arreglo asociativo (tanto llave como valor)
        foreach($atributos as $key => $value) {
            $sanitizado[$key] = self::$conexion->escape_string($value);
        }

        return $sanitizado;
    }


    // Validación
    public static function getErrores() {
        return static::$errores;
    }


    public function validar() {
        // Validar formulario
        static::$errores = [];
        return static::$errores;
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


    // Lista todos los registros
    public static function all() {
        $query = "SELECT * FROM " . static::$tabla; // Static toma el valor de la clase que lo está llamando 

        $resultado = self::consultarSQL($query);

        return $resultado; // Retorna un arreglo de objetos 
    }


    // Obtiene determinado numero de registros
    public static function get($cantidad) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT $cantidad"; 

        $resultado = self::consultarSQL($query);

        return $resultado; // Retorna un arreglo de objetos 
    }


    // Busca un registro por su ID
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = $id";

        $resultado = self::consultarSQL($query);

        return array_shift($resultado); // Retorna el primer objeto del arreglo de objetos
    }


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


    // Sincroniza el objeto en memoria con los cambios realizados por el usuario
    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {  // Revisar de un objeto que una propiedad exista
                $this->$key = $value;
            }
        }
    }
}