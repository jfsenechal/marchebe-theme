<?php

namespace AcMarche\Theme\Lib\Pivot\Enums;

enum ContentEnum: int
{
    case LVL0 = 0;
    case LVL1 = 1;
    case LVL2 = 2;
    case LVL3 = 3;

    public function description(): string
    {
        return match ($this) {
            self::LVL0 => '(valeur par défaut) génère des offres ne contenant que le codeCgt et les dates de
création et de dernière modification.',
            self::LVL1 => 'génère des « résumés » d’offres, ne contenant que le codeCgt, le nom, l’adresse et la
géolocalisation, ainsi que le classement, le label Qualité Wallonie et média par défaut
associé à l’offre.',
            self::LVL2 => 'produit des offres au contenu complet. Les offres filles des relations ne sont
représentées que par leur codeCgt.',
            self::LVL3 => 'produit les offres au contenu complet, avec également un contenu complet pour les
offres liées.',
        };

    }
}
