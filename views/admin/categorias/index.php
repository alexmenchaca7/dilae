<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/categorias/crear">
        <i class="fa-solid fa-circle-plus"></i>
        Añadir Categoria
    </a>

    <a class="dashboard__boton" href="/admin/categorias/subcategorias/crear">
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
                            <form class="table__formulario" action="/admin/categorias/eliminar?id=<?php echo $categoria->id; ?>" method="POST">
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