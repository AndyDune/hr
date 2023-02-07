<?php

namespace Gateway;

use PDO;

class User
{
    /**
     * @var PDO
     */
     private static PDO|null $instance = null;

    /**
     * Реализация singleton
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (is_null(self::$instance)) {
            $dsn = 'mysql:dbname=db;host=127.0.0.1';
            $user = 'dbuser';
            $password = 'dbpass';
            self::$instance = new PDO($dsn, $user, $password);
        }

        return self::$instance;
    }

    /**
     * Возвращает список пользователей старше заданного возраста.
     * @param int $ageFrom
     * @param int $limit
     * @return array
     */
    public static function getUsers(int $ageFrom, int $limit): array
    {
        $stmt = self::getInstance()->prepare("
                SELECT `id`, `name`, `lastName`, `from`, `age`, `settings` 
                FROM Users 
                WHERE `age` > :ageFrom 
                LIMIT :limit
                ");
        $stmt->execute(['ageFrom' => $ageFrom, 'limit' => $limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $settings = json_decode($row['settings']);
            if (!is_array($settings)) {
                $settings = [];
            }
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => $settings['key'] ?? null,
            ];
        }

        return $users;
    }

    /**
     * Возвращает пользователя по имени.
     * @param string $name
     * @return array
     */
    public static function user(string $name): array
    {
        $stmt = self::getInstance()->prepare("
                SELECT `id`, `name`, `lastName`, `from`, `age`, `settings` 
                FROM Users 
                WHERE `name` = :name
                ");
        $stmt->execute(['name' => $name]);
        $user_by_name = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'id' => $user_by_name['id'],
            'name' => $user_by_name['name'],
            'lastName' => $user_by_name['lastName'],
            'from' => $user_by_name['from'],
            'age' => $user_by_name['age'],
        ];
    }

    /**
     * Добавляет пользователя в базу данных.
     * @param string $name
     * @param string $lastName
     * @param int $age
     * @return string|bool
     */
    public static function add(string $name, string $lastName, int $age): string|false
    {
        $sth = self::getInstance()->prepare("
            INSERT INTO Users (`name`, `lastName`, `age`) 
            VALUES (:name, :lastName, :age)
            ");
        $sth->execute(['name' => $name, 'age' => $age, 'lastName' => $lastName]);

        return self::getInstance()->lastInsertId();
    }
}