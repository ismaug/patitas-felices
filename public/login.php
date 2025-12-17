<?php
/**
 * Página de Inicio de Sesión - CU-02
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

// Procesar formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $correo = $_POST['email'] ?? '';
        $contrasena = $_POST['password'] ?? '';
        
        // Instanciar servicio de autenticación
        $servicioAuth = new ServicioUsuariosAuth();
        
        // Llamar al método de inicio de sesión
        $resultado = $servicioAuth->iniciarSesion($correo, $contrasena);
        
        // Verificar si el login fue exitoso
        if ($resultado->isSuccess()) {
            // Obtener datos del usuario
            $datosUsuario = $resultado->getData();
            
            // DEBUG: Logging detallado para diagnóstico
            error_log("=== DEBUG LOGIN ===");
            error_log("Usuario ID: " . $datosUsuario['id_usuario']);
            error_log("Nombre: " . $datosUsuario['nombre']);
            error_log("Rol detectado: '" . $datosUsuario['rol'] . "'");
            error_log("ID Rol: " . $datosUsuario['id_rol']);
            error_log("Buscando 'Coordinador' en: '" . $datosUsuario['rol'] . "'");
            error_log("Resultado strpos: " . (strpos($datosUsuario['rol'], 'Coordinador') !== false ? 'TRUE' : 'FALSE'));
            
            // Iniciar sesión PHP y guardar datos del usuario
            $_SESSION['usuario_id'] = $datosUsuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $datosUsuario['nombre'];
            $_SESSION['usuario_apellido'] = $datosUsuario['apellido'];
            $_SESSION['usuario_correo'] = $datosUsuario['correo'];
            $_SESSION['usuario_rol'] = $datosUsuario['rol'];
            $_SESSION['usuario_id_rol'] = $datosUsuario['id_rol'];
            $_SESSION['autenticado'] = true;
            $_SESSION['fecha_login'] = date('Y-m-d H:i:s');
            
            // Redirigir según el rol del usuario
            $rol = $datosUsuario['rol'];
            
            // Verificar si es Coordinador (Adopciones o Rescates)
            if (strpos($rol, 'Coordinador') !== false) {
                header('Location: dashboard-coordinador.php');
                exit;
            }
            
            switch ($rol) {
                case 'Veterinario':
                    header('Location: dashboard-veterinario.php');
                    exit;
                case 'Voluntario':
                    header('Location: dashboard-voluntario.php');
                    exit;
                case 'Adoptante':
                    header('Location: dashboard-adoptante.php');
                    exit;
                case 'Admin':
                    header('Location: dashboard-coordinador.php'); // Admin usa dashboard coordinador
                    exit;
                default:
                    header('Location: dashboard.php');
                    exit;
            }
        } else {
            // Login falló - mostrar mensaje de error
            $mensajeError = $resultado->getMessage();
        }
        
    } catch (Exception $e) {
        // Manejar errores de forma amigable
        error_log("Error en login.php: " . $e->getMessage());
        $mensajeError = 'Error al procesar el inicio de sesión. Por favor, intenta nuevamente.';
    }
}

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Patitas Felices</title>
    <meta name="description" content="Inicia sesión en Patitas Felices para acceder a tu cuenta y gestionar adopciones, voluntariado y más.">
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

        /* Login Card */
        .login-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-xl);
            box-shadow: var(--md-elevation-3);
            padding: var(--md-spacing-2xl);
            width: 100%;
            max-width: 440px;
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

        .login-title {
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
        .login-form {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-lg);
        }

        /* Input Container with Floating Label */
        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-wrapper input {
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

        .input-wrapper input:focus {
            background-color: var(--md-surface-bright);
            border-bottom-color: var(--md-primary);
        }

        .input-wrapper input.error {
            border-bottom-color: var(--md-error);
        }

        .input-wrapper input:focus + label,
        .input-wrapper input:not(:placeholder-shown) + label {
            top: 4px;
            font-size: 0.75rem;
            color: var(--md-primary);
        }

        .input-wrapper input.error:focus + label,
        .input-wrapper input.error:not(:placeholder-shown) + label {
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

        .input-wrapper input:focus + label {
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

            .login-card {
                padding: var(--md-spacing-xl);
            }

            .main-content {
                padding: var(--md-spacing-lg) var(--md-spacing-md);
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
        <div class="login-card">
            <h1 class="md-headline-medium login-title">Iniciar Sesión</h1>
            
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
            
            <form action="login.php" method="POST" class="login-form" id="loginForm" novalidate>
                <!-- Email Input -->
                <div class="input-wrapper">
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
                    <div class="input-error-message" id="email-error"></div>
                </div>

                <!-- Password Input -->
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder=" "
                        autocomplete="current-password"
                        aria-label="Contraseña"
                        aria-describedby="password-error"
                    >
                    <label for="password">Contraseña</label>
                    <div class="input-error-message" id="password-error"></div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="submit-button" 
                    id="submitButton"
                    aria-label="Iniciar sesión"
                >
                    Iniciar Sesión
                </button>
            </form>

            <!-- Links Section -->
            <div class="links-section">
                <a href="register.php" class="text-link">¿No tienes cuenta? Regístrate</a>
                <a href="#" class="text-link">¿Olvidaste tu contraseña?</a>
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
            const form = document.getElementById('loginForm');
            const submitButton = document.getElementById('submitButton');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');

            // Animación de entrada suave para inputs
            const inputs = document.querySelectorAll('.input-wrapper input');
            inputs.forEach((input, index) => {
                input.style.opacity = '0';
                input.style.transform = 'translateX(-10px)';
                setTimeout(() => {
                    input.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    input.style.opacity = '1';
                    input.style.transform = 'translateX(0)';
                }, 100 + (index * 50));
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

            // Validación en tiempo real del email
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
                    this.style.borderBottomColor = 'var(--md-primary)';
                }
            });

            // Validación en tiempo real de la contraseña
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
            });

            passwordInput.addEventListener('input', function() {
                if (this.value.length >= 6) {
                    limpiarError(this, passwordError);
                    this.style.borderBottomColor = 'var(--md-primary)';
                }
            });

            // Validación completa antes de enviar el formulario
            form.addEventListener('submit', function(e) {
                let esValido = true;
                
                // Validar email
                const email = emailInput.value.trim();
                if (email === '') {
                    mostrarError(emailInput, emailError, 'El correo electrónico es obligatorio');
                    esValido = false;
                } else if (!validarEmail(email)) {
                    mostrarError(emailInput, emailError, 'Por favor, ingresa un correo válido');
                    esValido = false;
                } else {
                    limpiarError(emailInput, emailError);
                }
                
                // Validar contraseña
                const password = passwordInput.value;
                if (password === '') {
                    mostrarError(passwordInput, passwordError, 'La contraseña es obligatoria');
                    esValido = false;
                } else if (password.length < 6) {
                    mostrarError(passwordInput, passwordError, 'La contraseña debe tener al menos 6 caracteres');
                    esValido = false;
                } else {
                    limpiarError(passwordInput, passwordError);
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

            // Accesibilidad: Navegación con teclado mejorada
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
                    const focusedInput = document.activeElement;
                    if (focusedInput === emailInput) {
                        passwordInput.focus();
                        e.preventDefault();
                    } else if (focusedInput === passwordInput) {
                        form.dispatchEvent(new Event('submit', { cancelable: true }));
                        e.preventDefault();
                    }
                }
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