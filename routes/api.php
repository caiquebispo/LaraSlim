<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

// Grupo de rotas API
$app->group('/api', function ($group) {

    // Rota de exemplo para API
    $group->get('   ', function (Request $request, Response $response) {
        $data = [
            'status' => 'ok',
            'message' => 'API funcionando',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json');
    });

    // Rota de exemplo para listar usuários do banco
    $group->get('/users', function (Request $request, Response $response) {
        try {
            // Usar Eloquent para buscar usuários
            $users = \LaraSlim\Models\User::all();

            $response->getBody()->write(json_encode($users->toArray()));
            return $response
                ->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $error = ['error' => 'Erro ao buscar usuários: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    });

    // Rota para criar um usuário
    $group->post('/users', function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();

            $user = \LaraSlim\Models\User::create([
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => password_hash($data['password'] ?? '', PASSWORD_DEFAULT),
            ]);

            $response->getBody()->write(json_encode($user->toArray()));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (Exception $e) {
            $error = ['error' => 'Erro ao criar usuário: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    });

    // Adicione mais rotas API aqui...
});