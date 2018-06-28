<?php
//Autoload
$loader = require 'vendor/autoload.php';

//Instanciando objeto
$app = new \Slim\Slim(array(
    'templates.path' => 'templates'
));

$app->get('/', function() use ($app){
	//echo "sss";
});

//Listando todas
$app->get('/pessoas/', function() use ($app){
	(new \controllers\Pessoa($app))->lista();
});

//get pessoa
$app->get('/pessoas/:id', function($id) use ($app){
	(new \controllers\Pessoa($app))->get($id);
});

//nova pessoa
$app->post('/pessoas/', function() use ($app){
	(new \controllers\Pessoa($app))->nova();
	
});

//rota update pessoa
$app->put('/pessoas/:id/:tabela', function($id,$tabela) use ($app){
	(new \controllers\Pessoa($app))->editar($id,$tabela);
});

//rota delete passando id e tabela
$app->delete('/pessoas/:id/:tabela', function($id,$tabela) use ($app){
	(new \controllers\Pessoa($app))->excluir($id,$tabela);
});

//Rodando aplicação
$app->run();
