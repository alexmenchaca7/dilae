<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Subcategoría</legend>

    <!-- Nombre de la Subcategoría -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Subcategoría"
            value="<?php echo htmlspecialchars($subcategoria->nombre ?? ''); ?>"
        >
    </div>

    <!-- Selección de Categoría Principal -->
    <div class="formulario__campo">
        <label for="categoriaId" class="formulario__label">Categoría Principal</label>
        <select class="formulario__input" id="categoriaId" name="categoriaId">
            <option value="" disabled selected>Selecciona una Categoría</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria->id; ?>"
                    <?php echo (isset($subcategoria->categoriaId) && $subcategoria->categoriaId == $categoria->id) ? 'selected' : ''; ?>>
                    <?php echo $categoria->nombre; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>

<!-- Fieldset para asignar Atributos (por selección) -->
<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Atributos</legend>
    <div id="atributos-container">
        <?php if(isset($atributosAsociados) && !empty($atributosAsociados)): ?>
            <?php foreach($atributosAsociados as $index => $atributo): ?>
                <div class="atributo-block">
                    <div class="formulario__campo">
                        <label for="atributo_select_<?php echo $index; ?>" class="formulario__label">Atributo</label>
                        <select 
                            class="formulario__input"
                            id="atributo_select_<?php echo $index; ?>"
                            name="atributos[]"
                        >
                            <option value="">Selecciona un atributo</option>
                            <?php foreach($atributos as $opcion): ?>
                                <option value="<?php echo $opcion->id; ?>"
                                    <?php echo ($opcion->id == $atributo->id) ? 'selected' : ''; ?>>
                                    <?php echo $opcion->nombre . ' (' . ucfirst($opcion->tipo) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="remove-atributo formulario__accion--secundario">
                        <i class="fa-solid fa-circle-xmark"></i>
                        Eliminar
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="agregar-atributo" class="formulario__accion">
        <i class="fa-solid fa-circle-plus"></i>
        Agregar Atributo
    </button>
</fieldset>

<!-- Template para nuevos bloques de atributos (oculto) -->
<template id="atributo-template">
    <div class="atributo-block">
        <div class="formulario__campo">
            <label class="formulario__label">Atributo</label>
            <select class="formulario__input" name="atributos[]">
                <option value="">Selecciona un atributo</option>
                <?php foreach($atributos as $opcion): ?>
                    <option value="<?php echo $opcion->id; ?>">
                        <?php echo $opcion->nombre . ' (' . ucfirst($opcion->tipo) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="remove-atributo formulario__accion--secundario">
            <i class="fa-solid fa-circle-xmark"></i>
            Eliminar
        </button>
    </div>
</template>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const agregarBtn = document.getElementById('agregar-atributo');
        const container = document.getElementById('atributos-container');
        const template = document.getElementById('atributo-template').content;

        // Al hacer clic, clona el bloque de atributos y lo agrega al contenedor.
        agregarBtn.addEventListener('click', function() {
            const clone = document.importNode(template, true);
            container.appendChild(clone);
        });

        // Delegación de eventos para eliminar un bloque de atributos.
        container.addEventListener('click', function(e) {
            if(e.target && e.target.closest("button.remove-atributo")) {
                e.target.closest('.atributo-block').remove();
            }
        });
    });
</script>
