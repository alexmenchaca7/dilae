<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Crear subcategoria</legend>

    <!-- Nombre de la Subcategoria -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Subcategoria"
            value="<?php echo $subcategoria->nombre ?? ''; ?>"
        >
    </div>

    <!-- Selección de categoría -->
    <div class="formulario__campo">
        <label for="categoriaId" class="formulario__label">Categoría Principal</label>
        <select class="formulario__input" id="categoriaId" name="categoriaId">
            <option value="" disabled selected>Selecciona una Categoría</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria->id; ?>"
                    <?php echo isset($subcategoria->categoriaId) && $subcategoria->categoriaId == $categoria->id ? 'selected' : ''; ?>
                >
                    <?php echo $categoria->nombre; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>