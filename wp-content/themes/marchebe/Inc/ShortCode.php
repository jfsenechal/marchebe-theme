<?php

namespace AcMarche\Theme\Inc;

use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Twig;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function json_decode;

class ShortCode
{
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;

    public function __construct()
    {
        add_action('init', [$this, 'registerShortcodes']);
    }

    function registerShortcodes()
    {
        add_shortcode('taxe', [$this, 'taxe']);
        add_shortcode('enaos', [$this, 'enaos']);
        add_shortcode('conseil_ordre', [$this, 'conseilOrdre']);
        add_shortcode('conseil_pv', [$this, 'conseilPv']);
        add_shortcode('conseil_archive', [$this, 'conseilArchive']);
        add_shortcode('google_map', [$this, 'googleMap']);
        add_shortcode('capteur_list', [$this, 'capteurList']);
        add_shortcode('capteur_color', [$this, 'capteurColor']);
    }

    public function conseilOrdre(): string
    {
        $conseilDb = new ConseilDb();
        $ordres = $conseilDb->getAllOrdre();
        $twig = Twig::LoadTwig();

        return $twig->render(
            'conseil/_ordre.html.twig',
            [
                'ordres' => $ordres,
            ]
        );
    }

    public function conseilPv(): string
    {
        $year = date('Y');
        $conseilDb = new ConseilDb();
        $pvs = $conseilDb->getByYearPvs($year);
        $twig = Twig::LoadTwig();

        return $twig->render(
            'conseil/_pv.html.twig',
            [
                'pvs' => $pvs,
                'year' => $year,
            ]
        );
    }

    public function conseilArchive(): string
    {
        $twig = Twig::LoadTwig();
        $conseilDb = new ConseilDb();
        $endYear = date('Y') - 1;
        $txt = '';

        $years = range(2019, $endYear);
        rsort($years, SORT_NUMERIC);

        foreach ($years as $year) {
            $pvs = $conseilDb->getByYearPvs($year);
            $pvs = array_map(
                function ($pv) {
                    $pv['name'] = $pv['nom'];
                    $pv['url'] = '/wp-content/uploads/conseil/pv/'.$pv['file_name'];

                    return $pv;
                },
                $pvs
            );
            $txt .= $twig->render(
                'conseil/_archive.html.twig',
                [
                    'year' => $year,
                    'pvs' => $pvs,
                ]
            );
        }

        $txt .= $this->getArchiveByFiles();

        return $txt;
    }

    private function getArchiveByFiles()
    {
        $twig = Twig::LoadTwig();
        $conseil = new Conseil();

        $years = range(2013, 2018);
        rsort($years, SORT_NUMERIC);

        $txt = '';
        foreach ($years as $year) {
            $pvs = $conseil->find_all_files($year);
            $txt .= $twig->render(
                'conseil/_archive.html.twig',
                [
                    'year' => $year,
                    'pvs' => $pvs,
                ]
            );
        }

        return $txt;
    }

    public function enaos(): ?string
    {
        $cacheKey = Cache::generateKey('enaos');

        return Cache::get($cacheKey, function () {
            return file_get_contents('https://api.marche.be/marchebe/necrologie/');
            $content = wp_remote_get('https://api.marche.be/marchebe/necrologie/');//timeout
            if ($content instanceof \WP_Error) {
                return $content->get_error_message();
            } else {
                return $content['body'];
            }
        }
        );
    }

    public function taxe(): string
    {
        $cacheKey = Cache::generateKey('liste_taxes');

        return Cache::get($cacheKey, function () {
            $this->httpClient = HttpClient::create();
            $nomenclatures = $this->getContent('/taxes/api2');
            if ($nomenclatures) {
                $twig = Twig::LoadTwig();
                $template = '@AcMarche/article/_taxe.html.twig';

                return $twig->render($template, ['nomenclatures' => $nomenclatures]);
            }

            return 'Erreur de chargement de la page';
        });
    }

    private function getContent(string $url)
    {
        $base = 'https://extranet.marche.be';
        try {
            $response = $this->httpClient->request('GET', $base.$url);

            return json_decode($response->getContent());
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {

            return null;
        }
    }

    public function googleMap(array $args): string
    {
        $latitude = $args['lat'];
        $longitude = $args['long'];
        $twig = Twig::LoadTwig();
        $post = get_post();
        $title = $post ? $post->post_title : '';

        $t = $twig->render(
            'map/_carte.html.twig',
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'title' => $title,
            ]
        );
        $t = preg_replace("#\n#", "", $t);//bug avec raw de twig

        return $t;
    }

    public function capteurList(): string
    {
        $twig = Twig::LoadTwig();
        $capteur = new Capteur();

        return $twig->render(
            'capteur/_list.html.twig',
            [
                'stations' => $capteur->getStations(),
            ]
        );
    }

    public function capteurColor(): string
    {
        $twig = Twig::LoadTwig();

        return $twig->render(
            'capteur/_colors.html.twig',
            [
                'indices' => IndiceEnum::cases(),
            ]
        );
    }

}
