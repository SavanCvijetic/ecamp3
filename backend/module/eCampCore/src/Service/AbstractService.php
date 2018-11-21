<?php

namespace eCamp\Core\Service;

use Doctrine\ORM\EntityManager;
use eCamp\Core\Entity\User;
use eCamp\Lib\Acl\Acl;
use eCamp\Lib\Entity\BaseEntity;
use Zend\Authentication\AuthenticationService;

abstract class AbstractService {
    /** @var EntityManager */
    private $entityManager;

    /** @var Acl */
    private $acl;

    /** @var AuthenticationService */
    private $authenticationService;

    public function __construct(AuthenticationService $authenticationService) {
        $this->authenticationService = $authenticationService;
    }

    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    protected function getEntityManager() {
        return $this->entityManager;
    }

    /**
     * @param BaseEntity $entity
     * @return array
     */
    protected function getOrigEntityData($entity) {
        $uow = $this->entityManager->getUnitOfWork();
        return $uow->getOriginalEntityData($entity);
    }


    public function setAcl(Acl $acl) {
        $this->acl = $acl;
    }

    protected function getAcl() {
        return $this->acl;
    }

    /**
     * @return null|User
     */
    protected function getAuthUser() {
        /** @var User $user */
        $user = null;

        if ($this->authenticationService->hasIdentity()) {
            $userRepository = $this->getEntityManager()->getRepository(User::class);
            $userId = $this->authenticationService->getIdentity();
            $user = $userRepository->find($userId);
        }

        return $user;
    }

    /**
     * @param null $resource
     * @param null $privilege
     * @return bool
     */
    protected function isAllowed($resource, $privilege = null) {
        $user = $this->getAuthUser();
        return $this->getAcl()->isAllowed($user, $resource, $privilege);
    }

    /**
     * @param null $resource
     * @param null $privilege
     * @throws \eCamp\Lib\Acl\NoAccessException
     */
    protected function assertAllowed($resource, $privilege = null) {
        $user = $this->getAuthUser();
        return $this->getAcl()->assertAllowed($user, $resource, $privilege);
    }
}
