<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use Nette\Utils\DateTime;

final class BlogPresenter extends Nette\Application\UI\Presenter
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
        $this->template->blogPosts = $this->database->table('post')->order('datum DESC')->fetchAll();
    }

    public function renderPost($url)
    {
        $post = $this->database->table('post')->where('url = ?', $url)->fetch();
        if(!$post)
        {
            $this->redirect('Blog:default');
        }else{
            $this->template->post = $this->database->table('post')->where('url = ?', $url)->fetch();
            $this->template->allPosts = $this->database->table('post')->order('datum ASC')->fetchAll();
        }

    }
}