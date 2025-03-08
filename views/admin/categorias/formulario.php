<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Categoria</legend>

    <!-- Nombre de la categoria -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Categoria"
            value="<?php echo $categoria->nombre ?? ''; ?>"
        >
    </div>
</fieldset>