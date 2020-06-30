<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\SmartObject;

/**
 * Základní model pro všechny ostatní databázové modely aplikace.
 * Poskytuje přístup k práci s databází.
 * @package App\Model
 */
abstract class DatabaseManager
{
    use SmartObject;

    /** @var Context Služba pro práci s databází. */
    protected $database;
    private $userMestoId;
    private $userIdentity;

    /**
     * Konstruktor s injektovanou službou pro práci s databází.
     * @param Context $database automaticky injektovaná Nette služba pro práci s databází
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getUserMestoId($userID)
    {
        return $this->database->fetchField('SELECT mesto_id FROM user WHERE id = ?', $userID);
    }

    public function getSuperAdmin()
    {
        return $this->database->table('user')->where('role', 'superadmin')->fetchAll();
    }

    /**
     * Generuje user-frendly-url.
     * @param string|null $nadpis termínu
     */
    public function frendlyUrl(String $nadpis)
    {
        $url = $nadpis;
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url);
        return $url;
    }


}