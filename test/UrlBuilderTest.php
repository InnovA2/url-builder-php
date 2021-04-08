<?php
namespace Test;

use InnovA2\UrlBuilder\UrlBuilder;
use PHPUnit\Framework\TestCase;

final class UrlBuilderTest extends TestCase
{
    const URL_USERS_PAGINATED = 'https://localhost/users?page=1';
    const URL_USERS_PAGINATED_WITH_PORT = 'https://localhost:3000/users?page=1';

    const PATH_USERS_WITH_QP = '/users?page=1&order=ASC';
    const PATH_USER_COMMENTS = '/users/10/comments';
    const PATH_USER_COMMENT = '/users/10/comments/1';

    private UrlBuilder $url;

    /**
     * @before
     */
    public function setupInstance(): void
    {
        $this->url = new UrlBuilder();
    }

    /**
     * Test the creation of url builder from existing url string
     */
    public function testCreateFromString(): void
    {
        $this->url = UrlBuilder::createFromUrl(self::URL_USERS_PAGINATED_WITH_PORT)->addQuery('order', 'DESC');

        self::assertEquals(self::URL_USERS_PAGINATED_WITH_PORT . '&order=DESC', $this->url->toString());
        self::assertEquals('https', $this->url->getScheme());
        self::assertEquals('localhost', $this->url->getHost());
    }

    /**
     * Test trim path string
     */
    public function testTrimPath(): void
    {
        self::assertEquals(substr(self::PATH_USER_COMMENTS, 1), UrlBuilder::trimPath(self::PATH_USER_COMMENTS));
    }

    /**
     * Test if comparision is true
     */
    public function testCompareTo(): void
    {
        $url = UrlBuilder::createFromUrl(self::PATH_USER_COMMENTS);
        $url2 = UrlBuilder::createFromUrl(self::PATH_USER_COMMENTS);
        $url3 = UrlBuilder::createFromUrl(self::URL_USERS_PAGINATED_WITH_PORT);

        self::assertTrue($url->compareTo($url2));
        self::assertTrue($url->compareTo($url2, false));

        self::assertFalse($url->compareTo($url3));
        self::assertFalse($url->compareTo($url3, false));
    }

    /**
     * Test to set port
     */
    public function testSetPort(): void
    {
        $this->url = UrlBuilder::createFromUrl(self::URL_USERS_PAGINATED)->setPort(3000);

        self::assertEquals(self::URL_USERS_PAGINATED_WITH_PORT, $this->url->toString());
        self::assertEquals(3000, $this->url->getPort());
    }

    /**
     * Test to add params
     */
    public function testAddParams(): void
    {
        $this->url
            ->addPath('users/:userId/comments/:commentId')
            ->addParams([
                'userId' => 10,
                'commentId' => 1
            ]);

        self::assertEquals(self::PATH_USER_COMMENT, $this->url->getRelativePath());
    }

    /**
     * Test to get the same params
     */
    public function testGetSameParams(): void
    {
        $this->url
            ->addPath('users/:userId/comments/:commentId')
            ->addParams([
                'userId' => 10,
                'commentId' => 1
            ]);

        self::assertEquals(10, $this->url->getParams()['userId']);
    }

    /**
     * Test to add queries
     */
    public function testAddQueries(): void
    {
        $this->url
            ->addPath('users')
            ->addQueries([
                'page' => 1,
                'order' => 'ASC'
            ]);

        self::assertEquals(self::PATH_USERS_WITH_QP, $this->url->getRelativePath(true));
    }

    /**
     * Test to get the same queries
     */
    public function testGetSameQueries(): void
    {
        $this->url
            ->addPath('users')
            ->addQueries([
                'page' => 1,
                'order' => 'ASC'
            ]);

        self::assertEquals('ASC', $this->url->getQuery()['order']);
    }

    /**
     * Test to get the first path
     */
    public function testGetFirstPath(): void
    {
        $this->url = UrlBuilder::createFromUrl(self::PATH_USER_COMMENTS);

        self::assertEquals('users', $this->url->getFirstPath());
    }

    /**
     * Test to get the last path
     */
    public function testGetLastPath(): void
    {
        $this->url = UrlBuilder::createFromUrl(self::PATH_USER_COMMENTS);

        self::assertEquals('comments', $this->url->getLastPath());
    }

    /**
     * Test to get the parent url builder
     */
    public function testGetParent(): void
    {
        $this->url = UrlBuilder::createFromUrl('/users/:userId/comments/:commentId')
            ->addParams([
                'userId' => 10,
                'commentId' => 1
            ]);

        self::assertEquals(self::PATH_USER_COMMENTS, $this->url->getParent()->getRelativePath());
        self::assertEquals('/users/10', $this->url->getParent(2)->getRelativePath());
        self::assertEquals('/users', $this->url->getParent(3)->getRelativePath());
    }

    /**
     * Test to get segment path between two words
     */
    public function testGetBetween2Words(): void
    {
        $this->url = UrlBuilder::createFromUrl(self::PATH_USER_COMMENTS);

        self::assertEquals('10', $this->url->getBetween2Words('users', 'comments'));
        self::assertNull($this->url->getBetween2Words('user', 'comment'));
    }

    /**
     * Test to get empty relative path
     */
    public function testGetEmptyRelativePath(): void
    {
        self::assertEmpty($this->url->getRelativePath());
    }

    /**
     * Test to get only query in relative path
     */
    public function testGetQueryInRelativePath(): void
    {
        self::assertEquals('?page=2', $this->url->addQuery('page', 2)->getRelativePath(true));
    }

    /**
     * Test to get the same relative path
     */
    public function testGetSameRelativePath(): void
    {
        $this->url->addPath('users/:id/comments')->addParam('id', 10);

        self::assertEquals(['users', ':id', 'comments'], $this->url->getPaths());
        self::assertEquals(self::PATH_USER_COMMENTS, $this->url->getRelativePath());
    }

}
