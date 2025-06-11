@extends('adminlte::page')

@section('title', 'Crear Nuevo Espacio')

@section('content_header')
    <h1>Crear Nuevo Espacio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('spaces.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="location">Ubicación</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location') }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="capacity">Capacidad</label>
                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                               id="capacity" name="capacity" value="{{ old('capacity') }}" min="1">
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Imagen del Espacio</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">Seleccionar imagen...</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <div class="mt-2" id="imagePreview" style="display: none;">
                        <img src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>

                <!-- Estado del Espacio -->
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
                                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" checked>
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
                                    <input type="checkbox" class="custom-control-input" id="is_library" name="is_library" value="1">
                                    <label class="custom-control-label" for="is_library">
                                        <span class="text-info">Espacio de Biblioteca</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Implementos para Préstamo -->
                <div class="card mt-4 mb-4">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Implementos para Préstamo</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Agregue los implementos que estarán disponibles para préstamo junto con este espacio.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Aquí se agregarán los ítems dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-success btn-sm" id="add-item">
                            <i class="fas fa-plus"></i> Agregar Implemento
                        </button>
                    </div>
                </div>

                <!-- Sección de Implementación de Habilidades -->
                <div class="card mt-4 mb-4" id="skills-section" style="display: none;">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Implementación de Habilidades</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gestione las habilidades y sus ítems asociados que estarán disponibles para este espacio.</p>
                        
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
                                                <h5 class="mb-0">{{ $subcategory->name }}</h5>
                                                <button type="button" class="btn btn-success btn-sm add-skill-item" 
                                                        data-category="{{ $category->id }}" 
                                                        data-subcategory="{{ $subcategory->id }}"
                                                        data-subcategory-id="{{ $subcategory->id }}">
                                                    <i class="fas fa-plus"></i> Agregar Habilidad
                                                </button>
                                            </div>
                                            <p class="text-muted small">{{ $subcategory->description ?? 'Sin descripción disponible' }}</p>
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
                                                    <tbody></tbody>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ route('spaces.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const initializeImageHandlers = () => {
                const imageInput = document.getElementById('image');
                if (imageInput) {
                    imageInput.addEventListener('change', function() {
                        var fileName = this.files[0]?.name || 'Seleccionar imagen...';
                        document.querySelector('.custom-file-label').textContent = fileName;
                        
                        if (this.files && this.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                const preview = document.querySelector('#imagePreview img');
                                if (preview) {
                                    preview.src = e.target.result;
                                    document.getElementById('imagePreview').style.display = 'block';
                                }
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                }
            };

            const initializeLibraryHandlers = () => {
                const isLibrarySwitch = document.getElementById('is_library');
                const skillsSection = document.getElementById('skills-section');
                
                if (isLibrarySwitch && skillsSection) {
                    isLibrarySwitch.addEventListener('change', function() {
                        if (this.checked) {
                            skillsSection.style.display = '';
                        } else {
                            if (confirm('Al desactivar esta opción se eliminarán todas las habilidades asociadas al espacio. ¿Desea continuar?')) {
                                skillsSection.style.display = 'none';
                                document.querySelectorAll('.remove-skill-item').forEach(button => {
                                    button.click();
                                });
                            } else {
                                this.checked = true;
                            }
                        }
                    });
                }
            };

            // Variables para gestionar los implementos
            let itemIndex = 0;
            const itemsTable = document.getElementById('items-table');

            // Función para agregar un nuevo implemento
            document.querySelector('#add-item').addEventListener('click', function() {
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

            // Variable para gestionar el índice de las habilidades
            let skillItemIndex = 0;

            // Función para inicializar los manejadores de eventos de los botones de agregar habilidades
            const initializeSkillHandlers = () => {
                document.querySelectorAll('.add-skill-item').forEach(button => {
                    button.addEventListener('click', function() {
                        const category = this.dataset.category;
                        const subcategory = this.dataset.subcategory;
                        const subcategoryId = this.dataset.subcategoryId;
                        
                        const table = document.querySelector(`table[data-category="${category}"][data-subcategory="${subcategory}"]`);
                        if (!table) return;

                        const tbody = table.querySelector('tbody');
                        if (!tbody) return;

                        skillItemIndex++;
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <input type="text" class="form-control form-control-sm" 
                                       name="skills[${category}][${subcategory}][${skillItemIndex}][name]" required 
                                       placeholder="Nombre de la habilidad">
                                <input type="hidden" 
                                       name="skills[${category}][${subcategory}][${skillItemIndex}][subcategory_id]" 
                                       value="${subcategoryId}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" 
                                       name="skills[${category}][${subcategory}][${skillItemIndex}][description]" 
                                       placeholder="Descripción breve">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-skill-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;

                        tbody.appendChild(row);

                        // Agregar el evento para eliminar la habilidad
                        row.querySelector('.remove-skill-item').addEventListener('click', function() {
                            row.remove();
                        });

                        // Enfocar el input del nombre
                        row.querySelector('input[type="text"]').focus();
                    });
                });
            };

            // Inicializar todos los componentes
            initializeImageHandlers();
            initializeLibraryHandlers();
            initializeSkillHandlers();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
@stop