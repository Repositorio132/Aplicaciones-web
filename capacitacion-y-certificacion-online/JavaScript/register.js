document.querySelector('.form-register').addEventListener('submit', function(e) {
    let valid = true;
    let errorMessages = [];
    
    // Validación de Nombre
    const nombre = document.getElementById('nombre');
    if (!nombre.value.trim()) {
        valid = false;
        errorMessages.push('El nombre es obligatorio.');
    }
    
    // Validación de Apellidos
    const apellidoPaterno = document.getElementById('apellido_paterno');
    const apellidoMaterno = document.getElementById('apellido_materno');
    if (!apellidoPaterno.value.trim() || !apellidoMaterno.value.trim()) {
        valid = false;
        errorMessages.push('Ambos apellidos son obligatorios.');
    }
    
    // Validación de Número de Celular
    const numeroCelular = document.getElementById('numero_celular');
    const phonePattern = /^[0-9]{10}$/;
    if (!phonePattern.test(numeroCelular.value)) {
        valid = false;
        errorMessages.push('El número de celular debe tener 10 dígitos.');
    }

    // Validación de Correo
    const email = document.getElementById('email');
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(email.value)) {
        valid = false;
        errorMessages.push('Por favor, ingresa un correo electrónico válido.');
    }

    // Validación de Contraseñas
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    if (password.value !== confirmPassword.value) {
        valid = false;
        errorMessages.push('Las contraseñas no coinciden.');
    }

    // Mostrar mensajes de error
    const errorContainer = document.querySelector('.message');
    if (!valid) {
        errorContainer.innerHTML = errorMessages.join('<br>');
        errorContainer.style.display = 'block';
        e.preventDefault(); // Evita que se envíen los datos si hay errores
    } else {
        errorContainer.style.display = 'none';
    }
});
