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
        <?php foreach($imagenes as $imagen): ?>
            <div class="formulario__campo contenedor-imagen" data-existente="true">
                <div class="contenedor-imagen-preview">
                    <div class="imagen-preview">
                        <img src="/img/productos/<?= $imagen->url ?>.webp" alt="<?php echo $producto->nombre; ?>">
                        <input type="hidden" name="imagenes_existentes[]" value="<?= $imagen->id ?>">
                    </div>
                    <button type="button" class="formulario__accion--secundario eliminar-imagen">Eliminar</button>
                </div>
            </div>
        <?php endforeach; ?>
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
                <?php 
                    // Añadir verificación para evitar atributos duplicados
                    if (!isset($atributosProcesados[$atributo->id])): 
                        $atributosProcesados[$atributo->id] = true;
                ?>
                <div class="atributo-group" data-atributo-id="<?= $atributo->id ?>" data-renderizado-php="true">
                    <label class="formulario__label"><?= htmlspecialchars($atributo->nombre)?> (<?php echo $atributo->unidad; ?>)</label>
                    <div class="atributo-inputs">
                        <?php 
                            $valores = $atributosValores[$atributo->id] ?? [''];
                            foreach($valores as $valor): 
                        ?>
                        <div class="input-wrapper">
                            <input 
                                type="<?= $atributo->tipo === 'numero' ? 'number' : 'text' ?>" 
                                name="atributos[<?= $atributo->id ?>][]"
                                placeholder="<?= htmlspecialchars($atributo->nombre) ?>"
                                value="<?= htmlspecialchars($valor) ?>"
                                <?= $atributo->tipo === 'numero' ? 'step="any"' : '' ?>
                            >
                            <button type="button" class="eliminar-valor">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="agregar-valor">+ Agregar Valor</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="texto-info">Selecciona una categoría para ver los atributos disponibles</p>
        <?php endif; ?>
    </div>
</fieldset>

<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Fichas Técnicas (PDF)</legend>
    <div class="contenedor-fichas" id="contenedor-fichas">
        <?php foreach($fichas as $ficha): ?>
            <div class="formulario__campo contenedor-ficha" data-existente="true">
                <div class="ficha-preview">
                    <a href="/fichas/<?= $ficha->url ?>" target="_blank" class="ficha-existente">
                        <?= $ficha->url ?>
                    </a>
                    <input type="hidden" name="fichas_existentes[]" value="<?= $ficha->id ?>">
                    <button type="button" class="formulario__accion--secundario eliminar-ficha">Eliminar</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="formulario__accion" id="agregar-ficha">
        <i class="fas fa-plus"></i> Añadir ficha técnica
    </button>
</fieldset>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------- Variables globales -------------
    const subcategoriasPorCategoria = <?php echo json_encode($subcategoriasPorCategoria); ?>;
    const relacionesAtributos = <?php echo json_encode($relacionesAtributos); ?>;
    const todosAtributos = <?php echo json_encode($todosAtributos); ?>;
    const productoSubcategoriaId = <?= json_encode($producto->subcategoriaId ?? null) ?>;
    
    const categoriaSelect = document.getElementById('categoriaId');
    const subcategoriaSelect = document.getElementById('subcategoriaId');
    const atributosContainer = document.getElementById('atributos-container');

    const contenedorImagenes = document.getElementById('contenedor-imagenes');
    const btnAgregarImagen = document.getElementById('agregar-imagen');
    
    let imageCount = 0;
    const maxImages = 5;

    const contenedorFichas = document.getElementById('contenedor-fichas');
    const btnAgregarFicha = document.getElementById('agregar-ficha');
    
    let fichaCount = 0;
    const maxFichas = 5;

    // ------------- Funciones para subcategorías -------------
    function cargarSubcategorias(cargarAtributos = true) {
        const categoriaId = parseInt(categoriaSelect.value);
        subcategoriaSelect.innerHTML = '<option value="">-- Seleccione --</option>';
        
        if (categoriaId && subcategoriasPorCategoria[categoriaId]) {
            subcategoriasPorCategoria[categoriaId].forEach(subcategoria => {
                const option = new Option(subcategoria.nombre, subcategoria.id);
                if (subcategoria.id == productoSubcategoriaId) {
                    option.selected = true;
                }
                subcategoriaSelect.add(option);
            });
            subcategoriaSelect.disabled = false;
        } else {
            subcategoriaSelect.innerHTML = '<option value="" selected>Sin subcategoría</option>';
            subcategoriaSelect.disabled = true;
        }
        
        // Solo cargar atributos si el parámetro es true
        if (cargarAtributos) {
            cargarAtributosDisponibles();
        }
    }

    // ------------- Funciones para atributos -------------
    function cargarAtributosDisponibles() {
        // Limpiar solo los atributos dinámicos (no los renderizados por PHP)
        const atributosDinamicos = atributosContainer.querySelectorAll('.atributo-group[data-dinamico="true"]');
        atributosDinamicos.forEach(atributo => atributo.remove());

        const categoriaId = parseInt(categoriaSelect.value);
        const subcategoriaId = parseInt(subcategoriaSelect.value);
        
        let atributosIds = [];
        
        // Determinar si la categoría tiene subcategorías
        const tieneSubcategorias = subcategoriasPorCategoria[categoriaId]?.length > 0;

        if (tieneSubcategorias && subcategoriaId) {
            // Obtener de subcategoría
            atributosIds = relacionesAtributos.subcategorias[subcategoriaId] || [];
        } else if (!tieneSubcategorias) {
            // Obtener de categoría
            atributosIds = relacionesAtributos.categorias[categoriaId] || [];
        }

        // Filtrar atributos permitidos
        const atributosPermitidos = todosAtributos.filter(atributo => {
            return atributosIds.some(id => id === atributo.id);
        });

        actualizarAtributosEnFormulario(atributosPermitidos);
    }

    function actualizarAtributosEnFormulario(atributos) {
        // Limpiar todos los atributos dinámicos
        const atributosDinamicos = atributosContainer.querySelectorAll('.atributo-group[data-dinamico="true"]');
        atributosDinamicos.forEach(atributo => atributo.remove());

        if (atributos.length === 0) {
            atributosContainer.innerHTML = '<p class="texto-info">No hay atributos disponibles para esta selección</p>';
            return;
        }

        atributos.forEach(atributo => {
            const group = document.createElement('div');
            group.className = 'atributo-group';
            group.dataset.atributoId = atributo.id;
            group.dataset.dinamico = "true"; // Marcar como dinámico

            const label = document.createElement('label');
            label.className = 'formulario__label';
            label.textContent = atributo.nombre;
            group.appendChild(label);

            const inputsContainer = document.createElement('div');
            inputsContainer.className = 'atributo-inputs';

            // Input inicial
            const initialInput = createInput(atributo);
            inputsContainer.appendChild(initialInput);

            // Botón agregar
            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'agregar-valor';
            addButton.textContent = '+ Agregar Valor';
            addButton.addEventListener('click', () => {
                const newInput = createInput(atributo);
                inputsContainer.appendChild(newInput);
            });

            group.appendChild(inputsContainer);
            group.appendChild(addButton);
            atributosContainer.appendChild(group);
        });
    }

    function createInput(atributo) {
        const wrapper = document.createElement('div');
        wrapper.className = 'input-wrapper';

        const input = document.createElement('input');
        input.type = atributo.tipo === 'numero' ? 'number' : 'text';
        input.name = `atributos[${atributo.id}][]`;
        input.placeholder = atributo.nombre;
        input.step = atributo.tipo === 'numero' ? 'any' : '';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'eliminar-valor';
        removeBtn.textContent = '×';
        removeBtn.addEventListener('click', () => wrapper.remove());

        wrapper.appendChild(input);
        wrapper.appendChild(removeBtn);
        return wrapper;
    }

    // ------------- Funciones para imágenes -------------
    function crearNuevaImagen() {
        const totalImagenes = document.querySelectorAll('.contenedor-imagen:not([data-existente="true"])').length;
        if (totalImagenes >= maxImages) {
            alert('Máximo de imágenes alcanzado');
            return;
        }
        
        const nuevoContenedor = document.createElement('div');
        nuevoContenedor.className = 'formulario__campo contenedor-imagen';
        
        nuevoContenedor.innerHTML = `
            <div class="contenedor-imagen-preview">
                <div class="imagen-preview">
                    <span class="imagen-placeholder">+</span>
                    <input 
                        type="file"
                        class="imagen-input"
                        name="nuevas_imagenes[]"
                        accept="image/*"
                        style="display: none;"
                    >
                </div>
                <button type="button" class="formulario__accion--secundario eliminar-imagen">Eliminar</button>
            </div>
        `;

        contenedorImagenes.appendChild(nuevoContenedor);
        
        // Configurar eventos
        const previewElement = nuevoContenedor.querySelector('.imagen-preview');
        const inputFile = nuevoContenedor.querySelector('input[type="file"]');
        
        inputFile.addEventListener('change', previewImage);
        previewElement.addEventListener('click', () => inputFile.click());

        // Añadir evento de eliminación
        nuevoContenedor.querySelector('.eliminar-imagen').addEventListener('click', removeImage);
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
        const contenedor = e.target.closest('.formulario__campo');
    
        // Si es una imagen existente, marcar para eliminación
        if (contenedor.dataset.existente) {
            const inputId = contenedor.querySelector('input[type="hidden"]');
            const nuevoInput = document.createElement('input');
            nuevoInput.type = 'hidden';
            nuevoInput.name = 'imagenes_eliminadas[]';
            nuevoInput.value = inputId.value;
            contenedorImagenes.appendChild(nuevoInput);
        }
        
        contenedor.remove();
    }

    // ------------- Funciones para fichas tecnicas -------------
    function agregarEventListenerAFicha(contenedor) {
        const inputFile = contenedor.querySelector('.ficha-input');
        const nombreSpan = contenedor.querySelector('.ficha-nombre');

        inputFile.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "Ningún archivo seleccionado";
            nombreSpan.textContent = fileName;
        });

        // Event listener para el botón "Eliminar" dentro de cada ficha
        const btnEliminar = contenedor.querySelector('.eliminar-ficha');
        btnEliminar.addEventListener('click', function(e) {
            const contenedorFicha = e.target.closest('.contenedor-ficha');
            contenedorFicha.remove();

            // Reindexar las fichas restantes
            const fichas = document.querySelectorAll('.contenedor-ficha');
            fichas.forEach((ficha, index) => {
                ficha.dataset.index = index + 1;
                const input = ficha.querySelector('input[type="file"]');
                input.name = `fichas_${index + 1}[]`;
            });

            fichaCount = fichas.length;
        });
    }

    function crearNuevaFicha() {
        const totalFichas = document.querySelectorAll('.contenedor-ficha').length;
        if (totalFichas >= maxFichas) {
            alert('Máximo de fichas técnicas alcanzado');
            return;
        }

        const nuevoContenedor = document.createElement('div');
        nuevoContenedor.className = 'formulario__campo contenedor-ficha';
        
        nuevoContenedor.innerHTML = `
            <div class="ficha-preview">
                <input type="file" 
                    class="ficha-input" 
                    name="nuevas_fichas[]"
                    accept="application/pdf"
                >
                <button type="button" class="formulario__accion--secundario eliminar-ficha">Eliminar</button>
            </div>
        `;


        contenedorFichas.appendChild(nuevoContenedor);
        
        // Agregar evento de eliminación
        nuevoContenedor.querySelector('.eliminar-ficha').addEventListener('click', removeFicha);
    }



    function removeFicha(e) {
        const contenedor = e.target.closest('.formulario__campo');
    
        // Si es una ficha existente, crear input para eliminación
        if (contenedor.dataset.existente) {
            const inputId = contenedor.querySelector('input[type="hidden"]');
            const nuevoInput = document.createElement('input');
            nuevoInput.type = 'hidden';
            nuevoInput.name = 'fichas_eliminadas[]';
            nuevoInput.value = inputId.value;
            contenedorFichas.appendChild(nuevoInput);
        }
        
        contenedor.remove();
    }

    // ------------- Event Listeners -------------
    categoriaSelect.addEventListener('change', cargarSubcategorias);
    subcategoriaSelect.addEventListener('change', cargarAtributosDisponibles);

    btnAgregarImagen.addEventListener('click', crearNuevaImagen);

    btnAgregarFicha.addEventListener('click', crearNuevaFicha);
    
    // Delegación de eventos
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-imagen')) {
            removeImage(e);
        }
    });


    // ------------- Inicialización -------------
    if (window.location.pathname.includes('/crear')) {
        // Comportamiento para creación
        if (categoriaSelect.value) {
            cargarSubcategorias();
        }
        
        if (imageCount === 0) crearNuevaImagen();
        if (fichaCount === 0) crearNuevaFicha();
    } else {
        // Comportamiento para edición
        if (categoriaSelect.value) {
            cargarSubcategorias(false); // Evita la carga inicial de atributos
        }
        
        // Establecer la subcategoría seleccionada después de cargar las opciones
        if (productoSubcategoriaId) {
            subcategoriaSelect.value = productoSubcategoriaId;
        }
    }



    // Configurar imágenes existentes
    document.querySelectorAll('.contenedor-imagen:not([data-existente="true"])').forEach(contenedor => {
        const preview = contenedor.querySelector('.imagen-preview');
        const input = contenedor.querySelector('input[type="file"]');
        if (preview && input) {
            preview.addEventListener('click', () => input.click());
            input.addEventListener('change', previewImage);
        }
    });

    document.querySelectorAll('.contenedor-imagen[data-existente]').forEach(contenedor => {
        const btnEliminar = contenedor.querySelector('.eliminar-imagen');
        btnEliminar.addEventListener('click', function(e) {
            contenedor.remove();
        });
    });

    // Configurar fichas existentes
    document.querySelectorAll('.contenedor-ficha[data-existente]').forEach(contenedor => {
        const btnEliminar = contenedor.querySelector('.eliminar-ficha');
        btnEliminar.addEventListener('click', function(e) {
            contenedor.remove();
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('agregar-valor')) {
            const grupoAtributo = e.target.closest('.atributo-group');
            const atributoId = grupoAtributo.dataset.atributoId;
            const atributo = todosAtributos.find(a => a.id == atributoId);
            
            if (atributo) {
                const nuevoInput = createInput(atributo);
                grupoAtributo.querySelector('.atributo-inputs').appendChild(nuevoInput);
            }
        }
    });
});
</script>