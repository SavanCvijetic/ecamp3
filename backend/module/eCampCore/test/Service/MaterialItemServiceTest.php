<?php

namespace eCamp\CoreTest\Service;

use Doctrine\Common\DataFixtures\Loader;
use eCamp\Core\Entity\Camp;
use eCamp\Core\Entity\MaterialItem;
use eCamp\Core\Entity\MaterialList;
use eCamp\Core\Entity\Period;
use eCamp\Core\Entity\User;
use eCamp\Core\EntityService\MaterialItemService;
use eCamp\CoreTest\Data\CampTestData;
use eCamp\CoreTest\Data\MaterialListTestData;
use eCamp\CoreTest\Data\PeriodTestData;
use eCamp\CoreTest\Data\UserTestData;
use eCamp\Lib\Service\EntityValidationException;
use eCamp\LibTest\PHPUnit\AbstractApiControllerTestCase;

/**
 * @internal
 */
class MaterialItemServiceTest extends AbstractApiControllerTestCase {
    /** @var User */
    protected $user;
    /** @var Camp */
    protected $camp;
    /** @var Period */
    protected $period;
    /** @var MaterialList */
    protected $materialList;

    public function setUp(): void {
        parent::setUp();

        $userLoader = new UserTestData();
        $campLoader = new CampTestData();
        $periodLoader = new PeriodTestData();
        $materialListLoader = new MaterialListTestData();

        $loader = new Loader();
        $loader->addFixture($userLoader);
        $loader->addFixture($campLoader);
        $loader->addFixture($periodLoader);
        $loader->addFixture($materialListLoader);
        $this->loadFixtures($loader);

        $this->user = $userLoader->getReference(UserTestData::$USER1);
        $this->camp = $campLoader->getReference(CampTestData::$CAMP1);
        $this->period = $periodLoader->getReference(PeriodTestData::$PERIOD1);
        $this->materialList = $materialListLoader->getReference(MaterialListTestData::$MATERIALLIST1);

        $this->authenticateUser($this->user);
    }

    public function testCreateMaterialItem(): void {
        /** @var MaterialItemService $materialItemService */
        $materialItemService = $this->getApplicationServiceLocator()->get(MaterialItemService::class);

        // Create new MaterialItem
        /** @var MaterialItem $materialItem */
        $materialItem = $materialItemService->create((object) [
            'periodId' => $this->period->getId(),
            'materialListId' => $this->materialList->getId(),
            'article' => 'art',
            'quantity' => 3,
            'unit' => 'kg',
        ]);

        // MaterialItem is returned with correct data
        $this->assertNotNull($materialItem);
        $this->assertEquals('art', $materialItem->getArticle());
        $this->assertEquals(3, $materialItem->getQuantity());
        $this->assertEquals('kg', $materialItem->getUnit());

        $materialItemId = $materialItem->getId();

        // Load MaterialItem from DB
        /** @var MaterialItem $materialItem */
        $materialItem = $materialItemService->fetch($materialItemId);

        // MaterialItem is returned with correct data
        $this->assertNotNull($materialItem);
        $this->assertEquals('art', $materialItem->getArticle());
        $this->assertEquals(3, $materialItem->getQuantity());
        $this->assertEquals('kg', $materialItem->getUnit());
    }

    public function testAddInvalidItem(): void {
        /** @var MaterialItemService $materialItemService */
        $materialItemService = $this->getApplicationServiceLocator()->get(MaterialItemService::class);

        // Period or ContentNode is required
        $this->expectException(EntityValidationException::class);

        $materialItemService->create((object) [
            'materialListId' => $this->materialList->getId(),
            'quantity' => 5,
            'unit' => 'Stk',
            'article' => 'Article',
        ]);
    }
}
