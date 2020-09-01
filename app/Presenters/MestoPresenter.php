<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use Nette\Utils\DateTime;

final class MestoPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
        parent::__construct();
	    $this->database = $database;
    }

    public function renderMesto(String $url, String $page=NULL): void
    {
        $this->template->stranaMesta = NULL;
      //  $this->template->mesta = $this->database->table('mesto')->order('mesto ASC');

        $this->template->mesta = $this->database->table('mesto')->group('mesto.id')->having('COUNT(:terminy.datum)>1')->order('mesto ASC')->fetchAll();
        $mesto = $this->database->table('mesto')->where('url ?',$url)->fetch();
        $this->template->jmenoMesta = $mesto->mesto;
        $this->template->urlMesta = $mesto->url;
        $this->template->strankaUvod = $this->database->table('stranky_mesta')->where('mesto_id ? AND uvodni ?',$mesto->id, 'TRUE')->fetch();
        $this->template->stranky_mesta = $this->database->table('stranky_mesta')->where('mesto_id ?',$mesto->id)->fetchAll();
        $this->template->terminy = $this->database->table('terminy')->where('mesto_id ? AND datum >= ?',$mesto->id, new Datetime())->order('datum ASC')->fetchAll();
        if($page){
            $this->template->stranaMesta = $this->database->table('stranky_mesta')->where('mesto_id ? AND url ?', $mesto->id, $page)->fetch();
        }
    }
}