<?php

namespace Model;

class Proyecto extends ActiveRecord {
    
    // Arreglo de columnas para identificar que forma van a tener los datos
    protected static $columnasDB = ['id', 'titulo', 'video_id'];
    protected static $tabla = 'proyectos';  


    public $id;
    public $titulo;
    public $video_id;


    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->titulo = $args['titulo'] ?? '';
        $this->video_id = $args['video_id'] ?? '';
    }
}