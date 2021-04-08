<?php
namespace InnovA2\UrlBuilder;

use Doctrine\Common\Collections\ArrayCollection;

class UrlBuilder
{
    private string $scheme = Scheme::HTTPS;
    private ?string $host = null;
    private ?int $port = null;
    private ArrayCollection $paths;
    private ArrayCollection $params;
    private ArrayCollection $query;

    public function __construct()
    {
        $this->paths = new ArrayCollection();
        $this->params = new ArrayCollection();
        $this->query = new ArrayCollection();
    }

    static function createFromUrl(string $baseUrl): UrlBuilder
    {
        $url = new UrlBuilder();

        $components = parse_url($baseUrl);

        if (array_key_exists('scheme', $components)) {
            $url->scheme = $components['scheme'];
        }

        $url->host = $components['host'] ?? '';

        if (array_key_exists('port', $components)) {
            $url->port = $components['port'];
        }

        $url->paths = new ArrayCollection(self::splitPath($components['path']));

        if (array_key_exists('query', $components)) {
            foreach (explode('&', $components['query']) as $q) {
                $keyValue = explode('=', $q);

                $url->query->set($keyValue[0], $keyValue[1]);
            }
        }

        return $url;
    }

    static function splitPath(string $path): array
    {
        return array_filter(explode('/', $path), function ($p) {
            return $p != null;
        });
    }

    static function trimPath(string $path): string
    {
        return implode('/', self::splitPath($path));
    }

    function compareTo(UrlBuilder $url, bool $relative = true): bool
    {
        return ($relative && $url->getRelativePath() === $this->getRelativePath()) || (!$relative && $url->toString() === $this->toString());
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPaths(): array
    {
        return $this->paths->toArray();
    }

    function setPort(int $port): UrlBuilder
    {
        $this->port = $port;
        return $this;
    }

    function addPath(string $path): UrlBuilder {
        foreach (self::splitPath($path) as $item) {
            $this->paths->add($item);
        }
        return $this;
    }

    function addParam(string $key, $value): UrlBuilder {
        $this->params->set($key, $value);
        return $this;
    }

    function addParams(array $params): UrlBuilder {
        foreach ($params as $key => $value) {
            $this->params->set($key, $value);
        }
        return $this;
    }

    function getParams(): array {
        return $this->params->toArray();
    }

    function addQuery(string $key, $value): UrlBuilder {
        $this->query->set($key, $value);
        return $this;
    }

    function addQueries(array $queries): UrlBuilder {
        foreach ($queries as $key => $value) {
            $this->query->set($key, $value);
        }
        return $this;
    }

    function getQuery(): array {
        return $this->query->toArray();
    }

    function getFirstPath(): string {
        return $this->paths->first();
    }

    function getLastPath(): string {
        return $this->paths->last();
    }

    function getParent(int $n = 1): UrlBuilder {
        $parent = clone $this;
        $lastPath = $parent->getLastPath();

        $parent->paths = $parent->paths->filter(function ($p) use ($lastPath) {
            return $p !== $lastPath;
        });

        $lastPath = str_replace(':', '', $lastPath);
        $parent->params = $parent->params->filter(function ($p) use ($lastPath) {
            return $p !== $lastPath;
        });

        $parent->query = new ArrayCollection();

        return $n > 1 ? $parent->getParent($n - 1) : $parent;
    }

    function getBetween2Words(string $a, string $b): ?string {
        $indexA = $this->paths->indexOf($a);
        $indexB = $this->paths->indexOf($b);

        if (!$indexA || !$indexB) {
            return null;
        }

        $paths = array_slice($this->paths->toArray(), $indexA, 1);
        return count($paths) ? $paths[0] : null;
    }

    function getRelativePath(bool $query = false): string {
        $paths = [];

        foreach ($this->paths as $path) {
            $path = str_replace(':', '', $path);
            $param = $this->params->get($path);
            if ($param) {
                $path = $param;
            }

            $paths[] = $path;
        }

        $relativePath = count($paths) ? ('/' . implode('/', $paths)) : '';
        $queryString = $this->getQueryString();
        return $query && $queryString ? ($relativePath . $queryString) : $relativePath;
    }

    function getQueryString(): ?string {
        $queryParams = [];

        foreach ($this->query as $key => $value) {
            $queryParams[] = implode('=', [$key, $value]);
        }

        return count($queryParams) ? ('?' . implode('&', $queryParams)) : null;
    }

    function toString(): string {
        $baseUrl = $this->host ? implode('://', [$this->scheme, $this->host]) : '';

        if ($this->port) {
            $baseUrl = implode(':', [$baseUrl, $this->port]);
        }

        return implode('', [$baseUrl, $this->getRelativePath(), $this->getQueryString()]);
    }
}
