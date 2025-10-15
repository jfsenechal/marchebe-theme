<?php

namespace AcMarche\Theme\Lib\Pivot\Parser;


use AcMarche\Theme\Lib\Pivot\Entity\Adresse;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Entity\RelOffre;
use AcMarche\Theme\Lib\Pivot\Entity\Spec;
use AcMarche\Theme\Lib\Pivot\Entity\TypeOffre;
use AcMarche\Theme\Lib\Pivot\Entity\User;
use AcMarche\Theme\Lib\Pivot\Entity\UserGlobal;

class EventParser
{
    use DatesParserTrait;

    /**
     * @param string $jsonContent
     * @return array<Event>
     */
    public function parseJsonFile(string $jsonContent): array
    {
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: '.json_last_error_msg());
        }

        $events = [];

        foreach ($data['offre'] as $item) {
            // Filter only events with idTypeOffre = 9
            if (isset($item['typeOffre']['idTypeOffre']) &&
                $item['typeOffre']['idTypeOffre'] === 9) {
                $events[] = $this->parseEvent($item);
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
            userCreation: $this->parseUser($data['userCreation']),
            userGlobalCreation: $this->parseUserGlobal($data['userGlobalCreation']),
            userModification: $this->parseUser($data['userModification']),
            userGlobalModification: $this->parseUserGlobal($data['userGlobalModification']),
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
        $this->parseDates($event);

        return $event;
    }

    private function parseUser(array $data): User
    {
        return new User(
            codeCgt: $data['codeCgt'],
            login: $data['login'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }

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
            spec: $data['spec'] ?? null
        );
    }

    private function parseRelOffre(array $data): RelOffre
    {
        return new RelOffre(
            urn: $data['urn'],
            label: $data['label'],
            offre: $data['offre']
        );
    }
}