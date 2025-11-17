<?php
/**
 * ServiceResult - Clase para respuestas consistentes de servicios
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase estandariza las respuestas de todos los servicios,
 * proporcionando un formato JSON consistente para éxito y errores.
 */

class ServiceResult {
    /**
     * @var bool Indica si la operación fue exitosa
     */
    private bool $success;
    
    /**
     * @var string Mensaje descriptivo del resultado
     */
    private string $message;
    
    /**
     * @var mixed Datos adicionales de la respuesta (opcional)
     */
    private $data;
    
    /**
     * @var array Errores de validación o detalles adicionales (opcional)
     */
    private array $errors;

    /**
     * Constructor privado para forzar uso de métodos estáticos
     *
     * @param bool $success Estado de la operación
     * @param string $message Mensaje descriptivo
     * @param mixed $data Datos adicionales
     * @param array $errors Lista de errores
     */
    private function __construct(bool $success, string $message, $data = null, array $errors = []) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }

    /**
     * Crea un resultado exitoso
     *
     * @param string $message Mensaje de éxito
     * @param mixed $data Datos a retornar (opcional)
     * @return ServiceResult
     */
    public static function success(string $message, $data = null): ServiceResult {
        return new self(true, $message, $data, []);
    }

    /**
     * Crea un resultado de error
     *
     * @param string $message Mensaje de error
     * @param array $errors Lista de errores detallados (opcional)
     * @return ServiceResult
     */
    public static function error(string $message, array $errors = []): ServiceResult {
        return new self(false, $message, null, $errors);
    }

    /**
     * Obtiene el estado de éxito
     *
     * @return bool
     */
    public function isSuccess(): bool {
        return $this->success;
    }

    /**
     * Obtiene el mensaje
     *
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * Obtiene los datos
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Obtiene los errores
     *
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Convierte el resultado a un array asociativo
     *
     * @return array
     */
    public function toArray(): array {
        $result = [
            'success' => $this->success,
            'message' => $this->message
        ];

        if ($this->data !== null) {
            $result['data'] = $this->data;
        }

        if (!empty($this->errors)) {
            $result['errors'] = $this->errors;
        }

        return $result;
    }

    /**
     * Convierte el resultado a JSON
     *
     * @return string
     */
    public function toJson(): string {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Representación en string del resultado
     *
     * @return string
     */
    public function __toString(): string {
        return $this->toJson();
    }
}