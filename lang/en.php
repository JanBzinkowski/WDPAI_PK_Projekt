<?php

declare(strict_types=1);

return [
    'validation' => [
        'required' => 'The :field field is required.',
        'email' => 'The :field field must be a valid email address.',
        'min_length' => 'The :field field must be at least :min characters.',
        'max_length' => 'The :field field may not be greater than :max characters.',
        'int_range' => 'The :field field must be between :min and :max.',
        'in_enum' => 'The selected value for :field is invalid.',
        'same' => 'The :field and :other fields must match.',
    ],
    'auth' => [
        'invalid_credentials' => 'Invalid email or password.',
        'email_exists' => 'A user with this email address already exists.',
        'logout_success' => 'You have been successfully logged out.',
        'registration_success' => 'Account successfully created.',
    ],
    'nav' => [
        'home' => 'Home',
        'decks' => 'Decks',
        'study' => 'Study',
        'shop' => 'Shop',
        'profile' => 'Profile',
        'login' => 'Sign In',
        'register' => 'Sign Up',
        'logout' => 'Log Out',
    ],
];
