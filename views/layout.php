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
                        <button class="global-search-toggle" id="global-search-toggle" aria-label="Abrir búsqueda">
                            <i class="fa-solid fa-search"></i>
                        </button>
                        <div class="global-search-input-area" id="global-search-input-area" style="display: none;">
                            <form id="global-search-form" action="#" method="GET" role="search">
                                <input type="text" id="global-search-input" name="term" placeholder="Buscar en todo el sitio..." autocomplete="off" aria-label="Campo de búsqueda global">
                                <button type="button" class="global-search-close" id="global-search-close" aria-label="Cerrar búsqueda">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </form>
                            <div id="global-search-results" class="global-search-results-list">
                                <!-- Los resultados dinámicos se insertarán aquí por JavaScript -->
                            </div>
                        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchContainer = document.getElementById('buscar');
            if (!searchContainer) return;

            const searchToggleBtn = document.getElementById('global-search-toggle');
            const searchInputArea = document.getElementById('global-search-input-area');
            const searchInput = document.getElementById('global-search-input');
            const searchCloseBtn = document.getElementById('global-search-close');
            const searchResultsList = document.getElementById('global-search-results');
            const searchForm = document.getElementById('global-search-form');

            let debounceTimer;
            let currentFocus = -1; // Para navegación con teclado

            function openSearchUI() {
                searchInputArea.style.display = 'block';
                searchToggleBtn.style.display = 'none';
                searchInput.focus();
                document.addEventListener('keydown', handleEscKey);
            }

            function closeSearchUI() {
                searchInputArea.style.display = 'none';
                searchToggleBtn.style.display = 'inline-block'; // o flex, según tu CSS original
                searchResultsList.innerHTML = '';
                searchInput.value = '';
                currentFocus = -1;
                document.removeEventListener('keydown', handleEscKey);
            }

            function handleEscKey(e) {
                if (e.key === 'Escape') {
                    closeSearchUI();
                }
            }

            searchToggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openSearchUI();
            });

            searchCloseBtn.addEventListener('click', closeSearchUI);

            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const term = searchInput.value.trim();
                
                // Limpiar resultados anteriores y resetear foco
                searchResultsList.innerHTML = '';
                currentFocus = -1;

                if (term.length < 2) { // Mínimo 2 caracteres para buscar
                    return;
                }

                searchResultsList.innerHTML = '<div class="search-loading">Buscando...</div>';

                debounceTimer = setTimeout(async () => {
                    if (searchInput.value.trim().length < 2) { // Doble chequeo por si se borró mientras esperaba el debounce
                        searchResultsList.innerHTML = '';
                        return;
                    }
                    try {
                        const response = await fetch(`/api/global_search.php?term=${encodeURIComponent(term)}`);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const results = await response.json();
                        renderResults(results);
                    } catch (error) {
                        console.error('Error al obtener resultados de búsqueda:', error);
                        searchResultsList.innerHTML = '<div class="search-no-results">Error al buscar. Inténtalo de nuevo.</div>';
                    }
                }, 300); // Espera 300ms después de que el usuario deja de escribir
            });

            function renderResults(results) {
                searchResultsList.innerHTML = ''; // Limpiar loader o resultados previos
                if (!results || results.length === 0) {
                    searchResultsList.innerHTML = '<div class="search-no-results">No se encontraron resultados.</div>';
                    return;
                }

                results.forEach(result => {
                    const link = document.createElement('a');
                    link.href = result.url;
                    // Usamos innerHTML para poder añadir el span del tipo fácilmente
                    link.innerHTML = `${escapeHTML(result.label)} <span class="search-result-item-type">(${escapeHTML(result.type)})</span>`;
                    
                    link.addEventListener('mousedown', (e) => { // Usar mousedown para que se dispare antes que el blur del input
                        e.preventDefault(); // Prevenir que el input pierda el foco inmediatamente y cierre los resultados
                        window.location.href = result.url; // Redirigir
                    });
                    searchResultsList.appendChild(link);
                });
            }

            // Función para escapar HTML simple y prevenir XSS básico en los resultados
            function escapeHTML(str) {
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }
            
            // Prevenir que el form haga un submit tradicional, que no es necesario aquí
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                // Si hay un elemento seleccionado por teclado, ir a él
                const activeItem = searchResultsList.querySelector('a.selected');
                if (activeItem) {
                    window.location.href = activeItem.href;
                } else if (searchResultsList.firstChild && searchResultsList.firstChild.href) {
                    // Opcional: si se presiona Enter sin seleccionar, ir al primer resultado
                    window.location.href = searchResultsList.firstChild.href;
                }
            });

            // Navegación con teclado
            searchInput.addEventListener('keydown', function(e) {
                const items = searchResultsList.getElementsByTagName('a');
                if (items.length === 0 && e.key !== 'Escape') return; // Si no hay items (y no es Escape), no hacer nada

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    setActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    setActive(items);
                } else if (e.key === 'Enter') {
                    // El submit del form ya lo maneja si hay un item activo
                    // Si no hay submit de form o por alguna razón no funciona:
                    if (currentFocus > -1 && items[currentFocus]) {
                        e.preventDefault(); // Prevenir doble submit si el form también actúa
                        items[currentFocus].dispatchEvent(new MouseEvent('mousedown', {bubbles: true})); // Simular mousedown para redirigir
                    }
                }
                // El Escape ya lo maneja el listener en document
            });

            function setActive(items) {
                if (!items || items.length === 0) return;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = items.length - 1;
                items[currentFocus].classList.add('selected');
                // Opcional: scroll para asegurar que el item seleccionado esté visible
                items[currentFocus].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function removeActive(items) {
                for (let i = 0; i < items.length; i++) {
                    items[i].classList.remove('selected');
                }
            }

            // Cerrar el desplegable de búsqueda si se hace clic fuera de él
            document.addEventListener('click', (e) => {
                // Si el área de input está visible Y el clic NO fue dentro del contenedor principal de búsqueda
                if (searchInputArea.style.display === 'block' && !searchContainer.contains(e.target)) {
                    closeSearchUI();
                }
            });
        });
    </script>
</body>
</html>