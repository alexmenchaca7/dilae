<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/usuarios">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por nombre o email..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/admin/usuarios/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Usuario Administrador
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($usuarios)): ?>
        <table class="table">
            <thead class="table__thead">
                <tr>
                    <th scope="col" class="table__th">Nombre</th>
                    <th scope="col" class="table__th">Email</th>
                    <th scope="col" class="table__th">Confirmado</th>
                    <th scope="col" class="table__th"></th>
                </tr>
            </thead>

            <tbody class="table__tbody">
                <?php foreach($usuarios as $usuario): ?>
                    <tr class="table__tr">
                        <td class="table__td"><?php echo $usuario->nombre . ' ' . $usuario->apellido; ?></td>
                        <td class="table__td"><?php echo $usuario->email; ?></td>
                        <td class="table__td"><?php echo $usuario->confirmado ? 'Sí' : 'No'; ?></td>
                        <td class="table__td--acciones">
                            <a class="table__accion table__accion--editar" href="/admin/usuarios/editar?id=<?php echo $usuario->id; ?>">
                                <i class="fa-solid fa-user-pen"></i>
                                Editar
                            </a>
                            <form id="deleteForm" class="table__formulario" action="/admin/usuarios/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $usuario->id; ?>, 'usuario')">
                                <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">
                                
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
        <p class="t-align-center">No Hay Usuarios Aún</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="modal">
    <div class="modal__content">
        <h3>Advertencia</h3>
        <!-- Mensaje dinámico -->
        <p id="modalMessage">¡Al eliminar este usuario, perderá acceso al dashboard y todos los datos asociados serán eliminados! ¿Estás seguro de que deseas continuar?</p>
        <div class="modal__acciones">
            <button id="cancelDelete" class="modal__cancel">Cancelar</button>
            <button id="confirmDelete" class="modal__confirm">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let currentId = null;
    let currentForm = null; // Guardamos el formulario actual

    // Función para abrir el modal (para usuario)
    function openDeleteModal(event, id, type) {
        event.preventDefault(); // Previene que el formulario se envíe de inmediato
        currentId = id;
        currentForm = event.target.closest('form'); // Guardamos el formulario actual
        document.getElementById('deleteModal').style.display = 'block'; // Muestra el modal
        document.body.style.overflow = 'hidden'; // Deshabilita el desplazamiento

        // Cambia el mensaje dependiendo si es un usuario
        const message = '¡Al eliminar este usuario, perderá acceso al dashboard y todos los datos asociados serán eliminados! ¿Estás seguro de que deseas continuar?';

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
        // Enviar el formulario para eliminar el usuario
        if (currentForm) {
            currentForm.submit();
        }
    });
</script>
