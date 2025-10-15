<?php

namespace AcMarche\Theme\Lib\Pivot\Entity;

readonly class TypeOffre
{
    public function __construct(
        public int $idTypeOffre,
        /**
         * @var array<Label>|null
         */
        public ?array $label = null
    ) {
    }

    public function isEvent(): bool
    {
        return $this->idTypeOffre === 9;
    }
}