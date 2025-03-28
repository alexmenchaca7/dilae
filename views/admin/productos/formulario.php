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
</fieldset>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ------------- Código para subcategorías -------------
        const subcategoriasPorCategoria = <?php echo json_encode($subcategoriasPorCategoria); ?>;
        const categoriaSelect = document.getElementById('categoriaId');
        const subcategoriaSelect = document.getElementById('subcategoriaId');
        const subcategoriaInicial = <?php echo json_encode($producto->subcategoriaId ?? null); ?>;

        function cargarSubcategorias() {
            subcategoriaSelect.innerHTML = '<option value="">-- Seleccione --</option>';
            const categoriaId = categoriaSelect.value;
            
            if (categoriaId && subcategoriasPorCategoria[categoriaId]) {
                subcategoriasPorCategoria[categoriaId].forEach(subcategoria => {
                    const option = new Option(subcategoria.nombre, subcategoria.id);
                    option.selected = (subcategoria.id == subcategoriaInicial);
                    subcategoriaSelect.add(option);
                });
                subcategoriaSelect.disabled = false;
            } else {
                subcategoriaSelect.innerHTML = '<option value="" selected>Sin subcategoría</option>';
                subcategoriaSelect.disabled = true;
            }
        }


        categoriaSelect.addEventListener('change', cargarSubcategorias);
        
        if (categoriaSelect.value) {
            cargarSubcategorias();
        }

        // ------------- Código para imágenes -------------
        let imageCount = 0;
        const maxImages = 5;
        const contenedor = document.getElementById('contenedor-imagenes');
        const btnAgregarImagen = document.getElementById('agregar-imagen');

        function crearNuevaImagen() {
            if(imageCount >= maxImages) {
                alert('Máximo de imágenes alcanzado');
                return;
            }
            
            imageCount++;
            const nuevoContenedor = document.createElement('div');
            nuevoContenedor.className = 'formulario__campo contenedor-imagen';
            nuevoContenedor.setAttribute('data-index', imageCount);
            
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
                            onchange="previewImage(this, event)"
                        >
                    </div>
                    <button type="button" class="formulario__accion--secundario" onclick="removeImage(this)">Eliminar</button>
                </div>
            `;


            contenedor.appendChild(nuevoContenedor);
            
            // Eventos corregidos
            const previewElement = document.getElementById(`imagenPreview${imageCount}`);
            const inputFile = previewElement.querySelector('input[type="file"]');
            
            inputFile.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            previewElement.addEventListener('click', function() {
                inputFile.click();
            });
        }

        crearNuevaImagen(); // Carga inicial
        btnAgregarImagen.addEventListener('click', crearNuevaImagen);
    });

    // Función previewImage
    function previewImage(input, event) {
        const preview = input.closest('.contenedor-imagen-preview').querySelector('.imagen-preview');
        const placeholder = preview.querySelector('.imagen-placeholder');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Mantenemos el input file oculto pero presente
                const inputFile = preview.querySelector('input[type="file"]');
                
                // Creamos el contenedor de preview sin eliminar el input
                let imgPreview = preview.querySelector('.imagen-cargada');
                if (!imgPreview) {
                    imgPreview = document.createElement('img');
                    imgPreview.classList.add('imagen-cargada');
                    preview.insertBefore(imgPreview, placeholder);
                }
                
                imgPreview.src = e.target.result;
                imgPreview.alt = "Preview";
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    }

    // Función removeImage
    function removeImage(button) {
        const contenedor = button.closest('.formulario__campo');
        contenedor.remove();
        
        let index = 1;
        document.querySelectorAll('.contenedor-imagen').forEach(contenedor => {
            contenedor.setAttribute('data-index', index);
            index++;
        });
    }
</script>