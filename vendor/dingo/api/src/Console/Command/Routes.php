<?php

namespace Dingo\Api\Console\Command;

use Dingo\Api\Routing\Router;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Foundation\Console\RouteListCommand;

class Routes extends RouteListCommand
{
    /**
     * Array of route collections.
     *
     * @var array
     */
    protected $routes;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'api:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registeted API routes';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Host', 'URI', 'Name', 'Action', 'Protected', 'Version(s)', 'Scope(s)'];

    /**
     * Create a new routes command instance.
     *
     * @param \Dingo\Api\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        // Ugly, but we need to bypass the constructor and directly target the
        // constructor on the command class.
        Command::__construct();

        $this->routes = $router->getRoutes();
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = [];

        foreach ($this->routes as $collection) {
            foreach ($collection->getRoutes() as $route) {
                $routes[] = $this->filterRoute([
                    'host'      => $route->domain(),
                    'uri'       => implode('|', $route->methods()).' '.$route->uri(),
                    'name'      => $route->getName(),
                    'action'    => $route->getActionName(),
                    'protected' => $this->routeHasAuthMiddleware($route) ? 'Yes' : 'No',
                    'versions'  => implode(', ', $route->versions()),
                    'scopes'    => implode(', ', $route->scopes()),
                ]);
            }
        }

        if ($sort = $this->option('sort')) {
            $routes = array_sort($routes, function ($value) use ($sort) {
                return $value[$sort];
            });
        }

        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }

        return array_filter(array_unique($routes, SORT_REGULAR));
    }

    /**
     * Determine if the route has the authentication middleware.
     *
     * @return string
     */
    protected function routeHasAuthMiddleware($route)
    {
        $middleware = $route->getMiddleware();

        return isset($middleware['api.auth']);
    }

    /**
     * Filter the route by URI, Version, Scopes and / or name.
     *
     * @param array $route
     *
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        $filters = ['name', 'path', 'protected', 'unprotected', 'versions', 'scopes'];

        foreach ($filters as $filter) {
            if ($this->option($filter) && ! $this->{'filterBy'.ucfirst($filter)}($route)) {
                return;
            }
        }

        return $route;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        foreach ($options as $key => $option) {
            if ($option[0] == 'sort') {
                unset($options[$key]);
            }
        }

        return array_merge(
            $options,
            [
                ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action) to sort by'],
                ['versions', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Filter the routes by version'],
                ['scopes', 'S', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Filter the routes by scopes'],
                ['protected', null, InputOption::VALUE_NONE, 'Filter the protected routes'],
                ['unprotected', null, InputOption::VALUE_NONE, 'Filter the unprotected routes'],
            ]
        );
    }

    /**
     * Filter the route by its path.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByPath(array $route)
    {
        return str_contains($route['uri'], $this->option('path'));
    }

    /**
     * Filter the route by whether or not it is protected.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByProtected(array $route)
    {
        return $this->option('protected') && $route['protected'] == 'Yes';
    }

    /**
     * Filter the route by whether or not it is unprotected.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByUnprotected(array $route)
    {
        return $this->option('unprotected') && $route['protected'] == 'No';
    }

    /**
     * Filter the route by its versions.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByVersions(array $route)
    {
        foreach ($this->option('versions') as $version) {
            if (str_contains($route['versions'], $version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter the route by its name.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByName(array $route)
    {
        return str_contains($route['name'], $this->option('name'));
    }

    /**
     * Filter the route by its scopes.
     *
     * @param array $route
     *
     * @return bool
     */
    protected function filterByScopes(array $route)
    {
        foreach ($this->option('scopes') as $scope) {
            if (str_contains($route['scopes'], $scope)) {
                return true;
            }
        }

        return false;
    }
}
