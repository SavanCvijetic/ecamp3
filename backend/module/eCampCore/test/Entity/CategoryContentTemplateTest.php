<?php

namespace eCamp\CoreTest\Entity;

use eCamp\Core\Entity\CampTemplate;
use eCamp\Core\Entity\CategoryContentTemplate;
use eCamp\Core\Entity\CategoryTemplate;
use eCamp\Core\Entity\ContentType;
use eCamp\LibTest\PHPUnit\AbstractTestCase;

/**
 * @internal
 */
class CategoryContentTemplateTest extends AbstractTestCase {
    public function testCategoryContentTemplate(): void {
        $campTemplate = new CampTemplate();

        $contentType = new ContentType();

        $categoryTemplate = new CategoryTemplate();
        $categoryTemplate->setCampTemplate($campTemplate);
        $categoryTemplate->setName('CategoryName');

        $categoryContentTemplate = new CategoryContentTemplate();
        $categoryContentTemplate->setCategoryTemplate($categoryTemplate);
        $categoryContentTemplate->setContentType($contentType);
        $categoryContentTemplate->setInstanceName('CategoryContentName');
        $categoryContentTemplate->setPosition('position');

        $this->assertEquals($categoryTemplate, $categoryContentTemplate->getCategoryTemplate());
        $this->assertEquals($contentType, $categoryContentTemplate->getContentType());
        $this->assertEquals('CategoryContentName', $categoryContentTemplate->getInstanceName());
        $this->assertEquals('position', $categoryContentTemplate->getPosition());
    }

    public function testCategoryContentTemplateHierarchy(): void {
        $categoryContentTemplate = new CategoryContentTemplate();
        $childCategoryContentTemplate = new CategoryContentTemplate();

        // Add Child-CategoryContentTemplate
        $categoryContentTemplate->addChild($childCategoryContentTemplate);
        $this->assertCount(1, $categoryContentTemplate->getChildren());
        $this->assertEquals($categoryContentTemplate, $childCategoryContentTemplate->getParent());
        $this->assertTrue($categoryContentTemplate->isRoot());
        $this->assertFalse($childCategoryContentTemplate->isRoot());

        // Remove Child-CategoryContentTemplate
        $categoryContentTemplate->removeChild($childCategoryContentTemplate);
        $this->assertCount(0, $categoryContentTemplate->getChildren());
        $this->assertNull($childCategoryContentTemplate->getParent());
        $this->assertTrue($categoryContentTemplate->isRoot());
        $this->assertTrue($childCategoryContentTemplate->isRoot());
    }
}
