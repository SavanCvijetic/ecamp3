<?php

namespace eCamp\Core\EntityService;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use eCamp\Core\Entity\AbstractCampOwner;
use eCamp\Core\Entity\Camp;
use eCamp\Core\Entity\CampCollaboration;
use eCamp\Core\Entity\CampTemplate;
use eCamp\Core\Entity\CategoryTemplate;
use eCamp\Core\Entity\MaterialListTemplate;
use eCamp\Core\Entity\User;
use eCamp\Core\Hydrator\CampHydrator;
use eCamp\Lib\Acl\NoAccessException;
use eCamp\Lib\Entity\BaseEntity;
use eCamp\Lib\Service\EntityNotFoundException;
use eCamp\Lib\Service\ServiceUtils;
use Laminas\Authentication\AuthenticationService;

class CampService extends AbstractEntityService {
    protected PeriodService $periodService;
    protected MaterialListService $materialListService;
    protected CategoryService $categoryService;
    protected CampCollaborationService $campCollaboratorService;

    public function __construct(
        ServiceUtils $serviceUtils,
        AuthenticationService $authenticationService,
        PeriodService $periodService,
        MaterialListService $materialListService,
        CategoryService $categoryService,
        CampCollaborationService $campCollaboratorService
    ) {
        parent::__construct(
            $serviceUtils,
            Camp::class,
            CampHydrator::class,
            $authenticationService
        );

        $this->periodService = $periodService;
        $this->materialListService = $materialListService;
        $this->categoryService = $categoryService;
        $this->campCollaboratorService = $campCollaboratorService;
    }

    public function fetchByOwner(AbstractCampOwner $owner): array {
        $q = parent::findCollectionQueryBuilder(Camp::class, 'row', null);
        $q->where('row.owner = :owner');
        $q->setParameter('owner', $owner);

        return $this->getQueryResult($q);
    }

    /**
     * @throws NoAccessException
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    protected function createEntity($data): Camp {
        $this->assertAuthenticated();

        /** @var AbstractCampOwner $owner */
        $owner = $this->getAuthUser();
        if (isset($data->ownerId)) {
            /** @var AbstractCampOwner $owner */
            $owner = $this->findEntity(AbstractCampOwner::class, $data->ownerId);
        }

        /** @var User $creator */
        $creator = $this->getAuthUser();

        /** @var Camp $camp */
        $camp = parent::createEntity($data);
        $camp->setName($data->name);
        $camp->setCreator($creator);
        $owner->addOwnedCamp($camp);

        return $camp;
    }

    protected function createEntityPost(BaseEntity $entity, $data): Camp {
        /** @var Camp $camp */
        $camp = $entity;

        // Create CampCollaboration for Creator
        $this->campCollaboratorService->create((object) [
            'campId' => $camp->getId(),
            'role' => CampCollaboration::ROLE_MANAGER,
        ]);

        if (isset($data->campTemplateId)) {
            // CampTemplateId given
            // - Create MaterialLists
            // - Create Categories
            $camp->setCampTemplateId($data->campTemplateId);

            /** @var CampTemplate $campTemplate */
            $campTemplate = $this->findEntity(CampTemplate::class, $data->campTemplateId);

            /** @var MaterialListTemplate $materialListTemplate */
            foreach ($campTemplate->getMaterialListTemplates() as $materialListTemplate) {
                $this->materialListService->createFromTemplate($camp, $materialListTemplate);
            }

            /** @var CategoryTemplate $categoryTemplate */
            foreach ($campTemplate->getCategoryTemplates() as $categoryTemplate) {
                $this->categoryService->createFromTemplate($camp, $categoryTemplate);
            }
        }

        // Create Periods:
        if (isset($data->periods)) {
            foreach ($data->periods as $period) {
                $period = (object) $period;
                $period->campId = $camp->getId();
                $this->periodService->create($period);
            }
        }

        return $camp;
    }

    protected function fetchAllQueryBuilder($params = []): QueryBuilder {
        $q = parent::fetchAllQueryBuilder($params);

        if (isset($params['group'])) {
            $q->andWhere('row.owner = :group');
            $q->setParameter('group', $params['group']);
        }

        return $q;
    }
}
