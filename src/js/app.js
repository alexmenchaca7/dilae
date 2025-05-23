document.addEventListener("DOMContentLoaded", () => {

    /** MENU RESPONSIVO **/
    const hamburguesa = document.getElementById('hamburguesa');
    const buscar = document.getElementById('buscar');
    const navegacion = document.getElementById('navegacion');

    hamburguesa.addEventListener('click', function() {
        navegacion.classList.toggle('activo');
        buscar.classList.toggle('activo');
    });


    /** BARRA DE NAVEGACION FIJA **/
    const barra = document.getElementById('barra');
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Verificamos si hay submenus abiertos
        const submenusAbiertos = document.querySelectorAll('.submenu-contenedor.activo, .submenu-item.activo').length > 0;

        if (scrollTop > lastScrollTop && 
            !navegacion.classList.contains('activo') && 
            !submenusAbiertos) { // Solo ocultar si no hay submenús abiertos
            barra.classList.add('hidden');
        } else {
            barra.classList.remove('hidden');
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Evita valores negativos
    });
    

    /** CONTADORES ANIMADOS DE LA PAGINA DE INICIO **/
    const contadores = document.querySelectorAll(".contador");
    if (contadores.length > 0) {
        const duracion = 750;
        const iniciarContador = (contador) => {
            const objetivo = +contador.dataset.target;
            let inicio = 0;
            const incremento = objetivo / (duracion / 16);

            const actualizarContador = () => {
                inicio += incremento;
                contador.innerText = `+${Math.floor(inicio)}`;
                if (inicio < objetivo) requestAnimationFrame(actualizarContador);
            };
            actualizarContador();
        };

        const observarContadores = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) iniciarContador(entrada.target);
            });
        }, { threshold: 0.6 });

        contadores.forEach((contador) => observarContadores.observe(contador));
    }




    /** DUPLICANDO LAS MARCAS PARA EFECTO INFINITO DEL CARRUSEL EN PAGINA DE INICIO**/
    const logosSlide = document.querySelector(".logos-slide");
    if (logosSlide) {
        document.querySelector('.carrusel-logos')?.appendChild(logosSlide.cloneNode(true));
    }




    /** SUBMENU PRODUCTOS DE NAVEGACION **/
    const submenuContenedores = document.querySelectorAll(".submenu-contenedor");

    submenuContenedores.forEach(submenuContenedor => {
        const submenuToggle = submenuContenedor.querySelector(".submenu-toggle");

        if (submenuToggle) {
            submenuToggle.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();

                // Guardar referencia al ícono antes de cualquier cambio
                const icon = this.querySelector('i');
                
                // Cierra cualquier otro submenú activo antes de abrir este
                document.querySelectorAll(".submenu-contenedor.activo").forEach((item) => {
                    if (item !== submenuContenedor) {
                        item.classList.remove("activo");
                        // Restablece la flecha del submenú cerrado
                        const otherIcon = item.querySelector('.submenu-btn i');
                        if (otherIcon) {
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                });

                // Alternar el estado activo del submenú contenedor
                const isActive = submenuContenedor.classList.toggle("activo");
                
                // Rotar el ícono basado en el nuevo estado
                icon.style.transform = isActive ? 'rotate(90deg)' : 'rotate(0deg)';
            });
        }
    });

    /** SUB-SUBMENÚS DE NAVEGACION **/
    const subsubmenuBtns = document.querySelectorAll(".subsubmenu-btn");

    if (subsubmenuBtns.length > 0) {
        subsubmenuBtns.forEach((btn) => {
            btn.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                const parentItem = this.closest(".submenu-item");
                const icon = this.querySelector('i');

                if (parentItem) {
                    // Cierra otros sub-submenús antes de abrir este
                    document.querySelectorAll(".submenu-item.activo").forEach((item) => {
                        if (item !== parentItem) {
                            item.classList.remove("activo");
                            // Restablece la flecha del sub-submenú cerrado
                            const otherIcon = item.querySelector('.subsubmenu-btn i');
                            if (otherIcon) {
                                otherIcon.style.transform = 'rotate(0deg)';
                            }
                        }
                    });

                    // Alternar estado y rotar ícono
                    const isActive = parentItem.classList.toggle("activo");
                    icon.style.transform = isActive ? 'rotate(90deg)' : 'rotate(0deg)';
                }
            });
        });
    }

    /** CERRAR SUBMENÚS AL HACER CLIC FUERA **/
    document.addEventListener("click", function (event) {
        submenuContenedores.forEach(submenuContenedor => {
            const isClickInsideMenu = submenuContenedor.contains(event.target);

            if (!isClickInsideMenu) {
                // Cierra el submenú principal
                submenuContenedor.classList.remove("activo");
                
                // Restablece la flecha del submenú principal
                const mainIcon = submenuContenedor.querySelector('.submenu-btn i');
                if (mainIcon) {
                    mainIcon.style.transform = 'rotate(0deg)';
                }

                // Cierra todos los sub-submenús abiertos
                document.querySelectorAll(".submenu-item").forEach((item) => {
                    item.classList.remove("activo");
                    // Restablece las flechas de los sub-submenús
                    const subIcon = item.querySelector('.subsubmenu-btn i');
                    if (subIcon) {
                        subIcon.style.transform = 'rotate(0deg)';
                    }
                });
            }
        });
    });
    




    /** LISTA DE ELEMENTOS EN EL SIDEBAR DE LA PAGINA DE PRODUCTOS **/
    const listElements = document.querySelectorAll('.lista-boton-clic');
    if (listElements.length > 0) {
        listElements.forEach(listElement => {
            listElement.addEventListener('click', () => {
                listElement.classList.toggle('arrow');
                let parentItem = listElement.closest('.lista-item');
                let menu = parentItem.querySelector('.lista-show');
                menu.style.height = (menu.clientHeight == 0) ? menu.scrollHeight + "px" : "0px";
            });
        });

        // Verificar si estamos en la página de producto
        const isProductPage = document.querySelector('.contenedor-producto') !== null;

        // Solo expandir si NO estamos en la página de producto
        if (!isProductPage) {
            // Expandir submenús activos al cargar
            document.querySelectorAll('.lista-item').forEach(item => {
                const boton = item.querySelector('.lista-boton');
                if (boton && boton.classList.contains('activo')) {
                    const menu = item.querySelector('.lista-show');
                    const chevron = item.querySelector('.lista-boton-clic');
                    if (menu && chevron) {
                        menu.style.height = menu.scrollHeight + 'px';
                        chevron.classList.add('arrow');
                    }
                }
            });
        }
    }



    /** CAMBIO DE IMÁGENES EN LA PÁGINA DE PRODUCTO **/
    const imagenGrande = document.getElementById("imagenGrande");
    const miniaturas = document.querySelectorAll(".miniatura");
    const prevBtn = document.getElementById("prev");
    const nextBtn = document.getElementById("next");

    if (imagenGrande && miniaturas.length > 0) {
        let indiceActual = 0;

        function cambiarImagen(elemento) {
            imagenGrande.src = elemento.src;
            miniaturas.forEach(img => img.classList.remove("active"));
            elemento.classList.add("active");
            indiceActual = Array.from(miniaturas).indexOf(elemento);
        }

        function cambiarConFlecha(direccion) {
            indiceActual += direccion;
            if (indiceActual < 0) indiceActual = miniaturas.length - 1;
            if (indiceActual >= miniaturas.length) indiceActual = 0;
            cambiarImagen(miniaturas[indiceActual]);
        }

        prevBtn?.addEventListener("click", () => cambiarConFlecha(-1));
        nextBtn?.addEventListener("click", () => cambiarConFlecha(1));

        miniaturas.forEach(img => {
            img.addEventListener("click", function () {
                cambiarImagen(this);
            });
        });
    }

    



    /** INSERTAR LÍNEA DIVISORIA EN PROYECTOS **/
    const proyectosContainer = document.querySelector(".proyectos");
    if (proyectosContainer && proyectosContainer.children.length > 0) {
        const proyectos = Array.from(proyectosContainer.querySelectorAll(".proyecto"));

        for (let i = 1; i < proyectos.length; i += 2) {
            const separador = document.createElement("div");
            separador.classList.add("separador");

            if (proyectos[i + 1]) {
                proyectosContainer.insertBefore(separador, proyectos[i + 1]);
            } else {
                proyectosContainer.appendChild(separador);
            }
        }
    }



    /* GRID MARCAS EN PAGINA DE INICIO */
    const marcas = [
        { src: 'build/img/500x500/ALP.jpg', alt: 'A.L.P.', link: 'https://alpadvantage.com/' },
        { src: 'build/img/500x500/ALCODM.jpg', alt: 'Alcodm', link: 'https://www.alcodm.com.mx/?v=0b98720dcb2c' },
        { src: 'build/img/500x500/BEGHELLI-LOGO-FONDO.png', alt: 'Beghelli', link: 'https://beghelli.com.mx/' },
        { src: 'build/img/500x500/Bticino-500x500.jpg', alt: 'Bticino', link: 'https://bticino.com.mx/' },
        { src: 'build/img/500x500/EATON.jpg', alt: 'Eaton', link: 'https://www.eaton.com/mx/es-mx.php' },
        { src: 'build/img/500x500/energain.jpg', alt: 'Energain', link: 'https://energain.com.mx/' },
        { src: 'build/img/500x500/HOLOPHANE-LOGO-FONDO-BLANCO.jpg', alt: 'Holophane', link: 'https://holophane.acuitybrands.com/' },
        { src: 'build/img/500x500/Hubbell.png', alt: 'Hubbell', link: 'https://www.hubbell.com/hubbellmexico/es-mx' },
        { src: 'build/img/500x500/led_mexico.jpg', alt: 'Led Mexico', link: 'https://www1.ledmexico.com.mx/' },
        { src: 'build/img/500x500/LITHONIA-FONDO-BLANCO.jpg', alt: 'Lithonia Lighting', link: 'https://lithonia.acuitybrands.com/' },
        { src: 'build/img/500x500/PHILIPS.png', alt: 'Philips', link: 'https://www.lighting.philips.com.mx/' },
        { src: 'build/img/500x500/rawelt.png', alt: 'Rawelt', link: 'https://www.rawelt.com.mx/' },
        { src: 'build/img/500x500/SIMON.jpg', alt: 'Simon', link: 'https://www.simonelectric.com/mx' },
        { src: 'build/img/500x500/construlita.png', alt: 'Construlita', link: 'https://construlita.com/' },
        { src: 'build/img/500x500/Havells_Logo.png', alt: 'Havells', link: 'https://havells.com/' },
        { src: 'build/img/500x500/leviton_logo.png', alt: 'Leviton', link: 'https://es.leviton.com/' },
        { src: 'build/img/500x500/magg.jpg', alt: 'Magg', link: 'https://www.magg.com.mx/' },
        { src: 'build/img/500x500/OSRAM-LOGO-FONDO-BLANCO.png', alt: 'Osram', link: 'https://www.osram.mx/' },
        { src: 'build/img/500x500/tecno-lite-material-electrico-catatumbo.png', alt: 'Tecno Lite', link: 'https://tecnolite.mx/' },
    ];
    
    const gridContainer = document.getElementById('grid-marcas');
    
    // Función para calcular la distribución de las marcas en 3 filas dinámicas
    function createBrandRows(brands) {
        const totalBrands = brands.length;
        const row1Count = Math.ceil(totalBrands * 0.4);  // Aproximadamente el 40% de marcas en la primera fila
        const row2Count = Math.ceil(totalBrands * 0.3);  // Aproximadamente el 30% de marcas en la segunda fila
        const row3Count = totalBrands - row1Count - row2Count; // El resto en la tercera fila

        const rows = [
            brands.slice(0, row1Count),  // Fila 1
            brands.slice(row1Count, row1Count + row2Count),  // Fila 2
            brands.slice(row1Count + row2Count, totalBrands),  // Fila 3
        ];

        // Crear las filas y agregar al contenedor
        rows.forEach(rowBrands => {
            const row = document.createElement('div');
            row.classList.add('marca-row');
            rowBrands.forEach(brand => {
                const brandDiv = document.createElement('div');
                brandDiv.classList.add('marca');
                brandDiv.innerHTML = `
                    <a target="_blank" href="${brand.link}">
                        <img loading="lazy" src="${brand.src}" alt="${brand.alt}">
                    </a>
                `;
                row.appendChild(brandDiv);
            });
            gridContainer.appendChild(row);
        });
    }

    createBrandRows(marcas);
});