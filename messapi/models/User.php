<?php

require_once __DIR__ . '/../config/Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $database = new Database();

        $this->db =
            $database->connect();
    }


    // ========================================
    // REGISTRAR USUARIO Y RESTAURANTE
    // ========================================

    public function register(
        $restaurantName,
        $address,
        $city,
        $phone,
        $description,
        $email,
        $password
    ) {

        try {

            // ========================================
            // VERIFICAR SI EL CORREO YA EXISTE
            // ========================================

            $query = "
                SELECT id
                FROM users
                WHERE email = :email
                LIMIT 1
            ";


            $stmt =
                $this->db->prepare(
                    $query
                );


            $stmt->execute([
                ':email' =>
                    $email
            ]);


            if ($stmt->fetch()) {

                return [
                    'success' => false,

                    'message' =>
                        'El correo electrónico ya está registrado.'
                ];
            }


            // ========================================
            // INICIAR TRANSACCIÓN
            // ========================================

            $this->db->beginTransaction();


            // ========================================
            // ENCRIPTAR CONTRASEÑA
            // ========================================

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            // ========================================
            // CREAR USUARIO
            // ========================================

            $queryUser = "
                INSERT INTO users
                (
                    name,
                    email,
                    password
                )
                VALUES
                (
                    :name,
                    :email,
                    :password
                )
            ";


            $stmtUser =
                $this->db->prepare(
                    $queryUser
                );


            $stmtUser->execute([

                ':name' =>
                    $restaurantName,

                ':email' =>
                    $email,

                ':password' =>
                    $passwordHash
            ]);


            $userId =
                $this->db->lastInsertId();


            // ========================================
            // CREAR RESTAURANTE
            // ========================================

            $queryRestaurant = "
                INSERT INTO restaurants
                (
                    user_id,
                    name,
                    address,
                    city,
                    phone,
                    description,
                    is_open
                )
                VALUES
                (
                    :user_id,
                    :name,
                    :address,
                    :city,
                    :phone,
                    :description,
                    1
                )
            ";


            $stmtRestaurant =
                $this->db->prepare(
                    $queryRestaurant
                );


            $stmtRestaurant->execute([

                ':user_id' =>
                    $userId,

                ':name' =>
                    $restaurantName,

                ':address' =>
                    $address,

                ':city' =>
                    $city,

                ':phone' =>
                    $phone,

                ':description' =>
                    $description
            ]);


            $restaurantId =
                $this->db->lastInsertId();


            // ========================================
            // CREAR 10 MESAS INICIALES
            // ========================================

            $queryTable = "
                INSERT INTO tables
                (
                    restaurant_id,
                    table_number,
                    chairs,
                    status_id,
                    details
                )
                VALUES
                (
                    :restaurant_id,
                    :table_number,
                    :chairs,
                    :status_id,
                    :details
                )
            ";


            $stmtTable =
                $this->db->prepare(
                    $queryTable
                );


            for ($i = 1; $i <= 10; $i++) {

                $stmtTable->execute([

                    ':restaurant_id' =>
                        $restaurantId,

                    ':table_number' =>
                        $i,

                    ':chairs' =>
                        4,

                    ':status_id' =>
                        1,

                    ':details' =>
                        ''
                ]);
            }


            // ========================================
            // CONFIRMAR TRANSACCIÓN
            // ========================================

            $this->db->commit();


            return [

                'success' =>
                    true,

                'user_id' =>
                    (int) $userId,

                'restaurant_id' =>
                    (int) $restaurantId
            ];


        } catch (PDOException $e) {


            // ========================================
            // DESHACER SI ALGO FALLA
            // ========================================

            if (
                $this->db->inTransaction()
            ) {

                $this->db->rollBack();
            }


            return [

                'success' =>
                    false,

                'message' =>
                    'No se pudo registrar el usuario.'
            ];
        }
    }


    // ========================================
    // LOGIN
    // ========================================

    public function login(
        $email,
        $password
    ) {

        $query = "
            SELECT

                u.id AS user_id,
                u.name AS user_name,
                u.email,
                u.password,

                r.id AS restaurant_id,
                r.name AS restaurant_name

            FROM users u

            LEFT JOIN restaurants r
                ON r.user_id = u.id

            WHERE u.email = :email

            LIMIT 1
        ";


        $stmt =
            $this->db->prepare(
                $query
            );


        $stmt->execute([
            ':email' =>
                $email
        ]);


        $user =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        // ========================================
        // USUARIO INEXISTENTE
        // ========================================

        if (!$user) {

            return [

                'success' =>
                    false,

                'message' =>
                    'Correo o contraseña incorrectos.'
            ];
        }


        // ========================================
        // CONTRASEÑA INCORRECTA
        // ========================================

        if (
            !password_verify(
                $password,
                $user['password']
            )
        ) {

            return [

                'success' =>
                    false,

                'message' =>
                    'Correo o contraseña incorrectos.'
            ];
        }


        // ========================================
        // LOGIN CORRECTO
        // ========================================

        return [

            'success' =>
                true,


            'user_id' =>
                (int) $user['user_id'],


            'user_name' =>
                $user['user_name'],


            'email' =>
                $user['email'],


            'restaurant_id' =>
                $user['restaurant_id']
                    ? (int) $user['restaurant_id']
                    : null,


            'restaurant_name' =>
                $user['restaurant_name']
        ];
    }
}