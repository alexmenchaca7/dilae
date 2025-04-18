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
                        <label><?= htmlspecialchars($attr->nombre) ?></label>
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
                <button type="submit" class="boton">Aplicar Filtros</button>
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
                            <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
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
                    <form method="GET" action="">
                        <input 
                            type="text" 
                            name="busqueda" 
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
                <?php foreach ($productos as $producto): ?>
                    <div class="producto">
                        <div class="producto-contenido">
                            <a href="/productos/<?= $producto->categoria->slug ?>/<?= $producto->subcategoria ? $producto->subcategoria->slug : 'sin-subcategoria' ?>/<?= $producto->slug ?>" class="producto-link">
                                <div class="imagen">
                                    <?php if($producto->imagen_principal): ?>
                                    <img loading="lazy" src="/img/productos/<?= $producto->imagen_principal->url ?>.webp" 
                                        alt="<?= $producto->nombre ?>">
                                    <?php else: ?>
                                    <img loading="lazy" src="/img/productos/default.webp" alt="Producto sin imagen">
                                    <?php endif; ?>
                                    <div class="linea"></div>
                                    <h3><?= $producto->nombre ?></h3>
                                </div>
                            </a>

                            <button class="toggle-detalles" aria-expanded="false">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>

                            <div class="detalles">
                                <?php foreach ($producto->atributos as $nombre => $atributo): ?>
                                <div class="detalle">
                                    <p class="detalle-titulo"><?= htmlspecialchars($nombre) ?></p>
                                    <p>
                                        <?php 
                                            $valores = $atributo['valores'];
                                            $unidad = $atributo['unidad'];
                                            $valoresConUnidad = array_map(function($valor) use ($unidad) {
                                                // Convertir a float y verificar decimales
                                                $numero = (float)$valor;
                                                
                                                // Formatear: eliminar .00 si es entero
                                                $valorFormateado = $numero == (int)$numero 
                                                                 ? (int)$numero 
                                                                 : number_format($numero, 2, '.', '');
                                                
                                                // Agregar unidad si existe
                                                return $unidad 
                                                       ? htmlspecialchars($valorFormateado) . ' ' . htmlspecialchars($unidad)
                                                       : htmlspecialchars($valorFormateado);
                                            }, $valores);
                                            
                                            echo implode(', ', $valoresConUnidad);
                                        ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded" , () => {
        document.querySelectorAll('.toggle-detalles').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const detalles = this.nextElementSibling;
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Alternar la clase 'activo' en los detalles
                detalles.classList.toggle('activo', !isExpanded);
                this.setAttribute('aria-expanded', !isExpanded);
            });
        });
    });
</script>