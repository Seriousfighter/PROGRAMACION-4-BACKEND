<?php
require_once __DIR__ . '/../models/User.php'; // Importamos la clase User para poder interactuar con la tabla de usuarios
require_once __DIR__ . '/../helpers/JwtHelper.php'; // Importamos el helper para manejar tokens JWT
require_once __DIR__ . '/../views/JsonView.php'; // Importamos la clase JsonView para poder enviar respuestas en formato JSON

// AuthController se encarga de manejar las rutas relacionadas con la autenticación y gestión de usuarios.

class AuthController 
{
    public function register(): void
    {
        $b = $this->jsonBody(); // Obtenemos los datos del cuerpo de la solicitud
        $name     = trim($b['name']  ?? ''); // Obtenemos el nombre del usuario, eliminando espacios en blanco al inicio y al final
        $email    = trim($b['email'] ?? ''); // Obtenemos el email del usuario, eliminando espacios en blanco al inicio y al final
        $password = (string) ($b['password'] ?? ''); // Obtenemos la contraseña del usuario, asegurándonos de que sea una cadena de texto

        // Validamos los datos recibidos
        $errors = [];
        if ($name === '' || mb_strlen($name) > 100)       $errors['name']     = 'Obligatorio (máx. 100).'; 
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors['email']    = 'Email inválido.';
        if (strlen($password) < 6 || strlen($password) > 72) $errors['password'] = 'Entre 6 y 72 caracteres.';
        if ($errors) JsonView::render(['message' => 'Errores de validación', 'errors' => $errors], 422);

        // Verificamos si el email ya está registrado
        $users = new User();
        if ($users->emailExists($email)) {
            JsonView::render(['message' => 'El email ya está registrado'], 409);
        }

        // Creamos el usuario y generamos un token JWT
        $hash  = password_hash($password, PASSWORD_BCRYPT); // Hasheamos la contraseña para almacenarla de forma segura
        $id    = $users->create($name, $email, $hash);// Creamos el usuario en la base de datos y obtenemos su ID
        $user  = $users->findById($id);// Obtenemos los datos del usuario recién creado
        $token = JwtHelper::encode(['sub' => $id, 'email' => $email]);// Generamos un token JWT con el ID y email del usuario

        // Respondemos con el usuario creado y el token
        JsonView::render(['user' => $user, 'token' => $token], 201);
    }

    public function login(): void
    {
        $b = $this->jsonBody();
        $email    = trim($b['email'] ?? '');
        $password = (string) ($b['password'] ?? '');

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email']    = 'Email inválido.';
        if ($password === '')                            $errors['password'] = 'La contraseña es obligatoria.';
        if ($errors) JsonView::render(['message' => 'Errores de validación', 'errors' => $errors], 422);

        $users = new User();
        $user  = $users->findByEmailWithPassword($email);

        if (!$user || !password_verify($password, $user['password'])) {
            JsonView::render(['message' => 'Credenciales inválidas'], 401);
        }

        unset($user['password']);
        $token = JwtHelper::encode(['sub' => (int) $user['id'], 'email' => $user['email']]);

        JsonView::render(['user' => $user, 'token' => $token], 200);
    }

    public function logout(): void
    {
        JsonView::render(null, 204);
    }

    public function me(int $userId): void
    {
        $user = (new User())->findById($userId);
        if (!$user) JsonView::render(['message' => 'Usuario no encontrado'], 404);
        JsonView::render(['user' => $user], 200);
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) return [];
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
}