<?php

namespace App\Model;

use App\Model\DatabaseManager;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Model pro správu termínů
 * @package App\Model
 */
class TerminManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
    TABLE_NAME = 'terminy',
    COLUMN_ID = 'id',
    COLUMN_MESTO_ID = 'mesto_id',
    FILE_DIR = 'upload/';

    /**
     * Vrátí seznam všech termínu v databázi
     * @return Selection seznam všech termínu
     */
    public function getTerminy()
    {
        return $this->database->table(self::TABLE_NAME)->order('datum' . ' DESC');
    }

    /**
     * Vrátí Termín z databáze podle MESTO ID.
     * @param int $mestoId ID mesta
     * @return false|Selection první článek, který odpovídá URL nebo false pokud článek s danou URL neexistuje
     */
    public function getTerminyByMesto($mestoId)
    {
        return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_MESTO_ID, $mestoId)->order('datum' . ' DESC')->fetchAll();
    }

    /**
     * Uloží Termín do systému.
     * Pokud není nastaveno ID vloží nový termín, jinak provede editaci termínu s daným ID.
     * @param array|ArrayHash $termin článek
     */
    public function saveTermin($termin)
    {

        $casoveRazitko = new DateTime();
        $datum = DateTime::createFromFormat('j. n. Y H:i', $termin['datum']);


        if (empty($termin[self::COLUMN_ID])) {
            unset($termin[self::COLUMN_ID]);

            $this->database->table(self::TABLE_NAME)->insert([
                'datum' => $datum,
                'tema' => $termin['tema'],
                'kde' => $termin['kde'],
                'mesto_id' => $termin['mesto_id'],
                'homepage' => $termin['homepage'],
                'online' => $termin['online']
            ]);
        } else

            $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $termin[self::COLUMN_ID])->update([
                'datum' => $datum,
                'tema' => $termin['tema'],
                'kde' => $termin['kde'],
                'mesto_id' => $termin['mesto_id'],
                'homepage' => $termin['homepage'],
                'online' => $termin['online']
            ]);
    }

    /**
     * Odstraní článek s danou URL.
     * @param string $url URL článku
     */
    public function removeTermin($id)
    {
        $pictures = $this->database->table(self::TABLE_NAME)->get($id);

      //  Debugger::dump($pictures->pic1);
       /* if($pictures->pic1){
            unlink(self::FILE_DIR.$pictures->pic1);
        }
        if($pictures->pic2){
            unlink(self::FILE_DIR.$pictures->pic2);
        } */

        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
    }

    /**
     * Nastaví hodnotu sloupce daného řádku na NULL
     * @param $pic
     * @param $id
     */
    public function removePic($id,$pic) {
        $termin = $this->getTermin($id);

        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            $pic => null
        ]);

        unlink(self::FILE_DIR.$termin[$pic]);
    }

    public function getTermin($id){
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function getTerminyBySchvaleni($homepage,$schvaleni=null)
    {
        if($schvaleni){
            return $this->database->table(self::TABLE_NAME)->where('homepage = ? AND schvaleni = ?', $homepage, $schvaleni)->fetchAll();
        }
        else{
            return $this->database->table(self::TABLE_NAME)->where('homepage = ?', $homepage)->fetchAll();
        }

    }

    public function setTerminSchvaleni($id, $schvalovatel,$termin_schvaleni_id=null)
    {
        $termin = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'schvaleni' => 'Schváleno'
        ]);

        if($termin_schvaleni_id == null) {
            $this->database->table('terminy_schvaleni')->insert([
                'termin_id' => $id,
                'schvalovatel' => $schvalovatel
            ]);
        }else{
            $this->database->table('terminy_schvaleni')->where('id = ?', $termin_schvaleni_id)->update([
                'datum' => new DateTime(),
                'schvalovatel' => $schvalovatel
            ]);
        }
    }

    public function setTerminZamitnuti($id, $schvalovatel, $termin_schvaleni_id=null)
    {
        $termin = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'schvaleni' => 'Neschváleno'
        ]);

        if($termin_schvaleni_id == null) {
            $this->database->table('terminy_schvaleni')->insert([
                'termin_id' => $id,
                'schvalovatel' => $schvalovatel
            ]);
        }else{
            $this->database->table('terminy_schvaleni')->where('id = ?', $termin_schvaleni_id)->update([
                'datum' => new DateTime(),
                'schvalovatel' => $schvalovatel
            ]);
        }
    }

    public function getTerminyPrehled()
    {
        return $this->database->table('terminy_schvaleni')->order('datum' . ' DESC')->fetchAll();
    }

}