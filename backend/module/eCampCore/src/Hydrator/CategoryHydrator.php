<?php

namespace eCamp\Core\Hydrator;

use eCamp\Core\Entity\Category;
use eCamp\Lib\Entity\EntityLink;
use eCampApi\V1\Rest\CategoryContent\CategoryContentCollection;
use eCampApi\V1\Rest\CategoryContentType\CategoryContentTypeCollection;
use Laminas\Hydrator\HydratorInterface;

class CategoryHydrator implements HydratorInterface {
    public static function HydrateInfo(): array {
        return [];
    }

    /**
     * @param object $object
     */
    public function extract($object): array {
        /** @var Category $category */
        $category = $object;

        return [
            'id' => $category->getId(),
            'short' => $category->getShort(),
            'name' => $category->getName(),

            'color' => $category->getColor(),
            'numberingStyle' => $category->getNumberingStyle(),

            'camp' => EntityLink::Create($category->getCamp()),
            'categoryContentTypes' => new CategoryContentTypeCollection($category->getCategoryContentTypes()),
            'categoryContents' => new CategoryContentCollection($category->getCategoryContents()),
        ];
    }

    /**
     * @param object $object
     */
    public function hydrate(array $data, $object): Category {
        /** @var Category $category */
        $category = $object;

        if (isset($data['short'])) {
            $category->setShort($data['short']);
        }
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }

        if (isset($data['color'])) {
            $category->setColor($data['color']);
        }
        if (isset($data['numberingStyle'])) {
            $category->setNumberingStyle($data['numberingStyle']);
        }

        return $category;
    }
}
