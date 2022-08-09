<?php

namespace App\Model\DataObject;

use Pimcore\Bundle\EcommerceFrameworkBundle\Model\IndexableInterface;
use Pimcore\Model\DataObject\DemoProduct;

class MyProduct extends DemoProduct implements IndexableInterface
{
    public function getOSDoIndexProduct(): bool
    {
        return $this->isPublished();
    }

    public function getPriceSystemName(): ?string
    {
        return 'default';
    }

    public function isActive(bool $inProductList = false): bool
    {
        return $this->isPublished();
    }

    public function getOSIndexType(): ?string
    {
        return $this->getType();
    }

    public function getOSParentId()
    {
        return $this->getParentId();
    }
    public function getCategories(): array
    {
        return $this->getCategory();
    }
}
