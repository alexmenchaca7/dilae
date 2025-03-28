    <main class="contenedor seccion mb-10 ">
        <div class="layout-seccion">
            <!-- Barra lateral de categorías -->
            <aside class="barra-lateral">
                <h2>Categorías</h2>

                <nav>
                    <ul class="lista">
                        <li class="lista-item">
                            <div class="lista-boton activo"> <!-- Activo si es la página actual -->
                                <a href="#">Iluminación</a>
                                <div class="icono">
                                    <img class="lista-boton-clic" src="build/img/chevron-right.svg" alt="Icono flecha">
                                </div>
                            </div>
                            <ul class="lista-show">
                                <li><a href="#">Reflectores</a></li>
                                <li><a href="#" class="activo">Campanas UFO</a></li>
                                <li><a href="#">Bañadores</a></li>
                                <li><a href="#">Panel LED</a></li>
                                <li><a href="#">Downlight</a></li>
                                <li><a href="#">Lamparas</a></li>
                                <li><a href="#">Tubos</a></li>
                                <li><a href="#">Decorativos</a></li>
                                <li><a href="#">Tiras y Mangueras</a></li>
                            </ul>
                        </li>
                        <li class="lista-item">
                            <div class="lista-boton">
                                <a href="#">Gabinetes</a>
                                <img class="lista-boton-clic" src="build/img/chevron-right.svg" alt="Icono flecha">
                            </div>
                            <ul class="lista-show">
                                <li><a href="#">Gabinetes Iluminación</a></li>
                                <li><a href="#">Gabinetes Eléctricos</a></li>
                                <li><a href="#">CCM Armarios de Distribución</a></li>
                                <li><a href="#">Ducto Porta Cable</a></li>
                            </ul>
                        </li>
                        <li class="lista-item">
                            <div class="lista-boton">
                                <a href="#">Vialidades</a>
                                <img class="lista-boton-clic" src="build/img/chevron-right.svg" alt="Icono flecha">
                            </div>
                            <ul class="lista-show">
                                <li><a href="#">Alumbrado Publico</a></li>
                                <li><a href="#">Luminarias Solares Calle</a></li>
                                <li><a href="#">Punta Poste</a></li>
                                <li><a href="#">Punta Poste Solar</a></li>
                                <li><a href="#">Urbanos</a></li>
                                <li><a href="#">Postes y Accesorios</a></li>
                            </ul>
                        </li>
                        <li class="lista-item">
                            <div class="lista-boton">
                                <a href="#">Ventiladores</a>
                            </div>
                        </li>
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
                            <img src="build/img/icon_search-grey.svg" alt="Icono de busqueda">
                        </button>
                    </div>
                </div>
            
                <!-- Contenedor de productos -->
                <div class="productos">
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                    <a class="producto" href="producto.php">
                        <div class="imagen">
                            <img loading="lazy" src="build/img/productos/producto1.png" alt="Producto 1">
                            
                            <!-- Línea divisoria -->
                            <div class="linea"></div>
                            
                            <h3>DL-CAM-200W5YUF</h3>
                        </div>
    
                        <!-- Detalles del producto -->
                        <div class="detalles">
                            <div class="detalle">
                                <p class="detalle-titulo">Potencias</p>
                                <p>120W</p>
                                <p>150W</p>
                                <p>200W</p>
                            </div>
                            <div class="detalle">
                                <p class="detalle-titulo">Temperatura</p>
                                <p>4000K</p>
                                <p>5300K</p>
                                <p>6500K</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </main>