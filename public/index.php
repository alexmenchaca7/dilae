<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\PaginasController;
use Controllers\DashboardController;
use Controllers\ProductosController;
use Controllers\ProyectosController;
use Controllers\CategoriasController;

$router = new Router();


// ZONA PRIVADA


// ZONA PUBLICA
$router->get('/', [PaginasController::class, 'index']);
$router->get('/nosotros', [PaginasController::class, 'nosotros']);
$router->get('/proyectos', [PaginasController::class, 'proyectos']);
$router->get('/blog', [PaginasController::class, 'blog']);
$router->get('/contacto', [PaginasController::class, 'contacto']);

$router->get('/politicas/privacidad', [PaginasController::class, 'privacidad']);



// AREA DE ADMINISTRACION
$router->get('/admin/dashboard', [DashboardController::class, 'index']);

$router->get('/admin/categorias', [CategoriasController::class, 'index']);
$router->get('/admin/categorias/crear', [CategoriasController::class, 'crear']);
$router->post('/admin/categorias/crear', [CategoriasController::class, 'crear']);
$router->get('/admin/categorias/subcategorias/crear', [CategoriasController::class, 'crearSubcategoria']);
$router->post('/admin/categorias/subcategorias/crear', [CategoriasController::class, 'crearSubcategoria']);
$router->get('/admin/categorias/editar', [CategoriasController::class, 'editar']);
$router->post('/admin/categorias/editar', [CategoriasController::class, 'editar']);


$router->get('/admin/productos', [ProductosController::class, 'index']);
$router->get('/admin/productos/crear', [ProductosController::class, 'crear']);

$router->get('/admin/proyectos', [ProyectosController::class, 'index']);



// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();