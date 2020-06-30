<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class StrankaManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
        TABLE_NAME = 'stranky_mesta',
        COLUMN_ID = 'id',
        COLUMN_MESTO_ID = 'mesto_id';

    /**
     * Vrátí seznam všech stránek města v databázi
     * @return Selection seznam všech termínu
     */
    public function getStranky()
    {
        return $this->database->table(self::TABLE_NAME)->order('datum' . ' DESC');
    }

    /**
     * Vrátí Stránky města z databáze podle MESTO ID.
     * @param int $mestoId ID mesta
     * @return false|Selection stránky mesta, kterým odpovídá mestoID nebo false pokud taková stránka neexistuje
     */
    public function getStrankyByMesto($mestoId)
    {
        return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_MESTO_ID, $mestoId)->fetchAll();
    }

    public function removeStranka($id)
    {
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
    }

    public function saveStranka($stranka)
    {
        if($stranka['uvodni'] == false) {
            $uvodniStrana = 'FALSE';
        } else {
            $uvodniStrana = 'TRUE';
        }

        if (empty($stranka[self::COLUMN_ID])) {
            unset($stranka[self::COLUMN_ID]);

            $this->database->table(self::TABLE_NAME)->insert([
                'nazev' => $stranka['nazev'],
                'obsah' => $stranka['obsah'],
                'mesto_id' => $stranka['mesto_id'],
                'uvodni' => $uvodniStrana,
                'url' => $this->frendlyUrl($stranka['nazev'])
            ]);
        } else
            //Debugger::dump($uvodniStrana);
            $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $stranka[self::COLUMN_ID])->update([
                'nazev' => $stranka['nazev'],
                'obsah' => $stranka['obsah'],
                'uvodni' => $uvodniStrana
            ]);

    }

    public function getStranka($id){
        return $this->database->table(self::TABLE_NAME)->get($id);
    }



}