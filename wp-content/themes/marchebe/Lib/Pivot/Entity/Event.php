<?php

namespace AcMarche\Theme\Lib\Pivot\Entity;

class Event
{
    public ?\DateTimeInterface $dateDebValid = null;
    public ?\DateTimeInterface $dateFinValid = null;
    /**
     * @var array<EventDate>
     */
    public array $dates = [];
    /**
     * @var array<string>
     */
    public array $images = [];
    /**
     * @var array<string>
     */
    public array $documents = [];

    public function __construct(
        public string $codeCgt,
        public string $dateCreation,
        public string $dateModification,
        public User $userCreation,
        public UserGlobal $userGlobalCreation,
        public User $userModification,
        public UserGlobal $userGlobalModification,
        public string $nom,
        public int $estActive,
        public array $estActiveUrn,
        public int $visibilite,
        public array $visibiliteUrn,
        public TypeOffre $typeOffre,
        public Adresse $adresse1,
        /**
         * @var array<Spec>
         */
        public array $spec,
        /**
         * @var array<RelOffre>
         */
        public array $relOffre,
        public array $relOffreTgt,
    ) {
    }
}