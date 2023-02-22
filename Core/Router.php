<?php

namespace Core; 

/**
 * Class Router
 *
 * @category  Router
 * @package   Router
 * @author    Sadiq <sadiq.developer.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   2.0
 * @package   Alkane\Router
 */


class Router {

    private $routes = array();

    private $defaultMethod = 'main';

    private $routeParamPattern = '\{((int|string|str)\:)?([a-z0-9]+)\}';

    private $parameters = array();

    private $errCallback;


    public function route(string $requestMethod, string $route, string|callable $controler, string $method = null) {

        if ($method === null) {
            $method = $this->defaultMethod;
            if (is_string($controler)) {
                $contrl = @explode('::', $controler);
                if (isset($contrl[0])) {
                    $controler = $contrl[0];
                }
                if (isset($contrl[1])) {
                    $method = str_replace('()', '', $contrl[1]);
                }
            }
        }

        $this->routes[] = [
            'route' => $route,
            'requestMethod' => $requestMethod,
            'controler' => $controler,
            'method' => $method
        ];

    }

    public function get(string $route, string|callable $controler, string $method = null) {
        $this->route('GET', $route, $controler, $method);
    }

    public function post(string $route, string|callable $controler, string $method = null) {
        $this->route('POST', $route, $controler, $method);
    }

    public function put(string $route, string|callable $controler, string $method = null) {
        $this->route('PUT', $route, $controler, $method);
    }

    public function delete(string $route, string|callable $controler, string $method = null) {
        $this->route('DELETE', $route, $controler, $method);
    }

    public function basepath() {
        $path = parse_url(trim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH);
        return ($path == null || empty($path)) ? '' : urldecode($path);
    }

    public function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }


    private function isParamExist(string $route) {
        preg_match_all('/'. $this->routeParamPattern . '/i', $route, $matches);
        return array_slice($matches, 2);
    }

    private function isRequestMethodValid(string $method) {
        return (bool)(strtolower($method) === strtolower($this->getRequestMethod()));
    }

    public function run(string $basepath = null) {

        if ($basepath == null || empty($basepath)) {
            $basepath = $this->basepath();
        }

        $basepath = trim($basepath, '/');
        $routeIndex = -1;
        foreach ($this->routes as $key => $value) {
            $route = $value['route'];
            $paramInfo = array();
            if ($routeParams = $this->isParamExist($route)) {
                
                for ($paramIndex = 0; $paramIndex < count($routeParams[1]); $paramIndex++) {

                    $paramInfo[$paramIndex]['paramType'] = !empty($routeParams[0][$paramIndex]) ? strtolower($routeParams[0][$paramIndex]) : null;
                    $paramInfo[$paramIndex]['paramName'] = $routeParams[1][$paramIndex];

                    switch ($paramInfo[$paramIndex]['paramType']) {
                        case 'int':
                            $regex = '[0-9]+';
                            break;
                        case 'string':
                        case 'str':
                            $regex = '.+';
                            break;
                        default:
                            $regex = '.+';
                            break;
                    }

                    if (null === $paramInfo[$paramIndex]['paramType']) {
                        $paramInfo[$paramIndex]['param'] = '\{' . $paramInfo[$paramIndex]['paramName'] . '\}';
                    } else {
                        $paramInfo[$paramIndex]['param'] = '\{' . $paramInfo[$paramIndex]['paramType'] . '\:' . $paramInfo[$paramIndex]['paramName'] . '\}';
                    }

                    $paramInfo[$paramIndex]['paramExpression'] = '(' . $regex . ')';
                    
                    $route = preg_replace(
                        '#' . $paramInfo[$paramIndex]['param'] . '#i',
                        $paramInfo[$paramIndex]['paramExpression'],
                        $route
                    );

                }
            }


            $routeExp = '/^'. str_replace('/', '\/', trim($route, '/')) . '$/i';

            if (preg_match($routeExp, $basepath, $matches)) {        
                if ($this->isRequestMethodValid($this->routes[$key]['requestMethod'])) {
                    $routeIndex = $key;
                    $matches = array_slice($matches, 1);
                 
                    $params = array();
                    
                    foreach ($matches as $k => $paramData) {
                        $params[$paramInfo[$k]['paramName']] = $paramData;
                    }
                    
                    $this->parameters = $params;
                    
                    break;
                }
            }
            
            
        }


        if ($routeIndex > -1) {
            if (is_callable($this->routes[$routeIndex]['controler'])) {
                // callback function
                $callbackReturn = call_user_func_array(
                    $this->routes[$routeIndex]['controler'],
                    [
                        $this->parameters
                    ]
                );
                if (is_string($callbackReturn)) {
                    echo $callbackReturn;
                }
            } elseif (is_string($this->routes[$routeIndex]['controler'])) {
                $controler = $this->routes[$routeIndex]['controler'];
                $method = $this->routes[$routeIndex]['method'];
                $controler = new $controler();
                if ($this->getParams() === array()) {
                    $controler->$method();
                } else {
                    $controler->$method($this->getParams());
                }
            }
            
        } else {
            $this->defaultHandle();
        }

    }


    public function getParams() {
        return $this->parameters;
    }


    public function default($callback, $method = null) {
        if (is_callable($callback)) {
            $this->errCallback = $callback;
        } elseif (is_string($callback)) {
            if ($method === null) {
                $this->errCallback = $callback . '::' . $this->defaultMethod;
            } else {
                $this->errCallback = $callback . '::' . $method;
            }
        }
    }


    private function defaultHandle() {

        if (is_callable($this->errCallback)) {
            $callbackReturn = call_user_func_array($this->errCallback, array(
                array(
                    'basepath' => $this->basepath(),
                    'requestMethod' => $this->getRequestMethod()
                )
            ));
            if (is_string($callbackReturn)) {
                echo $callbackReturn;
            }
        } elseif (is_string($this->errCallback)) {
            $callback = explode('::', $this->errCallback);
            $controler = new $callback[0];
            $method = $callback[1];
            $controler->$method();
        }
        
    }



}




