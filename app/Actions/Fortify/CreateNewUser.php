<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Проверяет данные формы регистрации и создаёт пользователя.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // username приводим к нижнему регистру до проверки
        if (isset($input['username']) && is_string($input['username'])) {
            $input['username'] = Str::lower(trim($input['username']));
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique(User::class),
            ],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'username.regex' => 'Логин: только латинские буквы, цифры и знак подчёркивания.',
            'username.min' => 'Логин должен содержать не менее :min символов.',
            'username.unique' => 'Такой логин уже занят.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'password.min' => 'Пароль должен содержать не менее :min символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ], [
            'name' => 'Имя',
            'username' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
        ])->validate();

        // пароль хэшируется автоматически (cast «hashed» в модели User)
        return User::create([
            'name' => $input['name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
