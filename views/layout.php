<?php
    if(!isset($inicio)) {
        $inicio = false;
    }

    use Model\Categoria;
    use Model\Subcategoria;

    // Obtener todas las categorías con sus subcategorías
    $categoriasMenu = Categoria::ordenar('posicion', 'ASC');
    foreach ($categoriasMenu as $categoria) {
        $categoria->subcategorias = Subcategoria::metodoSQL([
            'condiciones' => ["categoriaId = '{$categoria->id}'"],
            'orden' => 'posicion ASC'
        ]);
    }

    // Inicializar variables (asumiendo que vienen del controlador)
    $categoria_slug = $_GET['categoria_slug'] ?? null;
    $subcategoria_slug = $_GET['subcategoria_slug'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dilae | <?php echo $inicio ? 'Inicio' : $titulo; ?></title>
    <link rel="stylesheet" href="/build/css/app.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="layout">
    <div class="layout__header">
        <header class="header">
            <div class="barra" id="barra">
                <div class="contenedor">
                    <a href="/">
                        <picture>
                            <source srcset="/build/img/logo.avif" type="image/avif">
                            <source srcset="/build/img/logo.webp" type="image/webp">
                            <source srcset="/build/img/logo.png" type="image/png">
                            <img loading="lazy" class="logo" src="/build/img/logo.png" alt="Logotipo de Dilae">
                        </picture>
                    </a>
    
                    <button class="hamburguesa" id="hamburguesa">
                        <i class="fa fa-bars"></i>
                    </button>
    
                    <nav class="navegacion" id="navegacion">
                        <a href="/nosotros">Nosotros</a>
                        <!-- Contenedor de Productos con Submenú -->
                        <div class="submenu-contenedor">
                            <div class="submenu-toggle">
                                <a href="/productos">Productos</a>
                                <button class="submenu-btn">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                            <?php if(is_array($categoriasMenu) || is_object($categoriasMenu)): ?>
                            <ul class="submenu">
                                <?php foreach ($categoriasMenu as $categoria): 
                                    $tiene_subactiva = false;
                                    if (!empty($categoria->subcategorias) && $subcategoria_slug) {
                                        foreach ($categoria->subcategorias as $subcategoria) {
                                            if ($subcategoria->slug === $subcategoria_slug) {
                                                $tiene_subactiva = true;
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <li class="submenu-item <?= 
                                    ($categoria_slug === $categoria->slug || $tiene_subactiva) ? 'activo' : '' ?>">
                                    <div class="submenu-toggle">
                                        <a href="/productos/<?= $categoria->slug ?>" 
                                        class="<?= ($categoria_slug === $categoria->slug) ? 'activo' : '' ?>">
                                            <?= $categoria->nombre ?>
                                        </a>
                                        <?php if(!empty($categoria->subcategorias)): ?>
                                        <button class="subsubmenu-btn">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($categoria->subcategorias)): ?>
                                    <ul class="subsubmenu">
                                        <?php foreach ($categoria->subcategorias as $subcategoria): ?>
                                        <li>
                                            <a href="/productos/<?= $categoria->slug ?>/<?= $subcategoria->slug ?>" 
                                            class="<?= ($subcategoria_slug === $subcategoria->slug) ? 'activo' : '' ?>">
                                                <?= $subcategoria->nombre ?>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
    
                        <a href="#">Paneles Solares</a>
                        <a href="/proyectos">Proyectos</a>
                        <a href="/blog">Blog</a>
                        <a href="/contacto">Contacto</a>
                    </nav>
    
                    <div class="buscar" id="buscar">
                        <button><i class="fa-solid fa-search"></i></button>
                    </div>
                </div>
            </div>
    
            <section class="hero <?php echo $inicio ? 'inicio' : ''; ?>">
                <div class="contenido-header contenedor">
                    <h1><?php echo $inicio ? 'Iluminación que transforma <span>cada proyecto</span>' : $titulo; ?></h1>
                    <?php if($inicio): ?>
                        <p>Hablando de iluminación, nosotros apreciamos los diferentes factores que deben tenerse en cuenta para
                        que la luz sea un protagonista en cada proyecto</p>
    
                        <!-- Contenedor del botón para controlar su tamaño -->
                        <div class="boton-contenedor">
                            <div class="logo-container">
                                <a class="boton-verde" href="/productos">Conoce nuestros productos</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </header>
    </div>

    <div class="layout__contenido">
        <?php echo $contenido; ?>
    </div>
    
    <div class="layout__footer">
        <footer class="footer">
            <div class="contenedor footer-contenedor">
                <!-- Sección del logo y derechos de autor -->
                <div class="footer-logo">
                    <div class="logo-container">
                        <a href="/">
                            <h2>DILAE</h2>
                        </a>
                    </div>
                    <p>Copyright © 2025 <a href="">DILAE</a></p>
                    <p>Todos los derechos reservados</p>
    
                    <div class="footer-social">
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.instagram.com">
                                <img loading="lazy" src="/build/img/icon_instagram.svg" alt="Instagram">
                            </a>
                        </div>
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank"
                                href="https://www.facebook.com/p/DILAE-100063075438310/">
                                <img loading="lazy" src="/build/img/icon_facebook.svg" alt="Facebook">
                            </a>
                        </div>
                        <div class="logo-container">
                            <a rel="noopener noreferrer" target="_blank" href="https://www.youtube.com/@dilaesadecv3250">
                                <img loading="lazy" src="/build/img/icon_youtube.svg" alt="YouTube">
                            </a>
                        </div>
                    </div>
                </div>
    
                <!-- Sección de enlaces -->
                <div class="footer-links">
                    <h3>Enlaces</h3>
                    <div class="logo-container">
                        <a href="/">Inicio</a>
                    </div>
                    <div class="logo-container">
                        <a href="/nosotros">Nosotros</a>
                    </div>
                    <div class="logo-container">
                        <a href="/productos">Productos</a>
                    </div>
                    <div class="logo-container">
                        <a href="#">Paneles Solares</a>
                    </div>
                    <div class="logo-container">
                        <a href="/proyectos">Proyectos</a>
                    </div>
                    <div class="logo-container">
                        <a href="/blog">Blog</a>
                    </div>
                    <div class="logo-container">
                        <a href="/contacto">Contacto</a>
                    </div>
                </div>
    
                <div class="footer-links">
                    <h3>Soporte</h3>
                    <div class="logo-container">
                        <a href="#">Términos de servicio</a>
                    </div>
                    <div class="logo-container">
                        <a href="/privacy">Política de privacidad</a>
                    </div>
                </div>
    
                <!-- Sección de suscripción -->
                <div class="footer-subscribe">
                    <h3>Mantente actualizado</h3>
                    <form action="/subscribe" method="POST">
                        <input type="email" name="email" placeholder="Tu correo electrónico" required>
                        <button type="submit">
                            <img loading="lazy" src="/build/img/icon_send.svg" alt="Enviar">
                        </button>
                    </form>
                </div>
            </div>
        </footer>
    </div>

    <script src="//code.tidio.co/ks94cvclexq9b0equflo49xrjn9oahg3.js" async></script>
    <script src="/build/js/app.js?v=<?= time() ?>"></script>
</body>
</html>