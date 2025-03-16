    <main class="contenedor seccion mb-10 ">
        <div class="layout">
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
            
                <div class="contenedor-producto">
                    <div class="producto-imagenes">

                        <div class="contenedor-imagenes">
                            <!-- Imagen Principal -->
                            <div class="imagen-principal">
                                <img id="imagenGrande" src="build/img/productos/producto2-1.png" alt="Producto imagen principal">
                                <button id="prev" class="flecha prev">&lt;</button>
                                <button id="next" class="flecha next">&gt;</button>
                            </div>
    
                            <!-- Miniaturas -->
                            <div class="miniaturas">
                                <img class="miniatura active" src="build/img/productos/producto2-1.png" alt="Miniatura 1" onclick="cambiarImagen(this)">
                                <img class="miniatura" src="build/img/productos/producto2-2.png" alt="Miniatura 2" onclick="cambiarImagen(this)">
                                <img class="miniatura" src="build/img/productos/producto2-3.png" alt="Miniatura 3" onclick="cambiarImagen(this)">
                                <img class="miniatura" src="build/img/productos/producto2-4.png" alt="Miniatura 4" onclick="cambiarImagen(this)">
                            </div>
                        </div> <!-- contenedor-imagenes -->
                    </div> <!-- producto-imagenes -->

                    <div class="producto-info">
                        <h3 class="titulo">Reflector Industrial DL-CTN</h3>

                        <p class="descripcion">Reflector de alta potencia con Tecnología LED apto para uso en entornos exteriores, resistente a la corrosión, de fácil instalación, esmaltado en color negro.</p>

                        <div class="atributo">
                            <h4>Potencia</h4>

                            <div class="valores">
                                <p>100W</p>
                                <p>200W</p>
                                <p>300W</p>
                                <p>400W</p>
                                <p>500W</p>
                                <p>600W</p>
                            </div>
                        </div> <!-- atributo -->

                        <div class="atributo">
                            <h4>Temperatura</h4>

                            <div class="valores">
                                <p>6500K</p>
                            </div>
                        </div> <!-- atributo -->

                        <div class="atributo">
                            <h4>Garantía</h4>

                            <div class="valores">
                                <p>3 años</p>
                            </div>
                        </div> <!-- atributo -->

                        <div class="atributo">
                            <h4>Certificados</h4>

                            <div class="valores">
                                <img src="build/img/certificados/icono_nom.svg" alt="Certificado NOM">
                            </div>
                        </div> <!-- atributo -->
                    </div> <!-- producto-info -->

                    <div class="contenedor-fichas">
                        <h4>Fichas Técnicas</h4>

                        <div class="fichas">
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-100W</p>
                            </a>
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-200W</p>
                            </a>
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-300W</p>
                            </a>
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-400W</p>
                            </a>
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-500W</p>
                            </a>
                            <a class="ficha" href="#">
                                <img src="build/img/icon_pdf.svg" alt="Icono PDF">
                                <p>DL-CTN-600W</p>
                            </a>
                        </div> <!-- fichas -->
                    </div> <!-- contenedor-fichas -->
                </div> <!-- contenedor-producto -->
            </div>
        </div>
    </main>