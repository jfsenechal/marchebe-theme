<?php

namespace AcMarche\Theme\Lib\Pivot\Parser;

use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Entity\EventDate;
use AcMarche\Theme\Lib\Pivot\Enums\UrnEnum;
use AcMarche\Theme\Lib\Pivot\Helper\DateHelper;

trait DatesParserTrait
{
    public function parseDates(Event $event): void
    {
        $today = new \DateTime();
        $allDates = [];
        foreach ($event->spec as $spec) {
            $dateEvent = new EventDate();
            foreach ($spec->spec as $specData) {
                $this->parseDatesValidation($event);
                if ($data = $this->getData($specData, UrnEnum::DATE_DEB->value)) {
                    $dateBegin = DateHelper::convertStringToDateTime($data);
                    $dateEvent->dateRealBegin = $dateBegin;
                    if ($dateBegin->format('Y-m-d') < $today->format('Y-m-d')) {
                        $dateEvent->dateBegin = $today;//st loup
                    } else {
                        $dateEvent->dateBegin = $dateBegin;
                    }
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_END->value)) {
                    $dateEvent->dateEnd = DateHelper::convertStringToDateTime($data);
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_OUVERTURE_HEURE_1->value)) {
                    $dateEvent->ouvertureHeure1 = $data;
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_FERMETURE_HEURE_1->value)) {
                    $dateEvent->fermetureHeure1 = $data;
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_OUVERTURE_HEURE_2->value)) {
                    $dateEvent->ouvertureHeure2 = $data;
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_FERMETURE_HEURE_2->value)) {
                    $dateEvent->fermetureHeure2 = $data;
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_DETAIL_OUVERTURE->value)) {
                    $dateEvent->ouvertureDetails =  $data;
                }
                if ($data = $this->getData($specData, UrnEnum::DATE_RANGE->value)) {
                    $dateEvent->dateRange = $data;
                }
            }
            if ($dateEvent->dateEnd && $dateEvent->dateEnd->format('Y-m-d') >= $today->format('Y-m-d')) {
                //dump($dateEvent->dateEnd->format('Y-m-d'));
                $allDates[] = $dateEvent;
            }
        }

        $event->dates = $allDates;
    }

    public function parseDatesValidation(Event $offre): void
    {
        $value = $this->findByUrn($offre, UrnEnum::DATE_DEB_VALID->value, returnValue: true);
        if ($value) {
            $offre->dateDebValid = DateHelper::convertStringToDateTime($value);
        }
        $value = $this->findByUrn($offre, UrnEnum::DATE_FIN_VALID->value, returnValue: true);
        if ($value) {
            $offre->dateFinValid = DateHelper::convertStringToDateTime($value);
        }
    }

    /**
     * Bug server www
     * @param array $data
     * @param string $urn
     * @return string|null
     */
    private function getData(array $data, string $urn): ?string
    {
        if ($data['urn'] === $urn) {
            return $data['value'];
        }

        return null;
    }

}