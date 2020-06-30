<?php


namespace App\Model;
use Nette\Database\Table\Selection;


use Nette\Database\Context;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Model pro správu měst
 * @package App\Model
 */
class MestoManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
        TABLE_NAME = 'mesto',
        COLUMN_ID = 'id';

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }

    public function getMesta()
    {
        return $this->database->table(self::TABLE_NAME)->order("mesto")->fetchAll();
    }

    public function getMesto($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function saveMesto($items)
    {
      //  Debugger::dump($items);
        if (empty($items[self::COLUMN_ID])) {
            unset($items[self::COLUMN_ID]);

            $this->database->table(self::TABLE_NAME)->insert([
                'mesto' => $items['mesto'],
                'url' => $this->frendlyUrl($items['mesto']),
                'top_position' => $items['top_position'],
                'left_position' => $items['left_position']
            ]);
        } else

            $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $items[self::COLUMN_ID])->update([
                'id' => $items['id'],
                'mesto' => $items['mesto'],
                'url' => $items['url'],
                'top_position' => $items['top_position'],
                'left_position' => $items['left_position']
            ]);
    }

    public function removeMesto($id)
    {

        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
    }

}