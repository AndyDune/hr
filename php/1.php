<?php

namespace Manager;

class User
{
    public const limit = 10;

    /**
     * Возвращает пользователей старше заданного возраста.
     * @param int $ageFrom
     * @return array
     */
    function getUsers(int $ageFrom): array
    {
        return \Gateway\User::getUsers($ageFrom, static::limit);
    }

    /**
     * Возвращает пользователей по списку имен.
     * @param array $names
     * @return array
     */
    public function getByNames(array $names): array
    {
        $users = [];
        foreach ($names as $name) {
            $users[] = \Gateway\User::user($name);
        }

        return $users;
    }

    /**
     * Добавляет пользователей в базу данных.
     * @param $users
     * @return array
     */
    public function users($users): array
    {
        $ids = [];
        foreach ($users as $user) {
            \Gateway\User::getInstance()->beginTransaction();
            try {
                \Gateway\User::add($user['name'], $user['lastName'], $user['age']);
                \Gateway\User::getInstance()->commit();
                $ids[] = \Gateway\User::getInstance()->lastInsertId();
            } catch (\Exception $e) {
                \Gateway\User::getInstance()->rollBack();
            }
        }

        return $ids;
    }
}