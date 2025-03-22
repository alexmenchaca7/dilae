<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <!-- Nombre del producto -->
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Producto"
            value="<?php echo $producto->nombre ?? ''; ?>"
        >
    </div>

    <!-- Descripción del producto -->
    <div class="formulario__campo">
        <label for="descripcion" class="formulario__label">Descripcion</label>
        <textarea class="formulario__input" name="descripcion" id="descripcion" rows="4"><?php echo $producto->descripcion ?? ''; ?></textarea>
    </div>

    <!-- Selección de la subcategoría -->
    <div class="formulario__campo">
        <label for="subcategoriaId" class="formulario__label">Subcategoría</label>
        <select class="formulario__input" id="subcategoriaId" name="subcategoriaId">
            <option value="" disabled selected>Selecciona una Subcategoría</option>

            <!-- Aquí irían las subcategorías dinámicamente cargadas desde la base de datos -->
            <?php foreach($subcategorias as $subcategoria): ?>
                <option value="<?php echo $subcategoria->id; ?>" 
                    <?php echo $producto->subcategoriaId === $subcategoria->id ? 'selected' : ''; ?>
                >
                    <?php echo $subcategoria->nombre; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>

<fieldset class="formulario__fieldset"> 
    <legend class="formulario__legend">Atributos del Producto</legend>

    <!-- Aquí puedes agregar campos para cada atributo -->
    <div class="formulario__campo">
        <label for="atributo">Potencia</label>
        <input
            class="formulario__input"
            type="text" 
            id="atributo" 
            name="atributo1">
    </div>

    <label for="atributo2">Atributo 2:</label>
    <input type="text" id="atributo2" name="atributo2">

    <!-- Si tienes atributos más complejos (como valores numéricos), puedes agregar campos adicionales -->
    <label for="atributo3">Atributo 3 (Numérico):</label>
    <input type="number" id="atributo3" name="atributo3" step="any">
</fieldset>