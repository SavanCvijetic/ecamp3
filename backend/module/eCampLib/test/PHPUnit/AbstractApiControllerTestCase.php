<?php

namespace eCamp\LibTest\PHPUnit;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use eCamp\Core\Entity\User;
use Laminas\Authentication\AuthenticationService;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as LaminasAbstractHttpControllerTestCase;

abstract class AbstractApiControllerTestCase extends LaminasAbstractHttpControllerTestCase {
    /**
     * Host name in URIs returned by API.
     *
     * @var string
     */
    protected $host = '';

    /**
     * @throws ToolsException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setUp(): void {
        parent::setUp();

        $data = include __DIR__.'/../../../../config/application.config.php';
        $this->setApplicationConfig($data);

        $em = $this->getEntityManager();
        $this->createDatabaseSchema($em);

        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('Accept', 'application/json');
        $headers->addHeaderLine('Content-Type', 'application/json');
    }

    protected function getEntityManager(?string $name = null): EntityManager {
        $name = $name ?: 'orm_default';
        $name = 'doctrine.entitymanager.'.$name;

        return $this->getApplicationServiceLocator()->get($name);
    }

    /** @throws ToolsException */
    protected function createDatabaseSchema(EntityManager $em): void {
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadatas);
    }

    /**
     * Set request content encoded as JSON.
     */
    protected function setRequestContent($content): void {
        $this->getRequest()->setContent(json_encode($content));
    }

    /**
     * Get response content decoded from JSON.
     */
    protected function getResponseContent() {
        return json_decode($this->getResponse()->getContent());
    }

    /**
     * Creates a new user and authenticates it as the current user.
     */
    protected function createAndAuthenticateUser(): User {
        $user = new User();
        $user->setRole(User::ROLE_USER);
        $user->setState(User::STATE_ACTIVATED);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->authenticateUser($user);

        return $user;
    }

    /**
     * Authenticates a given $user.
     */
    protected function authenticateUser(User $user): void {
        /** @var AuthenticationService $auth */
        $auth = $this->getApplicationServiceLocator()->get(AuthenticationService::class);
        $auth->getStorage()->write($user->getId());
    }

    /**
     * Returns id of authenticated user.
     */
    protected function getAuthenticatedUserId(): ?string {
        /** @var AuthenticationService $auth */
        $auth = $this->getApplicationServiceLocator()->get(AuthenticationService::class);

        return $auth->getStorage()->read();
    }

    /**
     * Verifies HAL response.
     */
    protected function verifyHalResourceResponse(string $rootAsJson, string $linksAsJson, array $embeddedObjectList): void {
        $response = $this->getResponseContent();

        // verify correctness of links
        $this->assertEquals(json_decode($linksAsJson), $response->_links);

        // verify existence of embedded objects
        foreach ($embeddedObjectList as $embeddedObject) {
            $this->assertObjectHasAttribute($embeddedObject, $response->_embedded);
        }

        // verify root content
        unset($response->_links, $response->_embedded);
        $this->assertEquals(json_decode($rootAsJson), $response);
    }

    /**
     * loads data from Fixtures into ORM.
     */
    protected function loadFixtures(Loader $loader): void {
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->getEntityManager(), $purger);
        $executor->execute($loader->getFixtures());
    }
}
