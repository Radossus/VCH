<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Model pro správu uživatelů
 * @package App\Model
 */
class UserManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
        TABLE_NAME = 'user',
        COLUMN_ID = 'id',
        COLUMN_MESTO_ID = 'mesto_id';

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }


    public function getSuperAdmin()
    {
        Debugger::dump($this->database->table(self::TABLE_NAME)->fetchAll());
        return $this->database->table(self::TABLE_NAME)->fetchAll();
    }

}