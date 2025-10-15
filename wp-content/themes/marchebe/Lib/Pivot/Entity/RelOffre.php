<?php

namespace AcMarche\Theme\Lib\Pivot\Entity;

readonly class RelOffre
{
    public function __construct(
        public string $urn,
        public ?string $label,
        public array $offre
    ) {}
}