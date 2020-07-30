<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\MestoManager;
use App\Model\PostManager;
use App\Model\TerminManager;
use App\Model\SliderManager;
use App\Model\UserManager;
use Nette;
use Nette\ComponentModel\IComponent;
use Tracy\Debugger;
use Nette\Utils\DateTime;
use Nette\Application\UI\Form;

final class AdministracePresenter extends BaseAdminPresenter
{
    /** @var Nette\Database\Context */
    private $database;
    private $userIdentity;
    private $terminManager;
    private $postManager;
    private $userMestoId;
    private $mestoManager;
    private $sliderManager;
    private $userManager;

    public function __construct(
        Nette\Database\Context $database,
        TerminManager $terminManager,
        PostManager $postManager,
        MestoManager $mestoManager,
        SliderManager $sliderManager,
        UserManager $userManager
    )

    {
        parent::__construct();
        $this->database = $database;
        $this->terminManager = $terminManager;
        $this->postManager = $postManager;
        $this->mestoManager = $mestoManager;
        $this->sliderManager = $sliderManager;
        $this->userManager = $userManager;
    }


    protected function startup()
    {
        parent::startup();
        $this->userIdentity = $this->getUser()->getIdentity();
        $this->userMestoId = $this->database->fetchField('SELECT mesto_id FROM user WHERE id = ?', $this->userIdentity->id);
    }

    public function renderDefault(): void
    {
        $this->template->terminySchvaleni = NULL;
        $this->template->userIdentity = $this->userIdentity;
      //  Debugger::dump($this->userIdentity->getRoles());
        if($this->userIdentity->getRoles()[0] == 'superadmin')
        {
            $this->template->terminySchvaleni = $this->terminManager->getTerminyBySchvaleni('vyžadováno','nevyžadováno');
            $this->template->postSchvaleni = $this->postManager->getPostsBySchvaleni("Čeká na schválení");
        }

    }

    public function renderTermin(): void
    {
        $user_mesto_id = $this->database->fetchField('SELECT mesto_id FROM user WHERE id = ?', $this->userIdentity->id);
        $this->template->userIdentity = $this->userIdentity;
        $this->template->terminy = $this->terminManager->getTerminyByMesto($this->userMestoId);
        /*$this->template->terminy = $this->database->table('terminy')->where('mesto_id', $user_mesto_id)->fetchAll();*/

    }

    public function renderStranka(): void
    {
        $user_mesto_id = $this->database->fetchField('SELECT mesto_id FROM user WHERE id = ?', $this->userIdentity->id);
        $this->template->userIdentity = $this->userIdentity;
        $this->template->stranky = $this->database->table('stranky_mesta')->where('mesto_id', $this->userMestoId)->fetchAll();
    }

    public function renderPrehled(): void
    {
        $this->template->terminyPrehled = $this->terminManager->getTerminyPrehled();
        $this->template->postPrehled = $this->postManager->getPostPrehled();
        $this->template->userIdentity = $this->userIdentity;
    }

    public function renderPost(): void
    {
        $this->template->posts = $this->postManager->getPostsByMesto($this->userMestoId);
        $this->template->userIdentity = $this->userIdentity;
    }

    public function renderMesto(): void
    {
        $this->template->mesta = $this->mestoManager->getMesta();
       // Debugger::dump($this->mestoManager->getMesta());
    }

    public function renderSlider(): void
    {
        $this->template->sliderItems = $this->sliderManager->getSliderItems();
    }

    public function renderUzivatel(): void
    {
        $this->template->uzivatele = $this->userManager->getUsers();
    }

}
