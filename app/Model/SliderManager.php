<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class SliderManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
        TABLE_NAME = 'slider',
        COLUMN_ID = 'id',
        FILE_DIR = 'upload/';

    public function getSliderItems()
    {
        return $this->database->table(self::TABLE_NAME)->order('poradi')->fetchAll();
    }

    public function getItemOrder()
    {
        return $this->database->table(self::TABLE_NAME)->fetchAll();
    }

    public function getItem($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function savePost($item, $filename = null)
    {
        if(empty($item['poradi']))
        {
            $poradi = null;
        }
        else
        {
            $poradi = $item['poradi'];
        }

        if (empty($item[self::COLUMN_ID])) {
            unset($item[self::COLUMN_ID]);


            $this->database->table(self::TABLE_NAME)->insert([
                'popisek' => $item['popisek'],
                'url' => $item['url'],
                'poradi' => $poradi,
                'zobrazit' => $item['zobrazit'],
                'pic' => $filename,
            ]);

        }else
            //Debugger::dump($uvodniStrana);
            $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $item[self::COLUMN_ID])->update([
                'popisek' => $item['popisek'],
                'url' => $item['url'],
                'poradi' => $poradi,
                'zobrazit' => $item['zobrazit'],
                'pic' => $filename,
            ]);

    }

    public function removeSliderItem($id) : void
    {
        $row = $this->database->table(self::TABLE_NAME)->get($id);

        if($row->pic){
            unlink(self::FILE_DIR.'slider/'.$row->pic);
        }
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
    }

    public function removePic($id,$pic)
    {
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'pic' => null
        ]);

        unlink(self::FILE_DIR."slider/".$pic);
    }

    public function getPicName($id){
        return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->select('pic')->fetch();
    }
}