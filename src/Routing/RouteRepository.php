<?php
declare(strict_types = 1);

namespace Mikemirten\Bundle\JsonApiBundle\Routing;

use Symfony\Component\Routing\
{
    Generator\UrlGeneratorInterface,
    Generator\UrlGenerator,
    RouteCollection,
    RequestContext,
    Route
};

/**
 * Repository of routes
 *
 * @package Springfield\Component\HttpClient
 */
class RouteRepository
{
    /**
     * Prepared URL generator
     *
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Route definitions
     *
     * @var array
     */
    private $routes;

    /**
     * Base url
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * RouteRepository constructor.
     *
     * @param string $baseUrl
     * @param array  $routes [name => definition]
     */
    public function __construct(string $baseUrl, array $routes)
    {
        $this->baseUrl = $baseUrl;
        $this->routes  = $routes;
    }

    /**
     * Generate URL by route
     *
     * @param  string $name       Route name
     * @param  array  $parameters Request parameters
     * @return string
     */
    public function generate(string $name, array $parameters = []): string
    {
        return $this->getUrlGenerator()->generate($name, $parameters);
    }

    /**
     * Get URL generator
     *
     * @return UrlGeneratorInterface
     */
    protected function getUrlGenerator(): UrlGeneratorInterface
    {
        if ($this->urlGenerator === null) {
            $this->urlGenerator = new UrlGenerator(
                $this->assembleRoutesCollection(),
                new RequestContext($this->baseUrl)
            );
        }

        return $this->urlGenerator;
    }

    /**
     * Assemble routes collection
     *
     * @return RouteCollection
     */
    protected function assembleRoutesCollection(): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->routes as $name => $definition)
        {
            $route = new Route($definition['path']);
            $route->setMethods($definition['methods']);

            $collection->add($name, $route);
        }

        return $collection;
    }
}