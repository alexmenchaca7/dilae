<?php

require_once __DIR__ . '/../includes/app.php'; 

use MVC\Router;
use Controllers\AuthController;
use Controllers\PaginasController;
use Controllers\UsuariosController;
use Controllers\DashboardController;
use Controllers\ProductosController;
use Controllers\ProyectosController;
use Controllers\CategoriasController;
use Controllers\SubcategoriasController;

$router = new Router();

// Login
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Formulario de olvide mi password
$router->get('/olvide', [AuthController::class, 'olvide']);
$router->post('/olvide', [AuthController::class, 'olvide']);

// Colocar el nuevo password
$router->get('/reestablecer', [AuthController::class, 'reestablecer']);
$router->post('/reestablecer', [AuthController::class, 'reestablecer']);

// Confirmar cuenta
$router->get('/confirmar-cuenta', [AuthController::class, 'confirmar']);

// Establecer nuevo password
$router->get('/establecer-password', [AuthController::class, 'establecerPassword']);
$router->post('/establecer-password', [AuthController::class, 'establecerPassword']);

// Pagina de Inicio
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
$router->get('/admin/categorias/editar', [CategoriasController::class, 'editar']);
$router->post('/admin/categorias/editar', [CategoriasController::class, 'editar']);
$router->post('/admin/categorias/eliminar', [CategoriasController::class, 'eliminar']);

$router->get('/admin/subcategorias/crear', [SubcategoriasController::class, 'crear']);
$router->post('/admin/subcategorias/crear', [SubcategoriasController::class, 'crear']);
$router->get('/admin/subcategorias/editar', [SubcategoriasController::class, 'editar']);
$router->post('/admin/subcategorias/editar', [SubcategoriasController::class, 'editar']);
$router->post('/admin/subcategorias/eliminar', [SubcategoriasController::class, 'eliminar']);

$router->get('/admin/productos', [ProductosController::class, 'index']);
$router->get('/admin/productos/crear', [ProductosController::class, 'crear']);

$router->get('/admin/proyectos', [ProyectosController::class, 'index']);

$router->get('/admin/usuarios', [UsuariosController::class, 'index']);
$router->get('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->post('/admin/usuarios/crear', [UsuariosController::class, 'crear']);
$router->get('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/editar', [UsuariosController::class, 'editar']);
$router->post('/admin/usuarios/eliminar', [UsuariosController::class, 'eliminar']);

// Comprobar y validar que las rutas existan para asignarles las funciones del Controlador
$router->comprobarRutas();