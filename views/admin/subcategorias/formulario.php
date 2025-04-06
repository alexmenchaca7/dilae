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
        <?php foreach($atributosDisponibles as $atributo): ?>
            <div class="tag" data-id="<?php echo $atributo->id; ?>">
                <?php echo $atributo->nombre . ' (' . ucfirst($atributo->tipo) . ')'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('search-attributes');
        const availableTags = document.getElementById('available-tags');
        const selectedTags = document.getElementById('selected-tags');

        function filterAttributes(searchText) {
            const tags = availableTags.querySelectorAll('.tag');
            tags.forEach(tag => {
                const text = tag.textContent.toLowerCase();
                const isVisible = text.includes(searchText.toLowerCase());
                tag.style.display = isVisible ? 'flex' : 'none';
            });
        }

        searchInput.addEventListener('input', function(e) {
            filterAttributes(e.target.value);
        });

        availableTags.addEventListener('click', function(e) {
            const tag = e.target.closest('.tag:not(.selected)');
            if (tag) {
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

        selectedTags.addEventListener('click', function(e) {
            const tag = e.target.closest('.tag.selected');
            if (tag) {
                tag.querySelector('input')?.remove();
                tag.querySelector('.remove-tag')?.remove();
                tag.classList.remove('selected');
                availableTags.appendChild(tag);
            }
        });
    });
</script>
