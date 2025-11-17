<?php
/**
 * Página de Registro de Usuario - CU-01
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Arquitectura: Presentación → ServicioUsuariosAuth → RepositorioUsuarios → BD
 */

// Iniciar sesión PHP
session_start();

// Cargar clases necesarias
require_once __DIR__ . '/../src/services/ServicioUsuariosAuth.php';
require_once __DIR__ . '/../src/models/ServiceResult.php';

// Variables para mensajes
$mensajeError = '';
$mensajeExito = '';
$erroresCampos = [];

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $datosRegistro = [
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'correo' => $_POST['email'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'contrasena' => $_POST['password'] ?? '',
            'rol' => $_POST['rol'] ?? ''
        ];
        
        // Validar que las contraseñas coincidan
        $confirmarContrasena = $_POST['confirm_password'] ?? '';
        if ($datosRegistro['contrasena'] !== $confirmarContrasena) {
            $mensajeError = 'Las contraseñas no coinciden';
            $erroresCampos['confirm_password'] = 'Las contraseñas deben ser iguales';
        } else {
            // Instanciar servicio de autenticación
            $servicioAuth = new ServicioUsuariosAuth();
            
            // Llamar al método de registro
            $resultado = $servicioAuth->registrarUsuario($datosRegistro);
            
            // Verificar si el registro fue exitoso
            if ($resultado->isSuccess()) {
                $mensajeExito = 'Registro exitoso. Redirigiendo al inicio de sesión...';
                // Limpiar datos del formulario
                $_POST = [];
                // Redirigir después de 2 segundos
                header("Refresh: 2; url=login.php");
            } else {
                // Registro falló - mostrar mensaje de error
                $mensajeError = $resultado->getMessage();
                
                // Obtener errores específicos por campo si existen
                $errores = $resultado->getErrors();
                if (is_array($errores)) {
                    $erroresCampos = $errores;
                }
            }
        }
        
    } catch (Exception $e) {
        // Manejar errores de forma amigable
        error_log("Error en register.php: " . $e->getMessage());
        $mensajeError = 'Error al procesar el registro. Por favor, intenta nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Patitas Felices</title>
    <meta name="description" content="Regístrate en Patitas Felices para adoptar mascotas o ser voluntario en nuestra fundación.">
    <link rel="stylesheet" href="css/material-design.css">
    <style>
        /* Reset y Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--md-background) 0%, var(--md-tertiary) 100%);
            color: var(--md-on-background);
        }

        /* Header */
        .header {
            background-color: var(--md-surface);
            box-shadow: var(--md-elevation-1);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            text-decoration: none;
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: var(--md-radius-full);
            background: linear-gradient(135deg, var(--md-primary) 0%, var(--md-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--md-primary);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--md-spacing-2xl) var(--md-spacing-md);
        }

        /* Register Card */
        .register-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-xl);
            box-shadow: var(--md-elevation-3);
            padding: var(--md-spacing-2xl);
            width: 100%;
            max-width: 600px;
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.4s ease forwards;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-title {
            text-align: center;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xl);
        }

        /* Alert Messages */
        .alert {
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background-color: var(--md-error-container);
            color: var(--md-on-error-container);
            border-left: 4px solid var(--md-error);
        }

        .alert-success {
            background-color: var(--md-success-container);
            color: var(--md-on-success-container);
            border-left: 4px solid var(--md-success);
        }

        .alert-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-message {
            flex: 1;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        /* Form Styles */
        .register-form {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-lg);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--md-spacing-md);
        }

        /* Input Container with Floating Label */
        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-wrapper.full-width {
            grid-column: 1 / -1;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            padding: var(--md-spacing-md) var(--md-spacing-md) var(--md-spacing-sm);
            font-size: 1rem;
            color: var(--md-on-surface);
            background-color: var(--md-surface-variant);
            border: none;
            border-bottom: 2px solid var(--md-outline);
            border-radius: var(--md-radius-sm) var(--md-radius-sm) 0 0;
            outline: none;
            transition: all var(--md-transition-base);
        }

        .input-wrapper select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right var(--md-spacing-md) center;
            padding-right: var(--md-spacing-2xl);
        }

        .input-wrapper input:focus,
        .input-wrapper select:focus {
            background-color: var(--md-surface-bright);
            border-bottom-color: var(--md-primary);
        }

        .input-wrapper input.error,
        .input-wrapper select.error {
            border-bottom-color: var(--md-error);
        }

        .input-wrapper input:focus + label,
        .input-wrapper input:not(:placeholder-shown) + label,
        .input-wrapper select:focus + label,
        .input-wrapper select:not([value=""]) + label {
            top: 4px;
            font-size: 0.75rem;
            color: var(--md-primary);
        }

        .input-wrapper input.error:focus + label,
        .input-wrapper input.error:not(:placeholder-shown) + label,
        .input-wrapper select.error:focus + label {
            color: var(--md-error);
        }

        .input-wrapper label {
            position: absolute;
            left: var(--md-spacing-md);
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: var(--md-on-surface-variant);
            pointer-events: none;
            transition: all var(--md-transition-base);
            background-color: transparent;
        }

        .input-wrapper input:focus + label,
        .input-wrapper select:focus + label {
            font-weight: 500;
        }

        .input-error-message {
            font-size: 0.75rem;
            color: var(--md-error);
            margin-top: var(--md-spacing-xs);
            display: none;
        }

        .input-error-message.show {
            display: block;
        }

        /* Optional Field Indicator */
        .optional-indicator {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
            font-style: italic;
            margin-left: var(--md-spacing-xs);
        }

        /* Submit Button */
        .submit-button {
            width: 100%;
            margin-top: var(--md-spacing-md);
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border: none;
            border-radius: var(--md-radius-full);
            font-size: 0.875rem;
            font-weight: 500;
            letter-spacing: 0.006em;
            cursor: pointer;
            transition: all var(--md-transition-base);
            box-shadow: var(--md-elevation-1);
            min-height: 48px;
        }

        .submit-button:hover:not(:disabled) {
            background-color: var(--md-primary-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        .submit-button:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: var(--md-elevation-1);
        }

        .submit-button:focus-visible {
            outline: 2px solid var(--md-primary);
            outline-offset: 2px;
        }

        .submit-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Links Section */
        .links-section {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-sm);
            margin-top: var(--md-spacing-xl);
            align-items: center;
        }

        .text-link {
            background-color: transparent;
            color: var(--md-primary);
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border: none;
            border-radius: var(--md-radius-sm);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--md-transition-base);
            display: inline-block;
        }

        .text-link:hover {
            background-color: rgba(13, 59, 102, 0.08);
        }

        .text-link:active {
            background-color: rgba(13, 59, 102, 0.12);
        }

        .text-link:focus-visible {
            outline: 2px solid var(--md-primary);
            outline-offset: 2px;
        }

        /* Footer */
        .footer {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-xl) var(--md-spacing-md);
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-text {
            font-size: 0.875rem;
            line-height: 1.5;
            opacity: 0.9;
        }

        .footer-links {
            margin-top: var(--md-spacing-sm);
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .header-content {
                padding: 0;
            }

            .logo-text {
                font-size: 1.25rem;
            }

            .register-card {
                padding: var(--md-spacing-xl);
            }

            .main-content {
                padding: var(--md-spacing-lg) var(--md-spacing-md);
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Accessibility - Focus Indicators */
        *:focus-visible {
            outline: 2px solid var(--md-primary);
            outline-offset: 2px;
        }

        /* Loading State */
        .submit-button.loading {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .submit-button.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid var(--md-on-primary);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo-container" aria-label="Ir a inicio">
                <div class="logo" aria-hidden="true">🐾</div>
                <span class="logo-text">Patitas Felices</span>
            </a>
            <a href="index.php" class="md-button-text" aria-label="Volver al inicio">Inicio</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="register-card">
            <h1 class="md-headline-medium register-title">Crear Cuenta</h1>
            
            <?php if (!empty($mensajeError)): ?>
                <div class="alert alert-error" role="alert">
                    <span class="alert-icon" aria-hidden="true">⚠️</span>
                    <span class="alert-message"><?php echo htmlspecialchars($mensajeError); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensajeExito)): ?>
                <div class="alert alert-success" role="alert">
                    <span class="alert-icon" aria-hidden="true">✓</span>
                    <span class="alert-message"><?php echo htmlspecialchars($mensajeExito); ?></span>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST" class="register-form" id="registerForm" novalidate>
                <!-- Nombre y Apellido -->
                <div class="form-row">
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required 
                            placeholder=" "
                            autocomplete="given-name"
                            aria-label="Nombre"
                            aria-describedby="nombre-error"
                            value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        >
                        <label for="nombre">Nombre</label>
                        <div class="input-error-message" id="nombre-error">
                            <?php echo isset($erroresCampos['nombre']) ? htmlspecialchars($erroresCampos['nombre']) : ''; ?>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="apellido" 
                            name="apellido" 
                            required 
                            placeholder=" "
                            autocomplete="family-name"
                            aria-label="Apellido"
                            aria-describedby="apellido-error"
                            value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                        >
                        <label for="apellido">Apellido</label>
                        <div class="input-error-message" id="apellido-error">
                            <?php echo isset($erroresCampos['apellido']) ? htmlspecialchars($erroresCampos['apellido']) : ''; ?>
                        </div>
                    </div>
                </div>

                <!-- Correo Electrónico -->
                <div class="input-wrapper full-width">
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder=" "
                        autocomplete="email"
                        aria-label="Correo electrónico"
                        aria-describedby="email-error"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                    <label for="email">Correo electrónico</label>
                    <div class="input-error-message" id="email-error">
                        <?php echo isset($erroresCampos['correo']) ? htmlspecialchars($erroresCampos['correo']) : ''; ?>
                    </div>
                </div>

                <!-- Teléfono (Opcional) -->
                <div class="input-wrapper full-width">
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        placeholder=" "
                        autocomplete="tel"
                        aria-label="Teléfono (opcional)"
                        aria-describedby="telefono-error"
                        value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                    >
                    <label for="telefono">Teléfono <span class="optional-indicator">(opcional)</span></label>
                    <div class="input-error-message" id="telefono-error"></div>
                </div>

                <!-- Dirección (Opcional) -->
                <div class="input-wrapper full-width">
                    <input 
                        type="text" 
                        id="direccion" 
                        name="direccion" 
                        placeholder=" "
                        autocomplete="street-address"
                        aria-label="Dirección (opcional)"
                        aria-describedby="direccion-error"
                        value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>"
                    >
                    <label for="direccion">Dirección <span class="optional-indicator">(opcional)</span></label>
                    <div class="input-error-message" id="direccion-error"></div>
                </div>

                <!-- Rol -->
                <div class="input-wrapper full-width">
                    <select 
                        id="rol" 
                        name="rol" 
                        required
                        aria-label="Selecciona tu rol"
                        aria-describedby="rol-error"
                    >
                        <option value="">Selecciona tu rol</option>
                        <option value="Adoptante" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Adoptante') ? 'selected' : ''; ?>>Adoptante</option>
                        <option value="Voluntario" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Voluntario') ? 'selected' : ''; ?>>Voluntario</option>
                    </select>
                    <label for="rol">Rol</label>
                    <div class="input-error-message" id="rol-error">
                        <?php echo isset($erroresCampos['rol']) ? htmlspecialchars($erroresCampos['rol']) : ''; ?>
                    </div>
                </div>

                <!-- Contraseña y Confirmar Contraseña -->
                <div class="form-row">
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder=" "
                            autocomplete="new-password"
                            aria-label="Contraseña"
                            aria-describedby="password-error"
                        >
                        <label for="password">Contraseña</label>
                        <div class="input-error-message" id="password-error">
                            <?php echo isset($erroresCampos['contrasena']) ? htmlspecialchars($erroresCampos['contrasena']) : ''; ?>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required 
                            placeholder=" "
                            autocomplete="new-password"
                            aria-label="Confirmar contraseña"
                            aria-describedby="confirm-password-error"
                        >
                        <label for="confirm_password">Confirmar contraseña</label>
                        <div class="input-error-message" id="confirm-password-error">
                            <?php echo isset($erroresCampos['confirm_password']) ? htmlspecialchars($erroresCampos['confirm_password']) : ''; ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="submit-button" 
                    id="submitButton"
                    aria-label="Registrarse"
                >
                    Registrarse
                </button>
            </form>

            <!-- Links Section -->
            <div class="links-section">
                <a href="login.php" class="text-link">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 Patitas Felices. Todos los derechos reservados.</p>
            <p class="footer-links">Fundación dedicada al rescate y adopción de animales</p>
        </div>
    </footer>

    <script>
        // Validación y microinteracciones del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitButton = document.getElementById('submitButton');
            
            // Inputs
            const nombreInput = document.getElementById('nombre');
            const apellidoInput = document.getElementById('apellido');
            const emailInput = document.getElementById('email');
            const telefonoInput = document.getElementById('telefono');
            const direccionInput = document.getElementById('direccion');
            const rolSelect = document.getElementById('rol');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            // Error elements
            const nombreError = document.getElementById('nombre-error');
            const apellidoError = document.getElementById('apellido-error');
            const emailError = document.getElementById('email-error');
            const telefonoError = document.getElementById('telefono-error');
            const direccionError = document.getElementById('direccion-error');
            const rolError = document.getElementById('rol-error');
            const passwordError = document.getElementById('password-error');
            const confirmPasswordError = document.getElementById('confirm-password-error');

            // Mostrar errores del servidor si existen
            <?php if (!empty($erroresCampos)): ?>
                <?php foreach ($erroresCampos as $campo => $error): ?>
                    const campo_<?php echo $campo; ?> = document.getElementById('<?php echo $campo === "correo" ? "email" : str_replace("_", "-", $campo); ?>-error');
                    if (campo_<?php echo $campo; ?>) {
                        campo_<?php echo $campo; ?>.classList.add('show');
                        const input_<?php echo $campo; ?> = document.getElementById('<?php echo $campo === "correo" ? "email" : str_replace("_", "-", $campo); ?>');
                        if (input_<?php echo $campo; ?>) {
                            input_<?php echo $campo; ?>.classList.add('error');
                        }
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            // Animación de entrada suave para inputs
            const inputs = document.querySelectorAll('.input-wrapper input, .input-wrapper select');
            inputs.forEach((input, index) => {
                input.style.opacity = '0';
                input.style.transform = 'translateX(-10px)';
                setTimeout(() => {
                    input.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    input.style.opacity = '1';
                    input.style.transform = 'translateX(0)';
                }, 100 + (index * 30));
            });

            // Función para validar email
            function validarEmail(email) {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(email);
            }

            // Función para mostrar error en input
            function mostrarError(input, errorElement, mensaje) {
                input.classList.add('error');
                errorElement.textContent = mensaje;
                errorElement.classList.add('show');
            }

            // Función para limpiar error en input
            function limpiarError(input, errorElement) {
                input.classList.remove('error');
                errorElement.textContent = '';
                errorElement.classList.remove('show');
            }

            // Validación de nombre
            nombreInput.addEventListener('blur', function() {
                const nombre = this.value.trim();
                if (nombre === '') {
                    mostrarError(this, nombreError, 'El nombre es obligatorio');
                } else if (nombre.length < 2) {
                    mostrarError(this, nombreError, 'El nombre debe tener al menos 2 caracteres');
                } else {
                    limpiarError(this, nombreError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
            });

            nombreInput.addEventListener('input', function() {
                if (this.value.trim().length >= 2) {
                    limpiarError(this, nombreError);
                }
            });

            // Validación de apellido
            apellidoInput.addEventListener('blur', function() {
                const apellido = this.value.trim();
                if (apellido === '') {
                    mostrarError(this, apellidoError, 'El apellido es obligatorio');
                } else if (apellido.length < 2) {
                    mostrarError(this, apellidoError, 'El apellido debe tener al menos 2 caracteres');
                } else {
                    limpiarError(this, apellidoError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
            });

            apellidoInput.addEventListener('input', function() {
                if (this.value.trim().length >= 2) {
                    limpiarError(this, apellidoError);
                }
            });

            // Validación de email
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                if (email === '') {
                    mostrarError(this, emailError, 'El correo electrónico es obligatorio');
                } else if (!validarEmail(email)) {
                    mostrarError(this, emailError, 'Por favor, ingresa un correo válido');
                } else {
                    limpiarError(this, emailError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
            });

            emailInput.addEventListener('input', function() {
                if (this.value.trim() !== '' && validarEmail(this.value.trim())) {
                    limpiarError(this, emailError);
                }
            });

            // Validación de rol
            rolSelect.addEventListener('change', function() {
                if (this.value === '') {
                    mostrarError(this, rolError, 'Debes seleccionar un rol');
                } else {
                    limpiarError(this, rolError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
            });

            // Validación de contraseña
            passwordInput.addEventListener('blur', function() {
                const password = this.value;
                if (password === '') {
                    mostrarError(this, passwordError, 'La contraseña es obligatoria');
                } else if (password.length < 6) {
                    mostrarError(this, passwordError, 'La contraseña debe tener al menos 6 caracteres');
                } else {
                    limpiarError(this, passwordError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
                
                // Revalidar confirmación si ya tiene valor
                if (confirmPasswordInput.value !== '') {
                    confirmPasswordInput.dispatchEvent(new Event('blur'));
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value.length >= 6) {
                    limpiarError(this, passwordError);
                }
            });

            // Validación de confirmar contraseña
            confirmPasswordInput.addEventListener('blur', function() {
                const confirmPassword = this.value;
                const password = passwordInput.value;
                
                if (confirmPassword === '') {
                    mostrarError(this, confirmPasswordError, 'Debes confirmar tu contraseña');
                } else if (confirmPassword !== password) {
                    mostrarError(this, confirmPasswordError, 'Las contraseñas no coinciden');
                } else {
                    limpiarError(this, confirmPasswordError);
                    this.style.borderBottomColor = 'var(--md-success)';
                }
            });

            confirmPasswordInput.addEventListener('input', function() {
                if (this.value === passwordInput.value && this.value !== '') {
                    limpiarError(this, confirmPasswordError);
                }
            });

            // Validación completa antes de enviar el formulario
            form.addEventListener('submit', function(e) {
                let esValido = true;
                
                // Validar nombre
                const nombre = nombreInput.value.trim();
                if (nombre === '') {
                    mostrarError(nombreInput, nombreError, 'El nombre es obligatorio');
                    esValido = false;
                } else if (nombre.length < 2) {
                    mostrarError(nombreInput, nombreError, 'El nombre debe tener al menos 2 caracteres');
                    esValido = false;
                }
                
                // Validar apellido
                const apellido = apellidoInput.value.trim();
                if (apellido === '') {
                    mostrarError(apellidoInput, apellidoError, 'El apellido es obligatorio');
                    esValido = false;
                } else if (apellido.length < 2) {
                    mostrarError(apellidoInput, apellidoError, 'El apellido debe tener al menos 2 caracteres');
                    esValido = false;
                }
                
                // Validar email
                const email = emailInput.value.trim();
                if (email === '') {
                    mostrarError(emailInput, emailError, 'El correo electrónico es obligatorio');
                    esValido = false;
                } else if (!validarEmail(email)) {
                    mostrarError(emailInput, emailError, 'Por favor, ingresa un correo válido');
                    esValido = false;
                }
                
                // Validar rol
                if (rolSelect.value === '') {
                    mostrarError(rolSelect, rolError, 'Debes seleccionar un rol');
                    esValido = false;
                }
                
                // Validar contraseña
                const password = passwordInput.value;
                if (password === '') {
                    mostrarError(passwordInput, passwordError, 'La contraseña es obligatoria');
                    esValido = false;
                } else if (password.length < 6) {
                    mostrarError(passwordInput, passwordError, 'La contraseña debe tener al menos 6 caracteres');
                    esValido = false;
                }
                
                // Validar confirmar contraseña
                const confirmPassword = confirmPasswordInput.value;
                if (confirmPassword === '') {
                    mostrarError(confirmPasswordInput, confirmPasswordError, 'Debes confirmar tu contraseña');
                    esValido = false;
                } else if (confirmPassword !== password) {
                    mostrarError(confirmPasswordInput, confirmPasswordError, 'Las contraseñas no coinciden');
                    esValido = false;
                }
                
                // Si no es válido, prevenir envío
                if (!esValido) {
                    e.preventDefault();
                    return false;
                }
                
                // Efecto de carga en el botón
                submitButton.classList.add('loading');
                submitButton.disabled = true;
            });

            // Efecto ripple en el botón
            submitButton.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    left: ${x}px;
                    top: ${y}px;
                    pointer-events: none;
                    animation: ripple 0.6s ease-out;
                `;
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });

            // Animación de focus suave
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });

        // Animación de ripple CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .submit-button {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>