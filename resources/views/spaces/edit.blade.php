@extends('adminlte::page')

@section('title', 'Editar Espacio')

@section('content_header')
    <h1>Editar Espacio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('spaces.update', $space) }}" method="POST" enctype="multipart/form-data" id="spaceForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $space->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $space->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="location">Ubicación</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location', $space->location) }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="capacity">Capacidad</label>
                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                               id="capacity" name="capacity" value="{{ old('capacity', $space->capacity) }}" min="1">
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Imagen del Espacio</label>
                    @if ($space->image_path)
                        <div class="mb-2">
                            <img src="{{ asset($space->image_path) }}" alt="{{ $space->name }}" 
                                 class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">{{ $space->image_path ? 'Cambiar imagen...' : 'Seleccionar imagen...' }}</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB</small>
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div class="mt-2" id="imagePreview" style="display: none;">
                        <img src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>

                <!-- Sección de Implementos para Préstamo -->
                <div class="form-group mt-4">
                    <label for="items">Implementos para Préstamo</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="items-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Disponible</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($space->items->count() > 0)
                                    @foreach($space->items as $index => $item)
                                    <tr data-index="{{ $index }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <input type="text" class="form-control" name="items[{{ $index }}][name]" 
                                                   value="{{ $item->name }}" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[{{ $index }}][description]" 
                                                   value="{{ $item->description }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[{{ $index }}][quantity]" 
                                                   value="{{ $item->quantity }}" min="1" required>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="available_{{ $index }}" name="items[{{ $index }}][available]" 
                                                       {{ $item->available ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="available_{{ $index }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-danger btn-sm delete-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-success btn-sm add-item">
                                            <i class="fas fa-plus"></i> Agregar Implemento
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 text-primary">
                                <i class="fas fa-power-off fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Estado del Espacio</h5>
                                <p class="text-muted mb-2">Determina si el espacio está disponible para reservas</p>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                                           {{ $space->active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="active">
                                        <span class="text-success">Espacio Activo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-info">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Tipo de Espacio</h5>
                                <p class="text-muted mb-2">Indica si el espacio pertenece a la biblioteca y requiere gestión de habilidades</p>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_library" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_library" name="is_library" value="1"
                                           {{ $space->is_library ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_library">
                                        <span class="text-info">Espacio de Biblioteca</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Implementación de Habilidades -->
                <div class="card mt-4 mb-4" id="skills-section" style="{{ $space->is_library ? '' : 'display: none;' }}">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Implementación de Habilidades</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestione las habilidades y sus ítems asociados que estarán disponibles para este espacio.</p>
                        
                        @if($space->skills->count() > 0)
                            <div class="alert alert-info">
                                Este espacio tiene {{ $space->skills->count() }} habilidades asociadas.
                            </div>
                        @endif
                        <div class="accordion" id="skillsAccordion">
                            @foreach($skillCategories as $category)
                            <div class="card">
                                <div class="card-header bg-light" id="heading{{ $category->id }}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left text-primary" type="button" 
                                                data-toggle="collapse" data-target="#collapse{{ $category->id }}" 
                                                aria-expanded="false" aria-controls="collapse{{ $category->id }}">
                                            <i class="fas fa-chevron-right"></i>
                                            {{ $category->name }} 
                                            @php
                                                $skillsInCategory = $space->skills->filter(function($skill) use ($category) {
                                                    return $skill->subcategory && $skill->subcategory->category_id == $category->id;
                                                });
                                            @endphp
                                            @if($skillsInCategory->count() > 0)
                                                <span class="badge badge-info">{{ $skillsInCategory->count() }}</span>
                                            @endif
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapse{{ $category->id }}" class="collapse" 
                                     aria-labelledby="heading{{ $category->id }}" 
                                     data-parent="#skillsAccordion">
                                    <div class="card-body">
                                        @foreach($category->subcategories as $subcategory)
                                        <div class="skill-subcategory mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h5 class="mb-0">
                                                    {{ $subcategory->name }}
                                                    @php
                                                        $skillsInSubcategory = $space->skills->filter(function($skill) use ($subcategory) {
                                                            return $skill->skill_subcategory_id == $subcategory->id;
                                                        });
                                                    @endphp
                                                    @if($skillsInSubcategory->count() > 0)
                                                        <span class="badge badge-success">{{ $skillsInSubcategory->count() }}</span>
                                                    @endif
                                                </h5>
                                                <button type="button" class="btn btn-success btn-sm add-skill-item" 
                                                        data-category="{{ $category->id }}" 
                                                        data-subcategory="{{ $subcategory->id }}"
                                                        data-subcategory-id="{{ $subcategory->id }}">
                                                    <i class="fas fa-plus"></i> Agregar Habilidad
                                                </button>
                                            </div>
                                            
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm" 
                                                       data-category="{{ $category->id }}" 
                                                       data-subcategory="{{ $subcategory->id }}">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Descripción</th>
                                                            <th width="100">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($space->skills as $index => $skill)
                                                            @if($skill->skill_subcategory_id == $subcategory->id)
                                                            <tr>
                                                                <td>
                                                                    <input type="text" 
                                                                           name="skills[{{ $category->id }}][{{ $subcategory->id }}][{{ $index }}][name]" 
                                                                           class="form-control form-control-sm" 
                                                                           value="{{ $skill->name }}" required>
                                                                    <input type="hidden" 
                                                                           name="skills[{{ $category->id }}][{{ $subcategory->id }}][{{ $index }}][id]" 
                                                                           value="{{ $skill->id }}">
                                                                    <input type="hidden" 
                                                                           name="skills[{{ $category->id }}][{{ $subcategory->id }}][{{ $index }}][subcategory_id]" 
                                                                           value="{{ $subcategory->id }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" 
                                                                           name="skills[{{ $category->id }}][{{ $subcategory->id }}][{{ $index }}][description]" 
                                                                           class="form-control form-control-sm" 
                                                                           value="{{ $skill->pivot->description }}" 
                                                                           placeholder="Descripción breve">
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-danger btn-sm remove-skill-item">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('spaces.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monitorear el envío del formulario
            const form = document.getElementById('spaceForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (form && submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Verificar si hay habilidades en el formulario
                    const skillInputs = document.querySelectorAll('input[name*="skills"][name*="name"]');
                    console.log('Habilidades encontradas:', skillInputs.length);
                    
                    // Obtener todos los datos del formulario
                    const formData = new FormData(form);
                    
                    // Verificar si is_library está marcado
                    console.log('Is Library:', formData.get('is_library') ? 'Sí' : 'No');
                    
                    // Enviar el formulario
                    form.submit();
                });
            }
            // Función para manejar la vista previa de imagen
            const initImagePreview = () => {
                const imageInput = document.getElementById('image');
                if (!imageInput) return;

                imageInput.addEventListener('change', function() {
                    const fileName = this.files[0]?.name || '{{ $space->image_path ? "Cambiar imagen..." : "Seleccionar imagen..." }}';
                    const fileLabel = document.querySelector('.custom-file-label');
                    if (fileLabel) fileLabel.textContent = fileName;
                    
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.querySelector('#imagePreview img');
                            const previewContainer = document.getElementById('imagePreview');
                            if (preview && previewContainer) {
                                preview.src = e.target.result;
                                previewContainer.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            };

            // Función para manejar la visibilidad de la sección de habilidades
            const initLibrarySection = () => {
                const isLibrarySwitch = document.getElementById('is_library');
                const skillsSection = document.getElementById('skills-section');
                if (!isLibrarySwitch || !skillsSection) return;

                isLibrarySwitch.addEventListener('change', function() {
                    if (this.checked) {
                        skillsSection.style.display = '';
                    } else {
                        if (confirm('Al desactivar esta opción se eliminarán todas las habilidades asociadas al espacio. ¿Desea continuar?')) {
                            skillsSection.style.display = 'none';
                            // Limpiar todas las habilidades
                            document.querySelectorAll('.remove-skill-item').forEach(button => {
                                if (button) button.click();
                            });
                        } else {
                            this.checked = true;
                        }
                    }
                });
            };

            // Función para inicializar los manejadores de habilidades
            const initSkillHandlers = () => {
                let skillItemCounter = {{ $space->skills->count() }};
                
                // Manejador para agregar ítems de habilidades
                document.querySelectorAll('.add-skill-item').forEach(button => {
                    if (!button) return;
                    button.addEventListener('click', function() {
                        const category = this.dataset.category;
                        const subcategory = this.dataset.subcategory;
                        const subcategoryId = this.dataset.subcategoryId;
                        if (!category || !subcategory || !subcategoryId) return;

                        const table = document.querySelector(`table[data-category="${category}"][data-subcategory="${subcategory}"]`);
                        if (!table) return;
                        const tbody = table.querySelector('tbody');
                        if (!tbody) return;
                    
                        skillItemCounter++;
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <input type="text" name="skills[${category}][${subcategory}][${skillItemCounter}][name]" 
                                       class="form-control form-control-sm" required 
                                       placeholder="Nombre de la habilidad">
                                <input type="hidden" name="skills[${category}][${subcategory}][${skillItemCounter}][subcategory_id]" 
                                       value="${subcategoryId}">
                            </td>
                            <td>
                                <input type="text" name="skills[${category}][${subcategory}][${skillItemCounter}][description]" 
                                       class="form-control form-control-sm" 
                                       placeholder="Descripción breve">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-skill-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        
                        tbody.appendChild(row);
                        
                        // Agregar evento para eliminar el ítem
                        row.querySelector('.remove-skill-item').addEventListener('click', function() {
                            this.closest('tr').remove();
                        });

                        // Enfocar el input del nombre
                        row.querySelector('input[type="text"]').focus();
                    });
                });

                // Agregar eventos a botones de eliminar existentes
                document.querySelectorAll('.remove-skill-item').forEach(button => {
                    button.addEventListener('click', function() {
                        this.closest('tr').remove();
                    });
                });
            };

            // Variables para gestionar los implementos
            let itemIndex = {{ $space->items->count() > 0 ? $space->items->count() : 0 }};
            const itemsTable = document.getElementById('items-table');

            // Función para agregar un nuevo implemento
            document.querySelector('.add-item').addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.dataset.index = itemIndex;
                
                newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control" name="items[${itemIndex}][name]" required>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="items[${itemIndex}][description]">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="items[${itemIndex}][quantity]" min="1" value="1" required>
                    </td>
                    <td class="text-center align-middle">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="available_${itemIndex}" name="items[${itemIndex}][available]" checked>
                            <label class="custom-control-label" for="available_${itemIndex}"></label>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm delete-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                itemsTable.querySelector('tbody').appendChild(newRow);
                itemIndex++;
                
                // Agregar evento al botón de eliminar
                newRow.querySelector('.delete-item').addEventListener('click', function() {
                    newRow.remove();
                });
            });

            // Agregar eventos a botones de eliminar existentes
            document.querySelectorAll('.delete-item').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('tr').remove();
                });
            });

            // Gestión del acordeón
            const skillsAccordion = document.getElementById('skillsAccordion');
            
            // Abrir el primer elemento del acordeón por defecto
            const firstCollapse = skillsAccordion.querySelector('.collapse');
            if (firstCollapse) {
                firstCollapse.classList.add('show');
                const button = firstCollapse.previousElementSibling.querySelector('button');
                button.setAttribute('aria-expanded', 'true');
                button.querySelector('i').classList.replace('fa-chevron-right', 'fa-chevron-down');
            }

            // Manejar la rotación del ícono al expandir/colapsar
            skillsAccordion.addEventListener('show.bs.collapse', function(e) {
                const button = e.target.previousElementSibling.querySelector('button');
                button.querySelector('i').classList.replace('fa-chevron-right', 'fa-chevron-down');
            });

            skillsAccordion.addEventListener('hide.bs.collapse', function(e) {
                const button = e.target.previousElementSibling.querySelector('button');
                button.querySelector('i').classList.replace('fa-chevron-down', 'fa-chevron-right');
            });

            // Inicializar todos los componentes
            try {
                initImagePreview();
                initLibrarySection();
                initSkillHandlers();
            } catch (error) {
                console.error('Error al inicializar componentes:', error);
            }
        });
    </script>
@stop