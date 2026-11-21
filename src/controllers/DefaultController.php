<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Http\Response;

final class DefaultController extends AppController
{
    public function index(): Response
    {
        return $this->render('home', [
            'pageTitle' => 'Moja aplikacja',
        ]);
    }

    public function notFound(): Response
    {
        return $this->render('not-found', [
            'pageTitle' => 'Nie znaleziono strony',
        ], 404);
    }
}
