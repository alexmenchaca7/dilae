    <main class="contenedor seccion mb-10 ">
        <div class="layout">
            <!-- Barra lateral de categorías -->
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
            </aside>

            <div class="layout-productos">
                <div class="contenedor-producto">
                    <div class="producto-imagenes">
                        <div class="contenedor-imagenes">
                        <div class="imagen-principal">
                            <?php if(!empty($producto->imagenes)): ?>
                                <img id="imagenGrande" src="/img/productos/<?= $producto->imagenes[0]->url ?>.webp" 
                                    alt="<?= $producto->nombre ?>">
                                
                                <?php if(count($producto->imagenes) > 1): ?>
                                    <button class="flecha prev" onclick="cambiarImagenPrev()">‹</button>
                                    <button class="flecha next" onclick="cambiarImagenNext()">›</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <img id="imagenGrande" src="/img/productos/default.webp" alt="Producto sin imagen">
                            <?php endif; ?>
                        </div>
                            
                            <?php if(count($producto->imagenes) > 1): ?>
                                <div class="puntos-indicadores">
                                    <?php foreach ($producto->imagenes as $index => $imagen): ?>
                                    <button class="punto-indicador <?= $index === 0 ? 'active' : '' ?>" 
                                        onclick="cambiarImagen(<?= $index ?>)"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="producto-info">
                        <h3 class="titulo"><?= htmlspecialchars($producto->nombre) ?></h3>
                        <p class="descripcion"><?= htmlspecialchars($producto->descripcion) ?></p>

                        <div class="atributos">
                            <?php foreach ($producto->atributos as $nombre => $atributoData): ?>
                            <div class="atributo">
                                <h4><?= htmlspecialchars($nombre) ?></h4>
                                <div class="valores">
                                    <?php 
                                        $valores = $atributoData['valores'];
                                        $unidad = $atributoData['unidad'] ?? '';
                                        $tipo = $atributoData['tipo'];
                                        
                                        $valoresMostrar = array_map(function($valor) use ($unidad, $tipo, $atributoData) {
                                            if ($tipo === 'numero' && is_numeric($valor)) {
                                                $numero = (float)$valor;
                                                $decimales = ($numero == floor($numero)) ? 0 : 2;
                                                $valorFormateado = number_format($numero, $decimales, '.', ',');
                                                $texto = htmlspecialchars($valorFormateado);
                                                
                                                // Agregar unidad SI EXISTE (incluyendo espacio según espacio_unidad)
                                                if ($unidad) {
                                                    $espacio = (isset($atributoData['espacio_unidad']) && $atributoData['espacio_unidad'] == 1) ? ' ' : '';
                                                    $texto .= $espacio . htmlspecialchars($unidad);
                                                }
                                                
                                                return $texto;
                                            } else {
                                                $texto = htmlspecialchars((string)$valor);
                                                if ($unidad) {
                                                    $espacio = (isset($atributoData['espacio_unidad']) && $atributoData['espacio_unidad'] == 1) ? ' ' : '';
                                                    $texto .= $espacio . htmlspecialchars($unidad);
                                                }
                                                return $texto;
                                            }
                                        }, $valores);
                                        
                                        echo implode(', ', $valoresMostrar);
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if(!empty($producto->fichas)): ?>
                    <div class="contenedor-fichas">
                        <h4>Fichas Técnicas</h4>
                        <div class="fichas">
                            <?php foreach ($producto->fichas as $ficha): ?>
                            <a class="ficha" href="/fichas/<?= $ficha->url ?>" target="_blank">
                                <img src="/build/img/icon_pdf.svg" alt="Icono PDF">
                                <p><?= basename($ficha->url, '.pdf') ?></p>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>


<script>
let currentIndex = 0;
const imagenes = <?= json_encode(array_column($producto->imagenes, 'url')) ?>;

function cambiarImagen(index) {
    const imagenGrande = document.getElementById('imagenGrande');
    const puntos = document.querySelectorAll('.punto-indicador');
    
    if (index >= 0 && index < imagenes.length) {
        // Actualizar imagen principal
        imagenGrande.src = `/img/productos/${imagenes[index]}.webp`;
        currentIndex = index;
        
        // Actualizar puntos indicadores
        puntos.forEach(punto => punto.classList.remove('active'));
        puntos[index].classList.add('active');
    }
}

function cambiarImagenPrev() {
    currentIndex = (currentIndex - 1 + imagenes.length) % imagenes.length;
    cambiarImagen(currentIndex);
}

function cambiarImagenNext() {
    currentIndex = (currentIndex + 1) % imagenes.length;
    cambiarImagen(currentIndex);
}
</script>