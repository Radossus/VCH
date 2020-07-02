<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use Nette\Utils\DateTime;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
        parent::__construct();
	    $this->database = $database;
    }
    
    public function renderDefault(): void
    {
        $today = new DateTime();
        $todayPlus = new DateTime();
        $todayPlus->modify('+1 month');
        $this->template->pages = $this->database->table('pages')->order('created_at DESC');
        $this->template->mesta = $this->database->table('mesto')->order('mesto ASC');
        $this->template->bannerDates = $this->database->table('terminy')->where('datum >= ?', $today)->order('datum')->limit(10)->fetchAll();

        $blogPosts = $this->database->table('post')->where('schvaleni = ?','Schváleno')->limit(3)->order('datum ASC')->fetchAll();
        $this->template->blogPosts = $blogPosts;

        $schvaleneTerminy = $this->database->table('terminy')->where('schvaleni = ? AND homepage ? AND DATUM >= ?','schvaleno','vyžadováno',$today)->order('datum ASC')->fetchAll();
        $this->template->schvaleneTerminy = $schvaleneTerminy;

        $homeNews = array_merge($blogPosts, $schvaleneTerminy);

        /* serazeni pole */
        usort($homeNews,[__CLASS__, 'sortByDatum']);
        $this->template->hpNews = $homeNews;
        /*
        foreach ($homeNews as $item) {
           echo $item->datum .'<br>';
            if (isset($item->kde) ){
                echo $item->kde.'<br>';
                $homeNews[]['novinka'] = 'termin';
            }
            if (isset($item->mesto->mesto) ){
                echo $item->mesto->mesto.'<br>';
            }
        }
        */

    }

    function sortByDatum($a, $b) {
        if ( $a['datum'] == $b['datum'] ) {
            return 0;
        }
        return ($a['datum'] > $b['datum'])? 1: -1;
    }


    public function renderMapa(): void
    {
        $this->template->mesta = $this->template->mesta = $this->database->table('mesto')->group('mesto.id')->having('COUNT(:terminy.datum)>0')->order('mesto ASC')->fetchAll();

        $this->template->mestaAkce = $this->getMestaAkce();
    }

    public function getMestaAkce()
    {

        $mesta = $this->database->table('mesto');
        $mestaAkceDnes = $this->database->table('mesto')->where(['DATE(:terminy.datum) =' => new \Nette\Database\SqlLiteral('DATE(NOW())')])->fetchAll();
        $MestaBezAkceDnes = $this->database->table('mesto')->where(['DATE(:terminy.datum) !=' => new \Nette\Database\SqlLiteral('DATE(NOW())')])->group('mesto_id')->fetchAll();

        $mestaStav = array();
        foreach($MestaBezAkceDnes as $mestoBezAkce){
            $mestaStav[$mestoBezAkce->url]['mesto'] = $mestoBezAkce->mesto;
            $mestaStav[$mestoBezAkce->url]['url'] = $mestoBezAkce->url;
            $mestaStav[$mestoBezAkce->url]['akce'] = FALSE;
            $mestaStav[$mestoBezAkce->url]['top_position'] = $mestoBezAkce->top_position;
            $mestaStav[$mestoBezAkce->url]['left_position'] = $mestoBezAkce->left_position;
        }

        foreach($mestaAkceDnes as $mestoAkceDnes){
            $mestaStav[$mestoAkceDnes->url]['akce'] = True;
            $mestaStav[$mestoAkceDnes->url]['top_position'] = $mestoAkceDnes->top_position;
            $mestaStav[$mestoAkceDnes->url]['left_position'] = $mestoAkceDnes->left_position;
            $mestaStav[$mestoAkceDnes->url]['mesto'] = $mestoAkceDnes->mesto;
            $mestaStav[$mestoAkceDnes->url]['url'] = $mestoAkceDnes->url;
        }
     //   Debugger::dump($mestoAkceDnes);
        return $mestaStav;
    }

    public function renderStrana($url): void
    {
        $this->template->strana = $this->database->table('pages')->where('url',$url)->fetch();
    }

    public function renderOnline(): void
    {
        $this->template->terminy = $this->database->table('terminy')->where('online',true)->order('datum ASC')->fetchAll();
    }

    public function renderTerminy()
    {
        $today = new DateTime();
        $this->template->terminy = $this->database->table('terminy')->where('datum >= ? ',$today)->order('datum')->fetchAll();
        $this->template->mesta = $this->template->mesta = $this->database->table('mesto')->group('mesto.id')->having('COUNT(:terminy.datum)>0')->order('mesto ASC')->fetchAll();
    }
    
}
