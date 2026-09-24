# 📋 Project Brief: Messapi
**Sistema de Gestión de Disponibilidad para Restaurantes**

---

## 1. Visión General y Objetivo del Proyecto
El proyecto consiste en la creación de una API REST destinada a un sistema de gestión de disponibilidad de mesas para restaurantes. El objetivo principal es permitir a los administradores gestionar sus locales y actualizar el estado de sus mesas en tiempo real, mientras se proporciona un portal público para que los clientes consulten qué restaurantes tienen mayor disponibilidad.

## 2. Usuarios y Roles

* **🛡️ Administradores (Privado):** 
  * Usuarios que deben registrarse con nombre, email y una contraseña segura. 
  * Tras iniciar sesión, se autentican mediante un Token JWT. 
  * Su rol les permite crear, consultar, modificar y eliminar exclusivamente los restaurantes que ellos administran, así como gestionar las mesas asociadas a estos.
* **👥 Clientes (Público):** 
  * Usuarios que acceden a una pantalla pública sin necesidad de autenticación. 
  * Su función es consumir un endpoint público que devuelve una lista de los restaurantes ordenados de manera descendente según la cantidad de mesas que tienen disponibles.

## 3. Alcance y Funcionalidades Clave

* **Seguridad y Autenticación:** Es obligatorio el uso de hashing (como bcrypt o argon2) para no guardar las contraseñas en texto plano. Los recursos privados están protegidos mediante tokens JWT, los cuales son validados para verificar su presencia, formato, firma, expiración y la existencia del usuario.
* **Módulo de Restaurantes:** Permite realizar operaciones de creación, lectura, actualización y borrado (CRUD) de los datos del local, incluyendo nombre, dirección, teléfono y descripción.
* **Módulo de Mesas:** Permite agregar mesas definiendo su detalle, cantidad de sillas y estado. Incluye una funcionalidad de cambio de estado rápido mediante un endpoint específico (`PATCH`) que, con un clic en el frontend, rota la mesa al siguiente estado disponible (ej. *Disponible → Ocupada → Reservada → Disponible*) sin que el cliente envíe el nuevo estado.
* **Consulta de Disponibilidad:** Un endpoint público que utiliza consultas optimizadas en la base de datos (con `COUNT`, `WHERE`, `GROUP BY` y `ORDER BY`) para listar la disponibilidad.

## 4. Arquitectura y Stack Tecnológico

* **Lenguaje:** Desarrollo 100% en **PHP**.
* **Base de Datos:** **MySQL** con relaciones estructuradas mediante Claves Primarias y Foráneas, distribuidas en las tablas `users`, `restaurants`, `tables` y `table_statuses`.
* **Arquitectura de Software:** Arquitectura desacoplada que separa Frontend, API y Base de Datos. Internamente, la API sigue un patrón de diseño en capas estructurado en: *Rutas → Controladores → Validación → Servicios → Modelo/ORM*.
* **Estándares de la API:** API REST utilizando **JSON** como estándar de intercambio. Se aplican de forma estricta los verbos HTTP (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`) y se hace un uso semántico de los códigos de estado HTTP (200, 201, 204, 400, 401, 403, 404, 409, 422, 500).

## 5. Hoja de Ruta (Roadmap)

El proyecto se divide en las siguientes etapas clave:

- [x] **Etapas 0 y 1:** Definición del alcance, casos de uso, modelo Entidad-Relación, wireframes y plan de pruebas.
- [x] **Etapas 2 y 3:** Estructura base del repositorio, configuración de variables, Docker, Git y creación de la BD con datos de prueba.
- [x] **Etapas 4, 5 y 6:** Autenticación JWT y desarrollo por capas del CRUD de Restaurantes y Mesas (incluyendo rotación de estado).
- [ ] **Etapas 7 y 8:** Desarrollo del endpoint público optimizado e integración de las APIs con el Frontend.
- [ ] **Etapas 9 y 10:** Pruebas funcionales, de integración y seguridad, finalizando con la documentación técnica completa.