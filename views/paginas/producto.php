    <main class="contenedor seccion mb-10 ">
        <div class="layout">
            <!-- Barra lateral de categorías -->
            <aside class="barra-lateral">
                <h2>Categorías</h2>

                <nav>
                    <ul class="lista">
                        <?php foreach ($categorias as $categoria): ?>
                        <li class="lista-item">
                            <div class="lista-boton <?= $categoriaId == $categoria->id ? 'activo' : '' ?>">
                                <a href="/productos?categoria=<?= $categoria->id ?>">
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
                                    <a href="/productos?subcategoria=<?= $subcategoria->id ?>"
                                    class="<?= $subcategoriaId == $subcategoria->id ? 'activo' : '' ?>">
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
                <!-- Barra de filtros y búsqueda -->
                <div class="barra-superior">
                    <div class="filtros">
                        <select id="categoria" name="categoria">
                            <option value="popularidad">Ordenar por popularidad</option>
                            <option value="calificacion">Ordenar por calificación media</option>
                            <option value="ultimas">Ordenar por las últimas</option>
                            <option value="precio">Ordenar por precio</option>
                        </select>
                    </div>
            
                    <div class="busqueda">
                        <input type="text" placeholder="Introduce aquí tu búsqueda...">
                        <button type="submit">
                            <img src="/build/img/icon_search-grey.svg" alt="Icono de busqueda">
                        </button>
                    </div>
                </div>
            
                <div class="contenedor-producto">
                    <div class="producto-imagenes">
                        <div class="contenedor-imagenes">
                            <div class="imagen-principal">
                                <?php if(!empty($producto->imagenes)): ?>
                                <img id="imagenGrande" src="/img/productos/<?= $producto->imagenes[0]->url ?>.webp" 
                                    alt="<?= $producto->nombre ?>">
                                <?php else: ?>
                                <img id="imagenGrande" src="/img/productos/default.webp" alt="Producto sin imagen">
                                <?php endif; ?>
                            </div>
                            
                            <?php if(count($producto->imagenes) > 1): ?>
                                <div class="miniaturas">
                                    <?php foreach ($producto->imagenes as $index => $imagen): ?>
                                    <img class="miniatura <?= $index === 0 ? 'active' : '' ?>" 
                                        src="/img/productos/<?= $imagen->url ?>.webp" 
                                        onclick="cambiarImagen(this, <?= $index ?>)">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="producto-info">
                        <h3 class="titulo"><?= $producto->nombre ?></h3>
                        <p class="descripcion"><?= $producto->descripcion ?></p>
                        
                        <?php foreach ($producto->atributos as $nombre => $atributo): ?>
                        <div class="atributo">
                            <h4><?= $nombre ?></h4>
                            <div class="valores">
                                <?= implode(', ', $atributo['valores']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if(!empty($producto->fichas)): ?>
                    <div class="contenedor-fichas">
                        <h4>Fichas Técnicas</h4>
                        <div class="fichas">
                            <?php foreach ($producto->fichas as $ficha): ?>
                            <a class="ficha" href="/fichas/<?= $ficha->url ?>" target="_blank">
                                <img src="/build/img/icon_pdf.svg" alt="Icono PDF">
                                <p><?= $producto->nombre ?> - <?= basename($ficha->url, '.pdf') ?></p>
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

function cambiarImagen(elemento, index) {
    const imagenGrande = document.getElementById('imagenGrande');
    const miniaturas = document.querySelectorAll('.miniatura');
    
    // Remover clase active de todas las miniaturas
    miniaturas.forEach(miniatura => miniatura.classList.remove('active'));
    
    // Agregar clase active a la miniatura clickeada
    elemento.classList.add('active');
    
    // Actualizar imagen principal
    imagenGrande.src = elemento.src;
    currentIndex = index;
}
</script>