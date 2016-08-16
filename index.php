<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Client {

	var $guzzle;
	var $base_url;
	var $api_key;
	
	public function Client() {
		$this->guzzle   = new \GuzzleHttp\Client();
		$this->base_url = "https://api.themoviedb.org/3/";
		$this->api_key  = "e32124517b8376484c88e5a11668e03b";
    }
    public function getResults($query,$page = 1) {
    	$url = $this->base_url."search/multi?query=".$query."&api_key=".$this->api_key."&language='en-US'&page=".$page;
        return $this->guzzle->request('GET', $url)->getBody();
    }
    public function getMovie($movie_id) {
    	$url = $this->base_url."movie/".$movie_id."?api_key=".$this->api_key;
        return $this->guzzle->request('GET', $url)->getBody();
    }
    public function getPerson($person_id) {
    	$url = $this->base_url."person/".$person_id."?api_key=".$this->api_key;
        return $this->guzzle->request('GET', $url)->getBody();
    }
    public function getMoviesByActor($actor_id, $page = 1) {
    	$url = $this->base_url."discover/movie?with_cast=".$actor_id."&api_key=".$this->api_key."&language='en-US'&sort_by=release_date.desc&page=".$page;
        return $this->guzzle->request('GET', $url)->getBody();
    }
}

$app = new \Slim\App;

$app->get('/search/{query}/page/{page}', function (Request $request, Response $response) {
	$query    = $request->getAttribute('query');
	$page     = $request->getAttribute('page');
	
	$client   = new Client;
	$res      = $client->getResults($query,$page);
	
	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});


$app->get('/movie/{movie_id}', function (Request $request, Response $response) {
	$movie_id = $request->getAttribute('movie_id');
	
	$client   = new Client;
	$res      = $client->getMovie($movie_id);
	
	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});

$app->get('/person/{person_id}', function (Request $request, Response $response) {
	$person_id = $request->getAttribute('person_id');
	
	$client   = new Client;
	$res      = $client->getPerson($person_id);
	
	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});

$app->get('/actor/{actor_id}/movies/page/{page}', function (Request $request, Response $response) {
	$actor_id = $request->getAttribute('actor_id');
	$page     = $request->getAttribute('page');
	
	$client   = new Client;
	$res      = $client->getMoviesByActor($actor_id, $page);
	
	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});
$app->run();