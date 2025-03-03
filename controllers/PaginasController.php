<?php

namespace Controllers;
use MVC\Router;

class PaginasController {
    public static function index(Router $router) {

        $inicio = true;

        $router->render('paginas/index', [
            'inicio' => $inicio
        ]);
    }

    public static function nosotros(Router $router) {

        $titulo = 'Nosotros';

        $router->render('paginas/nosotros', [
            'titulo' => $titulo
        ]);
    }

    public static function proyectos(Router $router) {

        $titulo = 'Proyectos';

        $router->render('paginas/proyectos', [
            'titulo' => $titulo
        ]);
    }

    public static function blog(Router $router) {

        $titulo = 'Blog';

        $router->render('paginas/blog', [
            'titulo' => $titulo
        ]);
    }

    public static function contacto(Router $router) {

        $titulo = 'Contacto';

        $router->render('paginas/contacto', [
            'titulo' => $titulo
        ]);
    }
}