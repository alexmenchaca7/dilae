<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <form class="dashboard__busqueda" method="GET" action="/admin/atributos">
        <div class="campo-busqueda">
            <input 
                type="text" 
                name="busqueda" 
                class="input-busqueda" 
                placeholder="Buscar por nombre o tipo..."
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>"
            >
            <button type="submit" class="boton-busqueda">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <a class="dashboard__boton" href="/admin/atributos/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Atributo
    </a>
</div>

<div class="dashboard__contenedor">
    <?php if(!empty($atributos)): ?>
        <table class="table">
            <thead class="table__thead">
                <tr>
                    <th scope="col" class="table__th">Nombre</th>
                    <th scope="col" class="table__th">Tipo</th>
                    <th scope="col" class="table__th"></th>
                </tr>
            </thead>

            <tbody class="table__tbody">
                <?php foreach($atributos as $atributo): ?>
                    <tr class="table__tr">
                        <td class="table__td"><?php echo $atributo->nombre; ?></td>
                        <td class="table__td"><?php echo ucfirst($atributo->tipo); ?></td>
                        <td class="table__td--acciones">
                            <a class="table__accion table__accion--editar" href="/admin/atributos/editar?id=<?php echo $atributo->id; ?>">
                                <i class="fa-solid fa-user-pen"></i>
                                Editar
                            </a>
                            <form class="table__formulario" action="/admin/atributos/eliminar" method="POST" onsubmit="return openDeleteModal(event, <?php echo $atributo->id; ?>, 'atributo')">
                                <input type="hidden" name="id" value="<?php echo $atributo->id; ?>">
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
        <p class="t-align-center">No Hay Atributos Aún</p>
    <?php endif; ?>
</div>

<?php echo $paginacion; ?>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="modal">
    <div class="modal__content">
        <h3>Advertencia</h3>
        <p id="modalMessage">¿Estás seguro de que deseas eliminar este atributo?</p>
        <div class="modal__acciones">
            <button id="cancelDelete" class="modal__cancel">Cancelar</button>
            <button id="confirmDelete" class="modal__confirm">Eliminar</button>
        </div>
    </div>
</div>

<script>
    let currentForm = null;

    // Función para abrir el modal de confirmación de eliminación para atributos
    function openDeleteModal(event, id) {
        event.preventDefault();
        currentForm = event.target.closest('form');
        document.getElementById('deleteModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        return false;
    }

    // Cerrar el modal al hacer clic en "Cancelar"
    document.getElementById('cancelDelete').addEventListener('click', function() {
        document.getElementById('deleteModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    // Cerrar modal si se hace clic fuera del contenido
    document.getElementById('deleteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Confirmar eliminación y enviar el formulario
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
    });
</script>