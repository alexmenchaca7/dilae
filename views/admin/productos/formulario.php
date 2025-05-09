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
    <legend class="formulario__legend">Imágenes del Producto</legend>

    <!-- Input oculto para el orden -->
    <input type="hidden" id="orden_imagenes" name="orden_imagenes" value="">
    
    <div class="contenedor-imagenes" id="contenedor-imagenes">
        <?php foreach($imagenes as $imagen): ?>
            <div class="formulario__campo contenedor-imagen" data-existente="true" data-id="<?= $imagen->id ?>">
                <div class="contenedor-imagen-preview">
                    <div class="imagen-preview">
                        <img src="/img/productos/<?= $imagen->url ?>.webp" alt="<?php echo $producto->nombre; ?>">
                        <input type="hidden" name="imagenes_existentes[]" value="<?= $imagen->id ?>">

                        <!-- Input para reemplazar -->
                        <input 
                            type="file"
                            class="imagen-input"
                            name="imagenes_reemplazo[<?= $imagen->id ?>]"
                            accept="image/*"
                            style="display: none;"
                        >
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
    <div id="atributos-container" data-orden="<?= !empty($atributosDisponibles) ? htmlspecialchars(json_encode(array_column($atributosDisponibles, 'id'))) : '' ?>">
        <?php if (!empty($atributosDisponibles)): ?>
            <?php 
            // Mantener el orden original de los IDs
            $ordenAtributos = array_column($atributosDisponibles, 'id');
            ?>
            <?php foreach($ordenAtributos as $atributoId): ?>
                <?php 
                $atributo = current(array_filter($atributosDisponibles, function($a) use ($atributoId) {
                    return $a->id == $atributoId;
                }));
                if ($atributo) : ?>
                    <div class="atributo-group" data-atributo-id="<?= $atributo->id ?>">
                        <label class="formulario__label">
                            <?= htmlspecialchars($atributo->nombre) ?>
                            <?php if (!empty($atributo->unidad) && trim($atributo->unidad) !== ''): ?>
                                (<?= htmlspecialchars($atributo->unidad) ?>)
                            <?php endif; ?>
                        </label>
                        <div class="atributo-inputs">
                            <?php 
                                // Inicializar con array vacío si no hay valores
                                $valores = $atributosValores[$atributo->id] ?? [];
                                // Si está vacío y es renderizado PHP, agregar un valor vacío
                                if (empty($valores) && isset($atributo->renderizado_php)) {
                                    $valores = [''];
                                }
                                foreach($valores as $valor):  
                            ?>
                            <div class="input-wrapper">
                                <input 
                                    type="<?= $atributo->tipo === 'numero' ? 'number' : 'text' ?>" 
                                    name="atributos[<?= $atributo->id ?>][]"
                                    placeholder="<?= htmlspecialchars($atributo->nombre) ?>"
                                    value="<?= htmlspecialchars(
                                        ($atributo->tipo === 'numero' && is_numeric($valor)) 
                                            ? rtrim(rtrim(number_format((float)$valor, 2, '.', ''), '0'), '.') 
                                            : $valor 
                                    ) ?>"
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
    const maxImages = 15;

    const contenedorFichas = document.getElementById('contenedor-fichas');
    const btnAgregarFicha = document.getElementById('agregar-ficha');
    
    let fichaCount = 0;
    const maxFichas = 15;

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

        // 1. Agregar atributos de categoría en ORDEN
        if (relacionesAtributos.categorias[categoriaId]) {
            atributosIds = [...relacionesAtributos.categorias[categoriaId]];
        }

        // 2. Agregar atributos de subcategoría en ORDEN (si aplica)
        if (tieneSubcategorias && subcategoriaId && relacionesAtributos.subcategorias[subcategoriaId]) {
            const subcategoriaAtributos = relacionesAtributos.subcategorias[subcategoriaId];
            subcategoriaAtributos.forEach(atributoId => {
                if (!atributosIds.includes(atributoId)) {
                    atributosIds.push(atributoId);
                }
            });
        }

        // Filtrar y mantener orden
        const atributosPermitidos = todosAtributos.filter(atributo => 
            atributosIds.includes(atributo.id)
        ).sort((a, b) => {
            return atributosIds.indexOf(a.id) - atributosIds.indexOf(b.id);
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
        if (atributo.tipo === 'numero') {
            input.step = 'any';
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'eliminar-valor';
        removeBtn.textContent = '×';

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

    function previewReemplazoImagen(e) {
        const input = e.target;
        const preview = input.closest('.imagen-preview');
        const img = preview.querySelector('img');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.add('imagen-reemplazada');
            }
            reader.readAsDataURL(file);
        }
    }


    // ------------- Ordenamiento de imágenes -------------
    const inputOrden = document.getElementById('orden_imagenes');

    // Inicializar Sortable
    const sortable = new Sortable(contenedorImagenes, {
        animation: 150,
        handle: '.contenedor-imagen-preview',
        ghostClass: 'imagen-fantasma',
        onUpdate: function(evt) {
            actualizarOrdenImagenes();
        }
    });

    function actualizarOrdenImagenes() {
        const ids = Array.from(contenedorImagenes.querySelectorAll('[data-id]'))
                       .map(img => img.getAttribute('data-id'))
                       .join(',');
        document.getElementById('orden_imagenes').value = ids;

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
        const nuevoContenedor = document.createElement('div');
        nuevoContenedor.className = 'formulario__campo contenedor-ficha';
        
        nuevoContenedor.innerHTML = `
            <div class="ficha-preview">
                <input type="file" 
                    class="ficha-input" 
                    name="nuevas_fichas[]"
                    accept="application/pdf"
                >
                <div class="mensaje-error text-red-600 text-sm"></div>
                <button type="button" class="formulario__accion--secundario eliminar-ficha">Eliminar</button>
            </div>
        `;

        const inputFile = nuevoContenedor.querySelector('.ficha-input');
        const mensajeError = nuevoContenedor.querySelector('.mensaje-error');

        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const nombreOriginal = file.name;
            const nombreSanitizado = nombreOriginal.replace(/[^a-zA-Z0-9_.-]/g, '_');

            // Validación en tiempo real
            fetch(`/admin/productos/verificar-ficha?nombre=${encodeURIComponent(nombreSanitizado)}`)
                .then(response => {
                    if(!response.ok) throw new Error('Error en la verificación');
                    return response.json();
                })
                .then(data => {
                    if (data.existe) {
                        mensajeError.textContent = `⚠️ El archivo ${nombreOriginal} ya existe`;
                        inputFile.value = '';
                    } else {
                        mensajeError.textContent = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mensajeError.textContent = 'Error verificando la ficha';
                });
        });

        contenedorFichas.appendChild(nuevoContenedor);
        nuevoContenedor.querySelector('.eliminar-ficha').addEventListener('click', removeFicha);
    }





    function removeFicha(e) {
        const contenedor = e.target.closest('.formulario__campo');
        
        // Si es una ficha existente, crear input para eliminación
        if (contenedor.dataset.existente) {
            const inputId = contenedor.querySelector('input[name="fichas_existentes[]"]');
            if (inputId) {
                const nuevoInput = document.createElement('input');
                nuevoInput.type = 'hidden';
                nuevoInput.name = 'fichas_eliminadas[]';
                nuevoInput.value = inputId.value;
                // Añadir al formulario, no al contenedor de fichas
                document.querySelector('form').appendChild(nuevoInput);
            }
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

        // Configurar imágenes existentes
        document.querySelectorAll('.contenedor-imagen[data-existente="true"]').forEach(contenedor => {
            const preview = contenedor.querySelector('.imagen-preview');
            const inputFile = contenedor.querySelector('.imagen-input');
            
            preview.addEventListener('click', () => inputFile.click());
            inputFile.addEventListener('change', previewReemplazoImagen);
        });
    }

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
            const contenedorFicha = e.target.closest('.contenedor-ficha');

            if (contenedorFicha && contenedorFicha.dataset.existente === 'true') {
                const idFicha = contenedorFicha.querySelector('input[name="fichas_existentes[]"]').value;

                // Crear un input oculto para marcar la ficha como eliminada
                const inputEliminado = document.createElement('input');
                inputEliminado.type = 'hidden';
                inputEliminado.name = 'fichas_eliminadas[]';
                inputEliminado.value = idFicha;

                // Añadir el input al formulario (asumiendo que el formulario es el padre del contenedor)
                contenedorFicha.closest('form').appendChild(inputEliminado);

                // Remover visualmente la ficha
                contenedorFicha.remove();
            }
        });
    });

    document.addEventListener('click', function(e) {
        // Eliminar valor
        if (e.target.classList.contains('eliminar-valor')) {
            e.target.closest('.input-wrapper').remove();
        }
        
        // Agregar nuevo valor
        if (e.target.classList.contains('agregar-valor')) {
            const grupoAtributo = e.target.closest('.atributo-group');
            const atributoId = grupoAtributo.dataset.atributoId;
            const inputsContainer = grupoAtributo.querySelector('.atributo-inputs');
            
            // Buscar el atributo en todosAtributos
            const atributo = todosAtributos.find(a => a.id == atributoId);
            if (atributo) {
                const nuevoInput = createInput(atributo);
                inputsContainer.appendChild(nuevoInput);
            }
        }
    });
});
</script>