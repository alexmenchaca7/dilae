document.addEventListener("DOMContentLoaded", () => {

    /** CONTADORES ANIMADOS DE LA PAGINA DE INICIO **/
    const contadores = document.querySelectorAll(".contador");
    const duracion = 2000; // Duración total en milisegundos (2 segundos)

    const iniciarContador = (contador) => {
        const objetivo = +contador.dataset.target;
        let inicio = 0;
        const incremento = objetivo / (duracion / 16); // Ajusta el incremento basado en el tiempo total

        const actualizarContador = () => {
            inicio += incremento;
            if (inicio >= objetivo) {
                contador.innerText = `+${objetivo}`; // Muestra el valor final
            } else {
                contador.innerText = `+${Math.floor(inicio)}`;
                requestAnimationFrame(actualizarContador);
            }
        };

        actualizarContador();
    };

    // Detecta si los contadores están en pantalla y los inicia
    const observarContadores = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                // Reiniciar el contador a 0 antes de volver a animar
                entrada.target.innerText = "+0"; 
                iniciarContador(entrada.target);
            }
        });
    }, { threshold: 0.6 });

    contadores.forEach((contador) => observarContadores.observe(contador));




    /** DUPLICANDO LAS MARCAS PARA EFECTO INFINITO DEL CARRUSEL EN PAGINA DE INICIO**/
    let copy = document.querySelector(".logos-slide").cloneNode(true);
    document.querySelector('.carrusel-logos').appendChild(copy);




    /** SUBMENU PRODUCTOS DE NAVEGACION **/
    const submenuContenedor = document.querySelector(".submenu-contenedor");
    const submenuBtn = submenuContenedor.querySelector(".submenu-btn");

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

    /** SUB-SUBMENÚS DE NAVEGACION **/
    const subsubmenuBtns = document.querySelectorAll(".subsubmenu-btn");

    subsubmenuBtns.forEach((btn) => {
        btn.addEventListener("click", function (event) {
            event.preventDefault();
            const parentItem = this.closest(".submenu-item");

            // Cierra otros sub-submenús antes de abrir este
            document.querySelectorAll(".submenu-item.activo").forEach((item) => {
                if (item !== parentItem) {
                    item.classList.remove("activo");
                }
            });

            parentItem.classList.toggle("activo");
        });
    });

    /** CERRAR SUBMENÚS AL HACER CLIC FUERA **/
    document.addEventListener("click", function (event) {
        const isClickInsideMenu = submenuContenedor.contains(event.target);

        if (!isClickInsideMenu) {
            // Cierra el submenú principal
            submenuContenedor.classList.remove("activo");

            // Cierra todos los sub-submenús abiertos
            document.querySelectorAll(".submenu-item").forEach((item) => {
                item.classList.remove("activo");
            });
        }
    });
});