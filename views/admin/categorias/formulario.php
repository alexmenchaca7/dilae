<!-- Fieldset para los datos de la Categoría -->
<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Categoría</legend>
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            id="nombre"
            name="nombre"
            placeholder="Nombre Categoría"
            value="<?php echo htmlspecialchars($categoria->nombre ?? ''); ?>"
        >
    </div>
</fieldset>

<!-- Fieldset para Atributos (tags) -->
<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Atributos</legend>
    
    <!-- Selected Tags -->
    <div id="selected-tags" class="tags-container">
        <?php if(isset($atributosAsociados) && !empty($atributosAsociados)): ?>
            <?php foreach($atributosAsociados as $atributo): ?>
                <div class="tag selected" data-id="<?php echo $atributo->id; ?>">
                    <?php echo $atributo->nombre . ' (' . ucfirst($atributo->tipo) . ')'; ?>
                    <span class="remove-tag">&times;</span>
                    <input type="hidden" name="atributos[]" value="<?php echo $atributo->id; ?>">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Search Bar -->
    <div class="formulario__campo">
        <input 
            type="text" 
            id="search-attributes" 
            class="formulario__input" 
            placeholder="Buscar atributos..."
        >
    </div>

    <!-- Available Tags -->
    <div id="available-tags" class="tags-container">
        <?php if(isset($atributosDisponibles) && !empty($atributosDisponibles)): ?>
            <?php foreach($atributosDisponibles as $atributo): ?>
                <div class="tag" data-id="<?php echo $atributo->id; ?>">
                    <?php echo $atributo->nombre . ' (' . ucfirst($atributo->tipo) . ')'; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="texto-info">No hay más atributos disponibles</p>
        <?php endif; ?>
    </div>
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
        const searchInput = document.getElementById('search-attributes');
        const availableTags = document.getElementById('available-tags');
        const selectedTags = document.getElementById('selected-tags');

        // Función para filtrar atributos
        function filterAttributes(searchText) {
            const tags = availableTags.querySelectorAll('.tag');
            tags.forEach(tag => {
                const text = tag.textContent.toLowerCase();
                const isVisible = text.includes(searchText.toLowerCase());
                tag.style.display = isVisible ? 'flex' : 'none';
            });
        }

        // Búsqueda en tiempo real
        searchInput.addEventListener('input', function(e) {
            filterAttributes(e.target.value);
        });

        // Manejar clic en tags disponibles
        availableTags.addEventListener('click', function(e) {
            const tag = e.target.closest('.tag:not(.selected)');
            if (tag) {
                // Mover el elemento en lugar de clonar
                tag.classList.add('selected');
                
                const removeSpan = document.createElement('span');
                removeSpan.className = 'remove-tag';
                removeSpan.innerHTML = '&times;';
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'atributos[]';
                hiddenInput.value = tag.dataset.id;
                
                tag.appendChild(removeSpan);
                tag.appendChild(hiddenInput);
                selectedTags.appendChild(tag);
            }
        });

        // Eliminar tags seleccionados
        selectedTags.addEventListener('click', function(e) {
            const tag = e.target.closest('.tag.selected');
            
            if (tag) {
                // Eliminar elementos internos
                tag.querySelector('input')?.remove();
                tag.querySelector('.remove-tag')?.remove();
                
                // Quitar clase selected y mover a disponibles
                tag.classList.remove('selected');
                availableTags.appendChild(tag);
            }
        });
    });
</script>