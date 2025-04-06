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
        <textarea 
            class="formulario__input"
            placeholder="Descripcion Producto"
            name="descripcion" 
            id="descripcion" 
            rows="4"
        ><?php echo $producto->descripcion ?? ''; ?></textarea>
    </div>

     <!-- Campo de Categoría -->
     <div class="formulario__campo">
        <label for="categoriaId" class="formulario__label">Categoría</label>
        <select 
            class="formulario__input" 
            id="categoriaId" 
            name="categoriaId" 
        >
            <option value="" disabled selected>Selecciona una Categoría</option>
            <?php foreach($categorias as $categoria): ?>
                <option 
                    value="<?php echo $categoria->id; ?>"
                    <?php echo ($producto->categoriaId ?? '') == $categoria->id ? 'selected' : ''; ?>
                >
                    <?php echo $categoria->nombre; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Campo de subcategoría -->
    <div class="formulario__campo" id="campo-subcategoria">
        <label for="subcategoriaId" class="formulario__label">Subcategoría</label>
        <select 
            class="formulario__input" 
            id="subcategoriaId" 
            name="subcategoriaId"
        >
            <option value="">-- Seleccione --</option>
            <?php if ($producto->categoriaId && !empty($subcategoriasPorCategoria[$producto->categoriaId])): ?>
                <?php foreach ($subcategoriasPorCategoria[$producto->categoriaId] as $subcategoria): ?>
                    <option 
                        value="<?= $subcategoria->id ?>"
                        <?= ($producto->subcategoriaId ?? '') == $subcategoria->id ? 'selected' : '' ?>
                    >
                        <?= $subcategoria->nombre ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" selected>Sin subcategoría</option>
            <?php endif; ?>
        </select>
    </div>
</fieldset>

<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Imágenes del Producto (Máximo 5)</legend>
    
    <div class="contenedor-imagenes" id="contenedor-imagenes">
        <!-- Las imágenes se agregarán dinámicamente aquí -->
    </div>

    <button type="button" class="formulario__accion" id="agregar-imagen">
        <i class="fas fa-plus"></i> Añadir imagen
    </button>
</fieldset>

<fieldset class="formulario__fieldset"> 
    <legend class="formulario__legend">Atributos del Producto</legend>
    <div id="atributos-container">
        <?php if (!empty($atributosDisponibles)): ?>
            <?php foreach($atributosDisponibles as $atributo): ?>
                <div class="formulario__campo" data-atributo-id="<?php echo $atributo->id; ?>">
                    <label><?php echo htmlspecialchars($atributo->nombre); ?></label>
                    <?php if($atributo->tipo === 'numero'): ?>
                        <input type="number" 
                               name="atributos[<?php echo $atributo->id; ?>][]" 
                               placeholder="<?php echo htmlspecialchars($atributo->nombre); ?>"
                               step="any"
                               value="<?= $_POST['atributos'][$atributo->id][0] ?? '' ?>">
                    <?php else: ?>
                        <input type="text" 
                               name="atributos[<?php echo $atributo->id; ?>][]" 
                               placeholder="<?php echo htmlspecialchars($atributo->nombre); ?>"
                               value="<?= $_POST['atributos'][$atributo->id][0] ?? '' ?>">
                    <?php endif; ?>
                    <button type="button" class="formulario__accion--secundario eliminar-atributo">Eliminar</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Selecciona una categoría para ver los atributos disponibles</p>
        <?php endif; ?>
    </div>
    
    <div class="formulario__campo">
        <label>Agregar nuevo atributo</label>
        <select id="nuevo-atributo-select" class="formulario__input" disabled>
            <option value="">-- Selecciona un atributo --</option>
            <?php foreach($todosAtributos as $atributo): ?>
                <option value="<?php echo htmlspecialchars($atributo->id); ?>">
                    <?php echo htmlspecialchars($atributo->nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="formulario__accion" id="agregar-atributo" disabled>
            <i class="fas fa-plus"></i> Añadir atributo
        </button>
    </div>
</fieldset>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------- Variables globales -------------
    const subcategoriasPorCategoria = <?php echo json_encode($subcategoriasPorCategoria); ?>;
    const relacionesAtributos = <?php echo json_encode($relacionesAtributos); ?>;
    const todosAtributos = <?php echo json_encode($todosAtributos); ?>;
    
    const categoriaSelect = document.getElementById('categoriaId');
    const subcategoriaSelect = document.getElementById('subcategoriaId');
    const atributosContainer = document.getElementById('atributos-container');
    const nuevoAtributoSelect = document.getElementById('nuevo-atributo-select');
    const btnAgregarAtributo = document.getElementById('agregar-atributo');
    const contenedorImagenes = document.getElementById('contenedor-imagenes');
    const btnAgregarImagen = document.getElementById('agregar-imagen');
    
    let imageCount = 0;
    const maxImages = 5;
    let atributosPermitidos = [];

    // ------------- Funciones para subcategorías -------------
    function cargarSubcategorias() {
        const categoriaId = categoriaSelect.value;
        subcategoriaSelect.innerHTML = '<option value="">-- Seleccione --</option>';
        
        if (categoriaId && subcategoriasPorCategoria[categoriaId]) {
            subcategoriasPorCategoria[categoriaId].forEach(subcategoria => {
                const option = new Option(subcategoria.nombre, subcategoria.id);
                subcategoriaSelect.add(option);
            });
            subcategoriaSelect.disabled = false;
        } else {
            subcategoriaSelect.innerHTML = '<option value="" selected>Sin subcategoría</option>';
            subcategoriaSelect.disabled = true;
        }
        
        cargarAtributosDisponibles();
        actualizarSelectAtributos();
    }

    // ------------- Funciones para atributos -------------
    function cargarAtributosDisponibles() {
        const categoriaId = categoriaSelect.value;
        const subcategoriaId = subcategoriaSelect.value;
        
        // Determinar si la categoría tiene subcategorías
        const tieneSubcategorias = categoriaId && subcategoriasPorCategoria[categoriaId]?.length > 0;
        
        // Lógica para atributos permitidos
        atributosPermitidos = [];
        
        if (tieneSubcategorias) {
            // Cargar atributos de la subcategoría (si está seleccionada)
            if (subcategoriaId) {
                const subcatAtributos = relacionesAtributos.subcategorias[subcategoriaId] || [];
                atributosPermitidos = subcatAtributos.map(id => 
                    todosAtributos.find(a => a.id == id)
                );
            }
        } else {
            // Cargar atributos de la categoría
            const catAtributos = relacionesAtributos.categorias[categoriaId] || [];
            atributosPermitidos = catAtributos.map(id => 
                todosAtributos.find(a => a.id == id)
            );
        }
        
        actualizarSelectAtributos();
        actualizarAtributosEnFormulario();
    }


    function actualizarSelectAtributos() {
        nuevoAtributoSelect.innerHTML = '<option value="">-- Selecciona un atributo --</option>';
        
        atributosPermitidos.forEach(atributo => {
            const option = document.createElement('option');
            option.value = atributo.id;
            option.textContent = atributo.nombre;
            nuevoAtributoSelect.appendChild(option);
        });
    }

    function actualizarAtributosEnFormulario() {
        // Limpiar atributos actuales
        atributosContainer.innerHTML = '';
        
        // Agregar los nuevos atributos permitidos
        atributosPermitidos.forEach(atributo => {
            const div = document.createElement('div');
            div.className = 'formulario__campo';
            div.dataset.atributoId = atributo.id;
            div.innerHTML = `
                <label>${atributo.nombre}</label>
                ${atributo.tipo === 'numero' ? 
                    `<input type="number" 
                            name="atributos[${atributo.id}][]" 
                            placeholder="${atributo.nombre}"
                            step="any">` : 
                    `<input type="text" 
                            name="atributos[${atributo.id}][]" 
                            placeholder="${atributo.nombre}">`
                }
                <button type="button" class="formulario__accion--secundario eliminar-atributo">Eliminar</button>
            `;
            atributosContainer.appendChild(div);
        });
    }

    function agregarAtributo() {
        const atributoId = nuevoAtributoSelect.value;
        if (!atributoId) return;

        // Buscar el atributo en los permitidos
        const atributo = atributosPermitidos.find(a => a.id == atributoId);
        if (!atributo) return;

        // Obtener valores previos del atributo
        const valoresPrevios = <?= json_encode($_POST['atributos'] ?? []) ?>;
        const valores = valoresPrevios[atributoId] || [''];

        // Crear campos para cada valor
        valores.forEach(valor => {
            const nuevoCampo = document.createElement('div');
            nuevoCampo.className = 'formulario__campo';
            nuevoCampo.dataset.atributoId = atributo.id;
            
            nuevoCampo.innerHTML = `
                <label>${atributo.nombre}</label>
                ${atributo.tipo === 'numero' ? 
                    `<input type="number" name="atributos[${atributo.id}][]" 
                            placeholder="${atributo.nombre}" step="any"
                            value="${valor}">` : 
                    `<input type="text" name="atributos[${atributo.id}][]" 
                            placeholder="${atributo.nombre}"
                            value="${valor}">`
                }
                <button type="button" class="formulario__accion--secundario eliminar-atributo">Eliminar</button>
            `;

            atributosContainer.appendChild(nuevoCampo);
        });
        
        nuevoAtributoSelect.value = '';
    }

    // ------------- Funciones para imágenes -------------
    function crearNuevaImagen() {
        if (imageCount >= maxImages) {
            alert('Máximo de imágenes alcanzado');
            return;
        }
        
        imageCount++;
        const nuevoContenedor = document.createElement('div');
        nuevoContenedor.className = 'formulario__campo contenedor-imagen';
        nuevoContenedor.dataset.index = imageCount;
        
        nuevoContenedor.innerHTML = `
            <div class="contenedor-imagen-preview">
                <div class="imagen-preview" id="imagenPreview${imageCount}">
                    <span class="imagen-placeholder">+</span>
                    <input 
                        type="file"
                        class="imagen-input"
                        name="imagenes_${imageCount}[]" 
                        accept="image/*"
                        style="display: none;"
                    >
                </div>
                <button type="button" class="formulario__accion--secundario eliminar-imagen">Eliminar</button>
            </div>
        `;

        contenedorImagenes.appendChild(nuevoContenedor);
        
        // Configurar eventos
        const previewElement = document.getElementById(`imagenPreview${imageCount}`);
        const inputFile = nuevoContenedor.querySelector('input[type="file"]');
        const btnEliminar = nuevoContenedor.querySelector('.eliminar-imagen');
        
        inputFile.addEventListener('change', previewImage);
        btnEliminar.addEventListener('click', removeImage);
        previewElement.addEventListener('click', () => inputFile.click());
    }

    function previewImage(e) {
        const input = e.target;
        const preview = input.closest('.contenedor-imagen-preview').querySelector('.imagen-preview');
        const placeholder = preview.querySelector('.imagen-placeholder');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let img = preview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.classList.add('imagen-cargada');
                    preview.insertBefore(img, placeholder);
                }
                img.src = e.target.result;
                img.alt = "Preview";
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage(e) {
        const button = e.target;
        const contenedor = button.closest('.formulario__campo');
        contenedor.remove();
        
        // Reindexar las imágenes restantes
        const imagenes = document.querySelectorAll('.contenedor-imagen');
        imagenes.forEach((img, index) => {
            img.dataset.index = index + 1;
            const input = img.querySelector('input[type="file"]');
            input.name = `imagenes_${index + 1}[]`;
        });
        
        imageCount = imagenes.length;
    }

    // ------------- Event Listeners -------------
    categoriaSelect.addEventListener('change', cargarSubcategorias);
    subcategoriaSelect.addEventListener('change', function() {
        cargarAtributosDisponibles();
        actualizarSelectAtributos();
    });
    btnAgregarAtributo.addEventListener('click', agregarAtributo);
    btnAgregarImagen.addEventListener('click', crearNuevaImagen);
    
    // Delegación de eventos
    atributosContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-atributo')) {
            e.target.closest('.formulario__campo').remove();
        }
    });
    
    contenedorImagenes.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-imagen')) {
            removeImage(e);
        }
    });

    // ------------- Inicialización -------------
    if (categoriaSelect.value) {
        cargarSubcategorias();
    }
    
    // Cargar atributos iniciales desde PHP
    const atributosIniciales = <?php echo json_encode($atributosDisponibles); ?>;
    if (atributosIniciales.length > 0) {
        atributosPermitidos = atributosIniciales;
        actualizarSelectAtributos();
    }
    
    crearNuevaImagen(); // Imagen inicial
});
</script>