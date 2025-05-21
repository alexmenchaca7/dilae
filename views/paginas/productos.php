<main class="contenedor seccion mb-10">
    <div class="layout-seccion">
        <aside class="barra-lateral">
            <h2>Categorías</h2>
            <nav>
                <ul class="lista">
                    <?php foreach ($categorias as $categoria): 
                        // Verificar si tiene subcategoría activa
                        $tiene_subactiva = false;
                        if (!empty($categoria->subcategorias) && isset($subcategoria_slug)) {
                            foreach ($categoria->subcategorias as $subcategoria) {
                                if ($subcategoria_slug == $subcategoria->slug) {
                                    $tiene_subactiva = true;
                                    break;
                                }
                            }
                        }       
                    ?>
                    <li class="lista-item">
                        <div class="lista-boton <?= 
                            ($categoria_slug == $categoria->slug || $tiene_subactiva) ? 'activo' : '' ?>">
                            <a href="/productos/<?= $categoria->slug ?>">
                                <?= $categoria->nombre ?>
                            </a>
                            <?php if(!empty($categoria->subcategorias)): ?>
                            <img class="lista-boton-clic" src="/build/img/chevron-right.svg" alt="Icono flecha">
                            <?php endif; ?>
                        </div>
                        
                        <?php if(!empty($categoria->subcategorias)): ?>
                        <ul class="lista-show">
                            <?php foreach ($categoria->subcategorias as $subcategoria): ?>
                            <li>
                                <a href="/productos/<?= $categoria->slug ?>/<?= $subcategoria->slug ?>" 
                                   class="<?= $subcategoria_slug == $subcategoria->slug ? 'activo' : '' ?>">
                                    <?= $subcategoria->nombre ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            
            <?php if (!empty($numericAttributes)): ?>
            <h2>Filtros</h2>
            <form method="GET" action="">
                <?php if ($categoria_slug): ?>
                    <input type="hidden" name="categoria_slug" value="<?= $categoria_slug ?>">
                <?php endif; ?>
                <?php if ($subcategoria_slug): ?>
                    <input type="hidden" name="subcategoria_slug" value="<?= $subcategoria_slug ?>">
                <?php endif; ?>
                
                <?php foreach ($numericAttributes as $attr): ?>
                    <div class="filtro-rango">
                        <label><?= htmlspecialchars($attr->nombre ?? '') ?><?= isset($attr->unidad) && $attr->unidad !== '' ? ' (' . htmlspecialchars($attr->unidad) . ')' : '' ?></label>
                        <div class="rango-inputs">
                            <input type="number" name="min_<?= $attr->id ?>" 
                                placeholder="Mín" 
                                value="<?= $_GET['min_' . $attr->id] ?? '' ?>">
                            <span>-</span>
                            <input type="number" name="max_<?= $attr->id ?>" 
                                placeholder="Máx" 
                                value="<?= $_GET['max_' . $attr->id] ?? '' ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="botones-filtros">
                    <button type="submit" class="boton">Aplicar Filtros</button>
                    <a href="<?= $cleanUrl ?>" class="boton boton-secundario">Limpiar Filtros</a>
                </div>
            </form>
            <?php endif; ?>
        </aside>

        <div class="layout-productos">
            <!-- Barra de filtros y búsqueda -->
            <div class="barra-superior">
                <div class="filtros">
                    <form method="GET" id="orden-form">
                        <?php if ($categoria_slug): ?>
                            <input type="hidden" name="categoria_slug" value="<?= $categoria_slug ?>">
                        <?php endif; ?>
                        <?php if ($subcategoria_slug): ?>
                            <input type="hidden" name="subcategoria_slug" value="<?= $subcategoria_slug ?>">
                        <?php endif; ?>
                        <?php if ($busqueda): ?>
                            <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '') ?>">
                        <?php endif; ?>
                        
                        <?php if (!empty($numericAttributes)): ?>
                            <?php foreach ($numericAttributes as $attr): 
                                $min = $_GET['min_' . $attr->id] ?? null;
                                $max = $_GET['max_' . $attr->id] ?? null;
                                if ($min !== null): ?>
                                    <input type="hidden" name="min_<?= $attr->id ?>" value="<?= $min ?>">
                                <?php endif;
                                if ($max !== null): ?>
                                    <input type="hidden" name="max_<?= $attr->id ?>" value="<?= $max ?>">
                                <?php endif;
                            endforeach; ?>
                        <?php endif; ?>
                        
                        <select name="orden" onchange="this.form.submit()">
                            <option value="nombre_asc" <?= ($_GET['orden'] ?? '') == 'nombre_asc' ? 'selected' : '' ?>>Nombre (A-Z)</option>
                            <option value="nombre_desc" <?= ($_GET['orden'] ?? '') == 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z-A)</option>
                        </select>
                    </form>
                </div>
        
                <div class="busqueda">
                    <form method="GET" action="" id="search-form">
                        <input 
                            type="text" 
                            name="busqueda"
                            id="busqueda-input" 
                            placeholder="Introduce aquí tu búsqueda..."
                            value="<?= htmlspecialchars($busqueda ?? '') ?>"
                        >
                        <button type="submit">
                            <img src="/build/img/icon_search-grey.svg" alt="Icono de búsqueda">
                        </button>
                    </form>
                </div>
            </div>
        
            <!-- Contenedor de productos -->
            <div class="productos">
                <?php include __DIR__ . '/_lista-productos.php'; ?>
            </div>
        </div>
    </div>

    <?php if(isset($paginacion)): ?>
        <div class="paginacion">
            <?php include __DIR__ . '/_paginacion.php'; ?>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener("DOMContentLoaded" , () => {
    const searchInput = document.getElementById('busqueda-input');
    const searchForm = document.getElementById('search-form'); // Para prevenir submit tradicional
    const productosContainer = document.querySelector('.productos');
    const paginacionContainer = document.querySelector('.paginacion');
    const ordenForm = document.getElementById('orden-form');

    let debounceTimer;

    // Función para manejar los toggles de detalles (usando delegación de eventos)
    function initializeDetailToggles(container) {
        container.addEventListener('click', function(e) {
            const button = e.target.closest('.toggle-detalles');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                
                const detalles = button.nextElementSibling;
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                
                detalles.classList.toggle('activo', !isExpanded);
                button.setAttribute('aria-expanded', !isExpanded);

                // Cambiar el icono
                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down', isExpanded);
                    icon.classList.toggle('fa-chevron-up', !isExpanded);
                }
            }
        });
    }

    // Inicializar toggles para la carga inicial
    if (productosContainer) {
        initializeDetailToggles(productosContainer);
    }

    async function fetchProductos(url) {
        try {
            // Mostrar un loader (opcional)
            if(productosContainer) productosContainer.innerHTML = '<p class="text-center">Cargando...</p>';
            if(paginacionContainer) paginacionContainer.innerHTML = '';


            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Para que el backend sepa que es AJAX
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (productosContainer) {
                productosContainer.innerHTML = data.productos_html;
            }
            if (paginacionContainer) {
                paginacionContainer.innerHTML = data.paginacion_html;
            }
            
            // Actualizar la URL en el navegador sin recargar
            history.pushState(null, '', url);

        } catch (error) {
            console.error('Error al cargar productos:', error);
            if(productosContainer) productosContainer.innerHTML = '<p class="text-center error">Error al cargar productos. Intenta de nuevo.</p>';
        }
    }

    function getUpdatedUrl() {
        const baseUrl = window.location.pathname; // Mantiene la categoría/subcategoría actual
        const params = new URLSearchParams();

        // Búsqueda
        if (searchInput.value.trim() !== '') {
            params.set('busqueda', searchInput.value.trim());
        }

        // Orden
        if (ordenForm && ordenForm.orden.value) {
            params.set('orden', ordenForm.orden.value);
        }
        
        // Mantener otros parámetros GET existentes que no sean 'page'
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.forEach((value, key) => {
            if (key !== 'busqueda' && key !== 'orden' && key !== 'page' && !key.startsWith('min_') && !key.startsWith('max_')) {
                 // Conservar slugs si están en query params (aunque normalmente están en el path)
                 // Conservar otros filtros que no gestionemos activamente aquí
                if (!params.has(key)) {
                    params.set(key, value);
                }
            }
        });
        
        // Filtros numéricos del sidebar (asumiendo que están en el GET actual)
        const filtroForm = document.querySelector('.barra-lateral form');
        if (filtroForm) {
            const filtroData = new FormData(filtroForm);
            filtroData.forEach((value, key) => {
                if (value.trim() !== '' && (key.startsWith('min_') || key.startsWith('max_'))) {
                     params.set(key, value);
                }
            });
        }


        const queryString = params.toString();
        return `${baseUrl}${queryString ? '?' + queryString : ''}`;
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const url = getUpdatedUrl();
                fetchProductos(url);
            }, 500); // Espera 500ms después de que el usuario deja de escribir
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Prevenir envío tradicional del formulario
            clearTimeout(debounceTimer); // Limpiar timer si estaba activo
            const url = getUpdatedUrl();
            fetchProductos(url);
        });
    }
    
    // Para la ordenación
    if (ordenForm) {
        ordenForm.addEventListener('change', () => {
            const url = getUpdatedUrl();
            fetchProductos(url);
        });
    }

    // Para la paginación (usando delegación de eventos en el contenedor)
    if (paginacionContainer) {
        paginacionContainer.addEventListener('click', (e) => {
            const link = e.target.closest('a'); // Busca el enlace más cercano al clic
            if (link && link.matches('.paginacion a')) { // Asegúrate que es un link de paginación
                e.preventDefault();
                const url = link.getAttribute('href');
                if (url) {
                    fetchProductos(url);
                }
            }
        });
    }
    
    // Para los filtros de la barra lateral (si el form tiene un ID 'filtros-laterales-form')
    const filtrosLateralesForm = document.querySelector('.barra-lateral form'); // Asume que solo hay un form
    if (filtrosLateralesForm) {
        filtrosLateralesForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const url = getUpdatedUrl(); // getUpdatedUrl ahora lee estos filtros
            fetchProductos(url);
        });

        // Para el botón "Limpiar Filtros"
        const limpiarFiltrosBtn = filtrosLateralesForm.querySelector('a.boton-secundario');
        if (limpiarFiltrosBtn) {
            limpiarFiltrosBtn.addEventListener('click', (e) => {
                e.preventDefault();
                // Resetear los inputs del formulario de filtros
                filtrosLateralesForm.querySelectorAll('input[type="number"]').forEach(input => input.value = '');
                
                const cleanUrl = limpiarFiltrosBtn.getAttribute('href'); // El PHP ya genera la URL limpia
                fetchProductos(cleanUrl);
            });
        }
    }

    // Manejar el botón "atrás/adelante" del navegador
    window.addEventListener('popstate', () => {
        // La URL ya cambió, solo necesitamos cargar el contenido para esa URL
        fetchProductos(window.location.href);
    });
});
</script>