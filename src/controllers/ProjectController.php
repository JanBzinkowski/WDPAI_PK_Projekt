<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Http\Response;

final class ProjectController extends AppController
{
    public function index(): Response
    {
        return $this->render('projects', [
            'pageTitle' => 'Projekty',
        ]);
    }

    public function show(int $id): Response
    {
        return $this->render('project', [
            'pageTitle' => 'Szczegóły projektu',
            'projectId' => $id,
        ]);
    }
}
