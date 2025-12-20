<?php

/**
 * ==================================================================================
 * DECLARACIÓN DE TIPOS ESTRICTOS (strict_types)
 * ==================================================================================
 */
declare(strict_types=1);

namespace App\Models;

use App\DB\ConnectionDB;
use PDO;
use PDOException;

/**
 * ==================================================================================
 * MODELO DE PRODUCTO (ProductModel)
 * ==================================================================================
 */
class ProductModel
{
    private PDO $connection;
    private string $table = 'productos';

    public function __construct()
    {
        $this->connection = ConnectionDB::getConnection();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->connection->query("SELECT ID_PRODUCTOS, name, description, stock, url, imageName FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->connection->prepare("SELECT ID_PRODUCTOS, name, description, stock, url, imageName FROM {$this->table} WHERE ID_PRODUCTOS = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (name, description, stock, url, imageName, IDtoken) VALUES (:name, :description, :stock, :url, :imageName, :IDtoken)"
            );

            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':description', $data['description']);
            $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT);
            $stmt->bindValue(':url', $data['url']);
            $stmt->bindValue(':imageName', $data['imageName']);
            $stmt->bindValue(':IDtoken', $data['IDtoken']);

            $stmt->execute();

            return (int)$this->connection->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $allowedFields = ['name', 'description', 'stock', 'url', 'imageName', 'IDtoken'];
            foreach (array_keys($data) as $field) {
                if (in_array($field, $allowedFields, true)) {
                    $fields[] = "{$field} = :{$field}";
                }
            }
            if (empty($fields)) {
                return false;
            }
            $query_fields = implode(', ', $fields);

            $stmt = $this->connection->prepare(
                "UPDATE {$this->table} SET {$query_fields} WHERE ID_PRODUCTOS = :id"
            );

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields, true)) {
                    $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $stmt->bindValue(":{$key}", $value, $paramType);
                }
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE ID_PRODUCTOS = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
