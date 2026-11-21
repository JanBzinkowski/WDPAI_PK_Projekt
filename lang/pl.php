<?php

declare(strict_types=1);

return [
    'validation' => [
        'required' => 'Pole :field jest wymagane.',
        'email' => 'Pole :field musi zawierać poprawny adres e-mail.',
        'min_length' => 'Pole :field musi mieć co najmniej :min znaków.',
        'max_length' => 'Pole :field może mieć maksymalnie :max znaków.',
        'int_range' => 'Pole :field musi być liczbą z zakresu od :min do :max.',
        'in_enum' => 'Wybrana wartość dla :field jest nieprawidłowa.',
        'same' => 'Pola :field oraz :other muszą być identyczne.',
    ],
    'auth' => [
        'invalid_credentials' => 'Nieprawidłowy e-mail lub hasło.',
        'email_exists' => 'Użytkownik o podanym adresie e-mail już istnieje.',
        'logout_success' => 'Zostałeś pomyślnie wylogowany.',
        'registration_success' => 'Konto zostało pomyślnie utworzone.',
    ],
    'nav' => [
        'home' => 'Strona główna',
        'decks' => 'Talie',
        'study' => 'Nauka',
        'shop' => 'Sklep',
        'profile' => 'Profil',
        'login' => 'Zaloguj się',
        'register' => 'Zarejestruj się',
        'logout' => 'Wyloguj się',
    ],
];
