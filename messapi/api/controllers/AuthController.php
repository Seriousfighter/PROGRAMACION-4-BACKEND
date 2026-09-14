<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JwtHelper.php';
require_once __DIR__ . '/../views/JsonView.php';

class AuthController
{
    public function register(): void
    {
        $b = $this->jsonBody();
        $name     = trim($b['name']  ?? '');
        $email    = trim($b['email'] ?? '');
        $password = (string) ($b['password'] ?? '');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100)       $errors['name']     = 'Obligatorio (máx. 100).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors['email']    = 'Email inválido.';
        if (strlen($password) < 6 || strlen($password) > 72) $errors['password'] = 'Entre 6 y 72 caracteres.';
        if ($errors) JsonView::render(['message' => 'Errores de validación', 'errors' => $errors], 422);

        $users = new User();
        if ($users->emailExists($email)) {
            JsonView::render(['message' => 'El email ya está registrado'], 409);
        }

        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $id    = $users->create($name, $email, $hash);
        $user  = $users->findById($id);
        $token = JwtHelper::encode(['sub' => $id, 'email' => $email]);

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