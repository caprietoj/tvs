/**
 * Script para manejar la visibilidad de las habilidades en los espacios
 * según si pertenecen o no a la biblioteca
 */
document.addEventListener('DOMContentLoaded', function() {
    // Controlar la visibilidad de la sección de habilidades
    const isLibraryCheckbox = document.getElementById('is_library');
    const skillsSection = document.getElementById('skills-section');
    
    if (isLibraryCheckbox && skillsSection) {
        // Función para controlar la visibilidad
        const toggleSkillsSection = () => {
            if (isLibraryCheckbox.checked) {
                skillsSection.style.display = 'block';
            } else {
                skillsSection.style.display = 'none';
            }
        };
        
        // Inicializar el estado
        toggleSkillsSection();
        
        // Añadir evento de cambio al checkbox
        isLibraryCheckbox.addEventListener('change', toggleSkillsSection);
    }
});
