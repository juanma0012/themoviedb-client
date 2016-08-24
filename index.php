<?php
ini_set('display_errors', 0);

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// This class has attributes and methods to makes requests to the external API. 
class Client {

	var $guzzle;
	var $base_url;
	var $api_key;
	
	public function Client() {
		// Guzzle is a PHP HTTP client that makes it easy to send HTTP requests.
		$this->guzzle   = new \GuzzleHttp\Client();
		$this->base_url = "https://api.themoviedb.org/3/";
		$this->api_key  = "e32124517b8376484c88e5a11668e03b";
    }
    //Each method makes request to the API and gets a json. Also, it's necessary to include the API key to access to the server. If the request doesn't get any information, the method return an empty json.

    public function getMovie($movie_id) {
    	try {
    		// the movie/:id  request gets a json with the information of a specific movie.
    		$url = $this->base_url."movie/".$movie_id."?api_key=".$this->api_key;
        	return $this->guzzle->request('GET', $url)->getBody();
    	} catch (GuzzleHttp\Exception\ClientException $e) {
			return json_encode (json_decode ("{}"));
		}
    }
    public function getPerson($person_id) {
    	try {
    		// the person/:id  request gets a json with the information of a specific person.
	    	$url = $this->base_url."person/".$person_id."?api_key=".$this->api_key;
	        return $this->guzzle->request('GET', $url)->getBody();
	    } catch (GuzzleHttp\Exception\ClientException $e) {
			return json_encode (json_decode ("{}"));
		}
    }
    public function getPopularMovies() {
    	try {
    		// the movie/popular  request gets a json with a list of popular movies.
    		$url = $this->base_url."movie/popular?api_key=".$this->api_key;
        	return $this->guzzle->request('GET', $url)->getBody();
        } catch (GuzzleHttp\Exception\ClientException $e) {
			return json_encode (json_decode ("{}"));
		}
    }
    public function getMoviesByActor($actor_id, $page = 1) {
    	try {
    		// the discover/movie  request gets a json with a list of  movies from a specific actor..
    		$url = $this->base_url."discover/movie?with_cast=".$actor_id."&api_key=".$this->api_key."&language='en-US'&sort_by=release_date.desc&page=".$page;
        	return $this->guzzle->request('GET', $url)->getBody();
        } catch (GuzzleHttp\Exception\ClientException $e) {
			return json_encode (json_decode ("{}"));
		}
    }
    public function getResults($query,$page = 1) {
    	try {
	    	// the search/multi request gets a json with a list of people and movies that their name or title matches with the query.
	    	$url = $this->base_url."search/multi?query=".$query."&api_key=".$this->api_key."&language='en-US'&page=".$page;
        	$json_result = json_decode($this->guzzle->request('GET', $url)->getBody());
        } catch (GuzzleHttp\Exception\ClientException $e) {
			return json_encode (json_decode ("{}"));
		}
		// in this section, it check each item of the list.
        foreach ($json_result->results as $result) {
        	// it compares if it's a person.
			if ($result->media_type=="person") {
				$actor_id = $result->id;
				$page = 0;
				// it searchs all the movies where the person has acted. The iteration is to get all the movies, for every page that the API offers. After that, the result is included in the list.
				do {
					$page++;
					$movies   = json_decode($this->getMoviesByActor($actor_id, $page));
						foreach ($movies->results as $movie) {
							$movie->media_type = 'movie';
							$movie->actor_name = $result->name;
							$json_result->results[] = $movie;
					}
					if (!$movies->total_pages) {
						$movies->total_pages =1;
					}
				} while ( $page != $movies->total_pages);
			}
		}
		return json_encode($json_result);
    }
}
//Slim is a PHP micro framework to creates APIs.
$app = new \Slim\App;
// Each URI is unique, it can have attributes, but it's not necessary, the function has a request and response, the response has a header, and the response's body is a json. The request has attributes for give as parameters to the client class.

$app->get('/', function (Request $request, Response $response) {
	
	$client   = new Client;
	$res      = json_encode (json_decode ("{}"));
	
	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});

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
	// If the json is empty, the code status is 404.
	if(empty(json_decode($res, true))){
		$response = $response->withStatus(404);
	} 

	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});

$app->get('/person/{person_id}', function (Request $request, Response $response) {
	$person_id = $request->getAttribute('person_id');
	
	$client   = new Client;
	$res      = $client->getPerson($person_id);
	
	if(empty(json_decode($res, true))){
		$response = $response->withStatus(404);
	} 

	$response = $response->withAddedHeader('Content-type', 'application/json');
	$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
	$response->getBody()->write($res);
    return $response;
});

$app->get('/movies/popular', function (Request $request, Response $response) {
	
	$client   = new Client;
	$res      = $client->getPopularMovies();
	
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