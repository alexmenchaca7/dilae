<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Crear Atributo</legend>

    <!-- Nombre del Atributo -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Ej: Potencia, Voltaje"
            value="<?php echo $atributo->nombre ?? ''; ?>"
        >
    </div>

    <!-- Unidad del Atributo -->
    <div class="formulario__campo">
        <label for="unidad" class="formulario__label">Unidad</label>
        <input 
            type="text"
            class="formulario__input"
            id="unidad"
            name="unidad"
            placeholder="Ej: W, V, Lm"
            value="<?php echo $atributo->unidad ?? ''; ?>"
        >

        <label class="formulario__label checkbox-label">
            <input 
                type="checkbox" 
                name="espacio_unidad" 
                <?= isset($atributo->espacio_unidad) && $atributo->espacio_unidad ? 'checked' : '' ?>
            >
            ¿Agregar espacio antes de la unidad?
        </label>
    </div>

    <!-- Selección del Tipo de Atributo -->
    <div class="formulario__campo">
        <label for="tipo" class="formulario__label">Tipo</label>
        <select class="formulario__input" id="tipo" name="tipo">
            <option value="" disabled selected>Selecciona un Tipo</option>
            <option value="texto" <?php echo isset($atributo->tipo) && $atributo->tipo == 'texto' ? 'selected' : ''; ?>>Texto</option>
            <option value="numero" <?php echo isset($atributo->tipo) && $atributo->tipo == 'numero' ? 'selected' : ''; ?>>Número</option>
        </select>
    </div>
</fieldset>