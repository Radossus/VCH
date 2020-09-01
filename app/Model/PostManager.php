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
class PostManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
    TABLE_NAME = 'post',
    COLUMN_ID = 'id',
    COLUMN_MESTO_ID = 'mesto_id',
    FILE_DIR = 'upload/post/';

    /**
     * Vrátí seznam všech termínu v databázi
     * @return Selection seznam všech termínu
     */
    public function getPosts()
    {
        return $this->database->table(self::TABLE_NAME)->order('datum' . ' DESC');
    }

    public function getPost($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function getPostsByMesto($mesto_id)
    {
        return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_MESTO_ID, $mesto_id)->order('datum' . ' DESC');
    }

    public function removePost($id):void
    {
        $pictures = $this->database->table(self::TABLE_NAME)->get($id);

        if($pictures->pic){
            $file_exists = file_exists(self::FILE_DIR.'intro_pic/'.$pictures->pic);
            if($file_exists){
                unlink(self::FILE_DIR.'intro_pic/'.$pictures->pic);
            }
        }

        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();

    }

    public function getPostById($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function savePost($post, $filename = null)
    {
        $datum = DateTime::createFromFormat('j. n. Y', $post['datum']);

        if (empty($post[self::COLUMN_ID])) {
            unset($post[self::COLUMN_ID]);

            $this->database->table(self::TABLE_NAME)->insert([
                'datum' => $datum,
                'nazev' => $post['nazev'],
                'autor' => $post['autor'],
                'mesto_id' => $post['mesto_id'],
                'obsah' => $post['obsah'],
                'post_kategorie_id' => $post['kategorie'],
                'url' => $this->frendlyUrl($post['nazev']."-".date_format($datum,"j-n-Y")),
                'pic' => $filename,
                'user' => $post['user']
            ]);
        } else
            //Debugger::dump($uvodniStrana);
            $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $post[self::COLUMN_ID])->update([
                'datum' => $datum,
                'nazev' => $post['nazev'],
                'autor' => $post['autor'],
                'obsah' => $post['obsah'],
                'pic' => $filename,
                'post_kategorie_id' => $post['kategorie'],
            ]);
    }

    public function getKategorie()
    {
        return $this->database->table('post_kategorie')->fetchAll();
    }

    public function removeIntroPic($id,$pic)
    {
        $post = $this->getPost($id);

        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'pic' => null
        ]);

        if($pic != 'default_article.jpg'){

            unlink(self::FILE_DIR."intro_pic/".$pic);
        }

    }

    public function getPostsBySchvaleni($schvaneni)
    {
        return $this->database->table(self::TABLE_NAME)->where('schvaleni ?', $schvaneni)->fetchAll();
    }

    public function setPostSchvaleni($post_id, $schvalovatel, $post_chvaleni_id = null)
    {
        $post = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $post_id)->update([
            'schvaleni' => 'Schváleno'
        ]);

        if($post_chvaleni_id == null){
            $this->database->table('post_schvaleni')->insert([
                'post_id' => $post_id,
                'schvalovatel' => $schvalovatel
            ]);
        }else{
            $this->database->table('post_schvaleni')->where('id = ?', $post_chvaleni_id)->update([
                'datum' => new DateTime(),
                'schvalovatel' => $schvalovatel
            ]);
        }

    }

    public function setPostZamitnuti($post_id, $schvalovatel,$post_chvaleni_id=null)
    {
        $post = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $post_id)->update([
            'schvaleni' => 'Neschváleno'
        ]);

        if($post_chvaleni_id == null) {
            $this->database->table('post_schvaleni')->insert([
                'post_id' => $post_id,
                'schvalovatel' => $schvalovatel
            ]);
        }else{
            $this->database->table('post_schvaleni')->where('id = ?', $post_chvaleni_id)->update([
                'datum' => new DateTime(),
                'schvalovatel' => $schvalovatel
            ]);
        }
    }

    public function getPostPrehled()
    {
        return $this->database->table('post_schvaleni')->order('datum' . ' DESC')->fetchAll();
    }

    public function getPrehledByPost($idPost)
    {
        return $this->database->table('post_schvaleni')->where('post_id = ?', $idPost)->Select('id')->fetch();
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

    public function setTerminSchvaleni($id, $schvalovatel)
    {
        $termin = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'schvaleni' => 'schváleno'
        ]);

        $this->database->table('terminy_schvaleni')->insert([
            'termin_id' => $id,
            'schvalovatel' => $schvalovatel
        ]);
    }

    public function setTerminZamitnuti($id, $schvalovatel)
    {
        $termin = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
            'schvaleni' => 'neschváleno'
        ]);

        $this->database->table('terminy_schvaleni')->insert([
            'termin_id' => $id,
            'schvalovatel' => $schvalovatel
        ]);
    }

    public function getTerminyPrehled()
    {
        return $this->database->table('terminy_schvaleni')->order('datum' . ' DESC')->fetchAll();
    }

}