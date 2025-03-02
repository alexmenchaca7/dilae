document.addEventListener("DOMContentLoaded", () => {

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
    const submenuContenedor = document.querySelector(".submenu-contenedor");

    // Verificamos que el contenedor y el botón existan antes de agregar eventos
    if (submenuContenedor) {
        const submenuBtn = submenuContenedor.querySelector(".submenu-btn");

        if (submenuBtn) {
            submenuBtn.addEventListener("click", function (event) {
                event.preventDefault();

                // Cierra cualquier otro submenú activo antes de abrir este
                document.querySelectorAll(".submenu-contenedor.activo").forEach((item) => {
                    if (item !== submenuContenedor) {
                        item.classList.remove("activo");
                    }
                });

                submenuContenedor.classList.toggle("activo");
            });
        }
    }

    /** SUB-SUBMENÚS DE NAVEGACION **/
    const subsubmenuBtns = document.querySelectorAll(".subsubmenu-btn");

    if (subsubmenuBtns.length > 0) {
        subsubmenuBtns.forEach((btn) => {
            btn.addEventListener("click", function (event) {
                event.preventDefault();
                const parentItem = this.closest(".submenu-item");

                if (parentItem) {
                    // Cierra otros sub-submenús antes de abrir este
                    document.querySelectorAll(".submenu-item.activo").forEach((item) => {
                        if (item !== parentItem) {
                            item.classList.remove("activo");
                        }
                    });

                    parentItem.classList.toggle("activo");
                }
            });
        });
    }

    /** CERRAR SUBMENÚS AL HACER CLIC FUERA **/
    document.addEventListener("click", function (event) {
        if (submenuContenedor) {
            const isClickInsideMenu = submenuContenedor.contains(event.target);

            if (!isClickInsideMenu) {
                // Cierra el submenú principal
                submenuContenedor.classList.remove("activo");

                // Cierra todos los sub-submenús abiertos
                document.querySelectorAll(".submenu-item").forEach((item) => {
                    item.classList.remove("activo");
                });
            }
        }
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
});