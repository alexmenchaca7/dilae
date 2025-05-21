<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $producto): ?>
        <div class="producto">
            <div class="producto-contenido">
                <a href="/productos/<?= $producto->categoria->slug ?>/<?= $producto->subcategoria ? $producto->subcategoria->slug : 'sin-subcategoria' ?>/<?= $producto->slug ?>" class="producto-link">
                    <div class="imagen">
                        <?php if($producto->imagen_principal): ?>
                        <img loading="lazy" 
                            src="/img/productos/<?= $producto->imagen_principal->url ?>.webp" 
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
                        <p class="detalle-titulo"><?= htmlspecialchars($nombre ?? '') ?></p>
                        <p>
                            <?php 
                                $valores = $atributo['valores'];
                                $unidad = $atributo['unidad'];
                                $valoresConUnidad = array_map(function($valor) use ($unidad, $atributo) {
                                    // Verificar si es numérico
                                    if ($atributo['tipo'] === 'numero' && is_numeric($valor)) {
                                        $numero = (float)$valor;
                                        $decimales = ($numero == floor($numero)) ? 0 : 2;
                                        $valorFormateado = number_format($numero, $decimales, '.', ',');
                                        $texto = htmlspecialchars($valorFormateado ?? '');
                                        
                                        if ($unidad) {
                                            $espacio = (isset($atributo['espacio_unidad']) && $atributo['espacio_unidad'] == 1) ? ' ' : '';
                                            $texto .= $espacio . htmlspecialchars($unidad ?? '');
                                        }
                                        
                                        return $texto;
                                    } else {
                                        $texto = htmlspecialchars($valor ?? '');
                                        if ($unidad) {
                                            $espacio = (isset($atributo['espacio_unidad']) && $atributo['espacio_unidad'] == 1) ? ' ' : '';
                                            $texto .= $espacio . htmlspecialchars($unidad ?? '');
                                        }
                                        return $texto;
                                    }
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
<?php else: ?>
    <p class="text-center">No se encontraron productos con los criterios seleccionados.</p>
<?php endif; ?>