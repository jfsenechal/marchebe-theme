<?php

namespace AcMarche\Theme\Lib\Pivot\Parser;


use AcMarche\Theme\Lib\Pivot\Entity\Adresse;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Entity\RelOffre;
use AcMarche\Theme\Lib\Pivot\Entity\Spec;
use AcMarche\Theme\Lib\Pivot\Entity\TypeOffre;
use AcMarche\Theme\Lib\Pivot\Entity\User;
use AcMarche\Theme\Lib\Pivot\Entity\UserGlobal;
use AcMarche\Theme\Lib\Pivot\Enums\TypeEnum;

class EventParser
{
    use DatesParserTrait, ImagesParserTrait;

    /**
     * @param string $jsonContent
     * @param int $maxIems
     * @return array<Event>
     */
    public function parseJsonFile(string $jsonContent, int $maxIems = 5): array
    {
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: '.json_last_error_msg());
        }

        $events = [];
        $i = 0;
        foreach ($data['offre'] as $item) {
            if (isset($item['typeOffre']['idTypeOffre']) && $item['typeOffre']['idTypeOffre'] === TypeEnum::Event->value) {
                $events[] = $this->parseEvent($item);
                $i++;
                if ($i > $maxIems) {
                    break;
                }
            }
        }

        return $events;
    }

    private function parseEvent(array $data): Event
    {
        $event = new Event(
            codeCgt: $data['codeCgt'],
            dateCreation: $data['dateCreation'],
            dateModification: $data['dateModification'],
            nom: $data['nom'],
            estActive: $data['estActive'],
            estActiveUrn: $data['estActiveUrn'],
            visibilite: $data['visibilite'],
            visibiliteUrn: $data['visibiliteUrn'],
            typeOffre: $this->parseTypeOffre($data['typeOffre']),
            adresse1: $this->parseAdresse($data['adresse1']),
            spec: array_map(fn($s) => $this->parseSpec($s), $data['spec'] ?? []),
            relOffre: array_map(fn($r) => $this->parseRelOffre($r), $data['relOffre'] ?? []),
            relOffreTgt: $data['relOffreTgt'] ?? [],
        );

        if ($event->typeOffre->idTypeOffre === TypeEnum::Event->value) {
            $this->parseDates($event);
        }

        $this->parseImages($event);

        return $event;
    }

    //removed for security
    private function parseUser(array $data): User
    {
        return new User(
            codeCgt: $data['codeCgt'],
            login: $data['login'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }

    //removed for security
    private function parseUserGlobal(array $data): UserGlobal
    {
        return new UserGlobal(
            idUserglobal: $data['idUserglobal'],
            nom: $data['nom']
        );
    }

    private function parseTypeOffre(array $data): TypeOffre
    {
        return new TypeOffre(
            idTypeOffre: $data['idTypeOffre'],
            label: $data['label'] ?? null
        );
    }

    private function parseAdresse(array $data): Adresse
    {
        return new Adresse(
            rue: $data['rue'] ?? null,
            numero: $data['numero'] ?? null,
            idIns: $data['idIns'] ?? null,
            ins: $data['ins'] ?? null,
            cp: $data['cp'] ?? null,
            localite: $data['localite'] ?? null,
            commune: $data['commune'] ?? null,
            lieuDit: $data['lieuDit'] ?? null,
            province: $data['province'] ?? null,
            provinceUrn: $data['provinceUrn'] ?? null,
            pays: $data['pays'] ?? null,
            paysUrn: $data['paysUrn'] ?? null,
            lambertX: $data['lambertX'] ?? null,
            lambertY: $data['lambertY'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            altitude: $data['altitude'] ?? null,
            noaddress: $data['noaddress'] ?? null,
            parcNaturel: $data['parcNaturel'] ?? null,
            organisme: $data['organisme'] ?? null
        );
    }

    private function parseSpec(array $data): Spec
    {
        return new Spec(
            urn: $data['urn'],
            urnCat: $data['urnCat'] ?? null,
            urnCatLabel: $data['urnCatLabel'] ?? null,
            urnSubCat: $data['urnSubCat'] ?? null,
            urnSubCatLabel: $data['urnSubCatLabel'] ?? null,
            order: $data['order'] ?? null,
            type: $data['type'] ?? null,
            label: $data['label'] ?? null,
            value: $data['value'] ?? null,
            valueLabel: $data['valueLabel'] ?? null,
            spec: $data['spec'] ?? []
        );
    }

    private function parseRelOffre(array $data): RelOffre
    {
        $relOffre = new RelOffre(
            urn: $data['urn'],
            label: $data['label'],
        );
        if ($data['offre']) {
            $relOffre->offre = $this->parseEvent($data['offre']);
        }

        return $relOffre;
    }

    public function findByUrn(Event $event, string $urnName, bool $returnValue = false): mixed
    {
        foreach ($event->spec as $specification) {
            if ($specification->urn === $urnName) {
                return $specification->value;
            }
        }

        return null;
    }
}