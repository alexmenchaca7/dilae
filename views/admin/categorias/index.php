<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/categorias">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por categoria o subcategoria..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/admin/categorias/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Categoria
    </a>

    <a class="dashboard__boton" href="/admin/subcategorias/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Subcategoria
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($categorias)): ?>
        <table class="table">
            <thead class="table__thead">
                <tr>
                    <th scope="col" class="table__th">Categoria</th>
                    <th scope="col" class="table__th">Subcategorías</th>
                    <th scope="col" class="table__th"></th>
                </tr>
            </thead>

            <tbody class="table__tbody">
                <?php foreach($categorias as $categoria): ?>
                    <tr class="table__tr">
                        <td class="table__td"><?php echo $categoria->nombre; ?></td>

                        <!-- Mostrar las subcategorías de esta categoría -->
                        <td class="table__td">
                            <?php if(!empty($subcategoriasPorCategoria[$categoria->id])): ?>
                                <ul class="subcategorias-lista">
                                    <?php foreach($subcategoriasPorCategoria[$categoria->id] as $subcategoria): ?>
                                        <li>
                                            <?php echo $subcategoria->nombre; ?>

                                            <!-- Enlaces de acciones: Editar y Eliminar -->
                                            <div class="table__td--acciones-subcategorias">
                                                <a class="table__accion table__accion--editar" href="/admin/subcategorias/editar?id=<?php echo $subcategoria->id; ?>">
                                                    <i class="fa-solid fa-user-pen"></i> Editar
                                                </a>
                                                <form class="table__formulario" action="/admin/subcategorias/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $subcategoria->id; ?>, 'subcategoria')">
                                                    <input type="hidden" name="id" value="<?php echo $subcategoria->id; ?>">

                                                    <button class="table__accion table__accion--eliminar" type="submit">
                                                        <i class="fa-solid fa-circle-xmark"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>

                        <td class="table__td--acciones">
                            <a class="table__accion table__accion--editar" href="/admin/categorias/editar?id=<?php echo $categoria->id; ?>">
                                <i class="fa-solid fa-user-pen"></i>
                                Editar
                            </a>
                            <form id="deleteForm" class="table__formulario" action="/admin/categorias/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $categoria->id; ?>, 'categoria')">
                                <input type="hidden" name="id" value="<?php echo $categoria->id; ?>">
                                
                                <button class="table__accion table__accion--eliminar" type="submit">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="t-align-center">No Hay Categorías Aún</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="modal">
    <div class="modal__content">
        <h3>Advertencia</h3>
        <!-- Mensaje dinámico -->
        <p id="modalMessage">¡Al eliminar esta categoría, todas las subcategorías y productos asociados serán eliminados! ¿Estás seguro de que deseas continuar?</p>
        <div class="modal__acciones">
            <button id="cancelDelete" class="modal__cancel">Cancelar</button>
            <button id="confirmDelete" class="modal__confirm">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let currentId = null;
    let currentForm = null; // Guardamos el formulario actual

    // Función para abrir el modal (para categoría o subcategoría)
    function openDeleteModal(event, id, type) {
        event.preventDefault(); // Previene que el formulario se envíe de inmediato
        currentId = id;
        currentForm = event.target.closest('form'); // Guardamos el formulario actual
        document.getElementById('deleteModal').style.display = 'block'; // Muestra el modal
        document.body.style.overflow = 'hidden'; // Deshabilita el desplazamiento

        // Cambia el mensaje dependiendo si es una categoría o subcategoría
        const message = type === 'categoria' 
            ? '¡Al eliminar esta categoría, todas las subcategorías y productos asociados serán eliminados! ¿Estás seguro de que deseas continuar?' 
            : '¡Al eliminar esta subcategoría, todos los productos asociados a ella serán eliminados! ¿Estás seguro de que deseas continuar?';

        document.getElementById('modalMessage').textContent = message; // Actualiza el mensaje del modal
    }

    // Función para cerrar el modal
    document.getElementById('cancelDelete').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none'; // Cierra el modal
        document.body.style.overflow = 'auto'; // Habilita el desplazamiento de nuevo
    });

    // Cerrar modal al hacer clic fuera del modal (en el fondo)
    document.getElementById('deleteModal').addEventListener('click', (event) => {
        if (event.target === document.getElementById('deleteModal')) {
            document.getElementById('deleteModal').style.display = 'none'; // Cierra el modal
            document.body.style.overflow = 'auto'; // Habilita el desplazamiento de nuevo
        }
    });

    // Confirmar eliminación
    document.getElementById('confirmDelete').addEventListener('click', () => {
        // Enviar el formulario para eliminar la categoría o subcategoría
        if (currentForm) {
            currentForm.submit();
        }
    });
</script>