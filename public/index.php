<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\PaginasController;
$router = new Router();


// ZONA PRIVADA


// ZONA PUBLICA
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/proyectos', [PaginasController::class, 'proyectos']);
$router->get('/blog', [PaginasController::class, 'blog']);
$router->get('/contacto', [PaginasController::class, 'contacto']);

$router->get('/politicas/privacidad', [PaginasController::class, 'privacidad']);




// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();