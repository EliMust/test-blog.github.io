<?php

namespace MyProject\Models\Users;

use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Models\ActiveRecordEntity;

class User extends ActiveRecordEntity
{
    /**@var string */
    protected $nickname;

    /**@var string */
    protected $email;

    /**@var int */
    protected $isConfirmed;

    /**@var string */
    protected $role;

    /**@var string */
    protected $passwordHash;

    /**@var string */
    protected $authToken;

    /**@var string */
    protected $createdAt;

    /**
     * @return string
    */

    public function getNickname() : string 
    {
        return $this->nickname;
    }

    /**
     * @return string 
    */

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getAuthToken(): string 
    {
        return $this->authToken;
    }

    public function getPasswordHash(): string 
    {
        return $this->passwordHash;
    }

    public function activate(): void {
        $this->isConfirmed = true;
        $this->save();
    }
 
    //создание нового пользователя и сохранение его в БД
    public static function signUp(array $userData)
    {
        //проверка на то, что никнейм передан
        if (empty($userData['nickname'])) {
            throw new InvalidArgumentException('Не передан nickname');
        }

        //проверка того, что nickname содержит только допустимые символы
        if (!preg_match('/^[a-zA-Z0-9]+$/', $userData['nickname'])) {
            throw new InvalidArgumentException('Nickname может состоять только из символов латинского алфавита и цифр');
        }

        //проверка на то, что email передан
        if (empty($userData['email'])) {
            throw new InvalidArgumentException('Не передан email');
        }

        //проверка того, что email является корректным email-ом
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email некорректен');
        }

        //проверка на то, что password передан
        if (empty($userData['password'])) {
            throw new InvalidArgumentException('Не передан password');
        }

        //проверка того, что длина пароля не менее восьми символов
        if (mb_strlen($userData['password']) < 8) {
            throw new InvalidArgumentException('Пароль должен быть не менее 8 символов');
        }
        
        //проверяем, что нет дубликатов, т.е. реализуем то, что описали в ActiveRecordEntity
        if (static::findOneByColumn('nickname', $userData['nickname']) !== null) {
            throw new InvalidArgumentException('Пользователь с таким nickname уже существует');
        }

        if (static::findOneByColumn('email', $userData['email']) !== null) {
            throw new InvalidArgumentException('Пользователь с таким email уже существует');
        }

        //когда все проверки пройдены, создаем нового пользователя
        $user = new User();
        $user->nickname = $userData['nickname'];
        $user->email = $userData['email'];
        $user->passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        $user->isConfirmed = false;
        $user->role = 'user';
        $user->authToken = sha1(random_bytes(100)) . sha1(random_bytes(100));

        $user->save();

        return $user;
    }

    public static function login(array $loginData): User
    {
        if (empty($loginData['email'])) {
            throw new InvalidArgumentException('Не передан email');
        }

        if (empty($loginData['password'])) {
            throw new InvalidArgumentException('Не передан password');
        }

        $user = User::findOneByColumn('email', $loginData['email']);

        if ($user === null) {
            throw new InvalidArgumentException('Нет пользователя с таким email');
        }

        if (!password_verify($loginData['password'], $user->getPasswordHash())) {
            throw new InvalidArgumentException('Неправильный пароль');
        }

        if (!$user->isConfirmed) {
            throw new InvalidArgumentException('Пользователь не подтверждён');
        }

        $user->refreshAuthToken();
        $user->save();

        return $user;
    }

    public function isAdmin(): bool
    {
        return $this->role === admin;
    }

    protected static function getTableName(): string
    {
        return 'users';
    }

    private function refreshAuthToken()
    {
        $this->authToken = sha1(random_bytes(100)) . sha1(random_bytes(100));
    }

}