<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Bottin\Bottin;

class BottinRepository
{
    private ?\PDO $dbh = null;
    private static ?self $instance = null;

    private function init(): void
    {
        if (!$this->dbh) {
            $dsn = 'mysql:host=127.0.0.1;dbname=bottin';
            $username = $_ENV['DB_BOTTIN_USER'];
            $password = $_ENV['DB_BOTTIN_PASS'];
            $options = array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            );
            $this->dbh = new \PDO($dsn, $username, $password, $options);
        }
    }

    public static function instanceBottinRepository(): self
    {
        if (!self::$instance) {
            self::$instance = new BottinRepository();
            self::$instance->init();
        }

        return self::$instance;
    }

    public function getClassementsFiche(int $ficheId): array|bool
    {
        $sql = 'SELECT * FROM classements WHERE `fiche_id` = '.$ficheId.' ORDER BY `principal` DESC ';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function getCategoriesOfFiche(int $ficheId): array
    {
        $categories = [];
        $classements = $this->getClassementsFiche($ficheId);
        foreach ($classements as $classement) {
            $category = $this->getCategory($classement['category_id']);
            if ($category) {
                $category->principal = $classement['principal'];
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public function getCategoriePrincipale(object $fiche): ?object
    {
        $categories = $this->getCategoriesOfFiche($fiche->id);
        $classementPrincipal = array_filter(
            $categories,
            function ($category) {
                if ($category->principal) {
                    return $category;
                }

                return null;
            }
        );
        if ($classementPrincipal !== []) {
            return $classementPrincipal[0];
        }
        if ($categories !== []) {
            return $categories[0];
        }

        return null;
    }

    /**
     *
     * @return object|bool
     * @throws \Exception
     */
    public function getFicheById(int $id): bool|object
    {
        $sql = 'SELECT * FROM fiche WHERE `id` = '.$id;
        $query = $this->execQuery($sql);

        return $query->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * @param int $id
     *
     * @return object|bool
     * @throws \Exception
     */
    public function getFicheBySlug(string $slug): ?object
    {
        $this->init();
        $sql = 'SELECT * FROM fiche WHERE `slug` = :slug ';
        $sth = $this->dbh->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute(array(':slug' => $slug));
        if (!$data = $sth->fetch(\PDO::FETCH_OBJ)) {
            return null;
        }

        return $data;
    }

    /**
     * @return object[]
     * @throws \Exception
     */
    public function getFiches(): array|bool
    {
        $sql = 'SELECT * FROM fiche';
        $query = $this->execQuery($sql);

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @throws \Exception
     */
    public function getImagesFiche(int $id): array|bool
    {
        $sql = 'SELECT * FROM fiche_images WHERE `fiche_id` = '.$id.' ORDER BY `principale` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @throws \Exception
     */
    public function getDocuments(int $id): array|bool
    {
        $sql = 'SELECT * FROM document WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    /**
     * @throws \Exception
     */
    public function getSituations(int $id): array|bool
    {
        $sql = 'SELECT * FROM `fiche_situation` LEFT JOIN situation ON situation.id = fiche_situation.situation_id WHERE `fiche_id` = '.$id.' ORDER BY `name` DESC';
        $query = $this->execQuery($sql);

        return $query->fetchAll();
    }

    public function isCentreVille(int $id): bool
    {
        $situations = $this->getSituations($id);
        foreach ($situations as $situation) {
            if (in_array('Centre ville', $situation)) {
                return true;
            }
        }

        return false;
    }

    public function getLogo(int $id): ?string
    {
        $images = $this->getImagesFiche($id);
        $logo = null;

        if ($images !== []) {
            $logo = Bottin::getUrlBottin().$id.DIRECTORY_SEPARATOR.$images[0]['image_name'];
        }

        return $logo;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function getCategory(?int $id): ?object
    {
        if (!$id) {
            return null;
        }
        $sql = 'SELECT * FROM category WHERE `id` = '.$id;
        $sth = $this->execQuery($sql);
        if (!$data = $sth->fetch(\PDO::FETCH_OBJ)) {
            return null;
        }

        return $data;
    }

    /**
     *
     * @return object|bool
     * @throws \Exception
     */
    public function getCategoryBySlug(string $slug): ?object
    {
        $this->init();
        $sql = 'SELECT * FROM category WHERE `slug` = :slug ';
        $sth = $this->dbh->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute(array(':slug' => $slug));
        if (!$data = $sth->fetch(\PDO::FETCH_OBJ)) {
            return null;
        }

        return $data;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function getCategories(?int $parentId): array|bool
    {
        if ($parentId == null) {
            $sql = 'SELECT * FROM category WHERE `parent_id` IS NULL';
        } else {
            $sql = 'SELECT * FROM category WHERE `parent_id` = '.$parentId;
        }
        $query = $this->execQuery($sql);

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     *
     * @throws \Exception
     */
    public function getAllCategories(): array|bool
    {
        $sql = 'SELECT * FROM category ORDER BY `name` ';
        $query = $this->execQuery($sql);

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getFichesByCategories(array $ids): array
    {
        $fiches = [[]];
        foreach ($ids as $id) {
            $fiches[] = $this->getFichesByCategory($id);
        }

        $fiches = array_merge(...$fiches);

        return $this->sortFiches($fiches);
    }

    public function getFichesByCategory(int $id): array
    {
        $sql = 'SELECT * FROM classements WHERE `category_id` = '.$id;
        $query = $this->execQuery($sql);
        $classements = $query->fetchAll();

        $fiches = array_map(
            fn($classement) => $this->getFicheById($classement['fiche_id']),
            $classements
        );

        $data = [];
        foreach ($fiches as $fiche) {
            $data[$fiche->id] = $fiche;
        }

        return $this->sortFiches($data);
    }

    /**
     * @param $sql
     *
     * @throws \Exception
     */
    public function execQuery($sql): \PDOStatement|false
    {
        $this->init();
        $query = $this->dbh->query($sql);
        $error = $this->dbh->errorInfo();
        if ($error[0] != '0000') {
            //Mailer::sendError("wp error sql", $sql.' '.$error[2]);
            throw new \Exception($error[2]);
        }

        return $query;
    }

    public function isEconomy(array $categories): ?\stdClass
    {
        foreach ($categories as $category) {
            if (isset($category->parent_id)) {
                $parent = $this->getCategory($category->parent_id);
                if (in_array($parent->id, Bottin::ALL)) {
                    return $category;
                }
                if (isset($parent->parent_id)) {
                    $parent2 = $this->getCategory($parent->parent_id);
                    if (in_array($parent2->id, Bottin::ALL)) {
                        return $category;
                    }
                }
            }
        }

        return null;
    }

    public function findSiteFiche(object $fiche): int
    {
        if ($classementPrincipal = $this->getCategoriePrincipale($fiche)) {
            list($vide, $root) = explode('/', $classementPrincipal->materialized_path);
            if ($root) {
                return match ($root) {
                    '485' => Theme::TOURISME,
                    '486' => Theme::SPORT,
                    '487' => Theme::SOCIAL,
                    '488' => Theme::SANTE,
                    '511' => Theme::ECONOMIE,
                    '663' => Theme::CULTURE,
                    '664' => Theme::ADMINISTRATION,
                    '671' => Theme::ENFANCE,
                    default => Theme::CITOYEN,
                };
            }
        }

        return Theme::CITOYEN;
    }

    private function sortFiches(array $fiches): array
    {
        usort(
            $fiches,
            function ($a, $b) {
                {
                    if ($a->societe == $b->societe) {
                        return 0;
                    }

                    return ($a->societe < $b->societe) ? -1 : 1;
                }
            }
        );

        return $fiches;
    }
}
