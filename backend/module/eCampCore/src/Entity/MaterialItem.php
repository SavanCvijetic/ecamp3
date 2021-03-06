<?php

namespace eCamp\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use eCamp\Lib\Entity\BaseEntity;

/**
 * @ORM\Entity
 */
class MaterialItem extends BaseEntity implements BelongsToCampInterface {
    /**
     * @ORM\ManyToOne(targetEntity="MaterialList")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    protected ?MaterialList $materialList = null;

    /**
     * @ORM\ManyToOne(targetEntity="Period")
     * @ORM\JoinColumn(nullable=true, onDelete="cascade")
     */
    protected ?Period $period = null;

    /**
     * @ORM\ManyToOne(targetEntity="eCamp\Core\Entity\ContentNode")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected ?ContentNode $contentNode = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private ?string $article = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $quantity = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private ?string $unit = null;

    public function getMaterialList(): ?MaterialList {
        return $this->materialList;
    }

    public function setMaterialList(?MaterialList $materialList): void {
        $this->materialList = $materialList;
    }

    public function getCamp(): ?Camp {
        return $this->materialList->getCamp();
    }

    public function getPeriod(): ?Period {
        return $this->period;
    }

    public function setPeriod(?Period $period): void {
        $this->contentNode = null;
        $this->period = $period;
    }

    public function getContentNode(): ?ContentNode {
        return $this->contentNode;
    }

    public function setContentNode(?ContentNode $contentNode): void {
        $this->period = null;
        $this->contentNode = $contentNode;
    }

    public function getArticle(): ?string {
        return $this->article;
    }

    public function setArticle(?string $article): void {
        $this->article = $article;
    }

    public function getQuantity(): ?float {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): void {
        $this->quantity = $quantity;
    }

    public function getUnit(): ?string {
        return $this->unit;
    }

    public function setUnit(?string $unit): void {
        $this->unit = $unit;
    }
}
