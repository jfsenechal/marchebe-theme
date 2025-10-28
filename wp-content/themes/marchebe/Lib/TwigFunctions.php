<?php

namespace AcMarche\Theme\Lib;

use Twig\TwigFunction;

class TwigFunctions
{
    public static function fichePhones(): TwigFunction
    {
        return new TwigFunction(
            'fichePhones',
            function (\stdClass $fiche): array {
                /**
                 *   const phoneFields = [
                 * fiche.telephone,
                 * fiche.telephone_autre,
                 * fiche.gsm,
                 * fiche.contact_gsm,
                 * fiche.contact_telephone,
                 * fiche.contact_telephone_autre
                 * ]
                 * return phoneFields.filter(Boolean)
                 */
                $phoneFields = [
                    $fiche->telephone ?? null,
                    $fiche->telephone_autre ?? null,
                    $fiche->gsm ?? null,
                    $fiche->contact_gsm ?? null,
                    $fiche->contact_telephone ?? null,
                    $fiche->contact_telephone_autre ?? null,
                ];

                return array_values(array_filter($phoneFields, fn($phone) => !empty($phone)));
            }
        );
    }

    public static function ficheEmails(): TwigFunction
    {
        return new TwigFunction(
            'ficheEmails',
            function (\stdClass $fiche): array {
                $emailFields = [
                    $fiche->email ?? null,
                    $fiche->contact_email ?? null,
                ];

                return array_values(array_filter($emailFields, fn($email) => !empty($email)));
            }
        );
    }

    public static function ficheUrlMap(): TwigFunction
    {
        return new TwigFunction(
            'ficheUrlMap',
            function (\stdClass $fiche): string {
                if ($fiche->latitude && $fiche->longitude) {
                    return "https://www.google.com/maps/search/?api=1&query=".$fiche->latitude.",".$fiche->longitude;
                }

                $address = "$fiche->rue $fiche->numero , $fiche->cp $fiche->localite";

                return "https://www.google.com/maps/search/?api=1&query=".urlencode($address);

            }
        );
    }
}