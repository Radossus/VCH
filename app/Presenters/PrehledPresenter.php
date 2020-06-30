<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\PostManager;
use App\Model\TerminManager;
use Nette;
use Tracy\Debugger;

final class PrehledPresenter extends BaseAdminPresenter
{

    private $terminManager;
    private $postManager;

    public function __construct(TerminManager $terminManager, PostManager $postManager)
    {
        parent::__construct();
        $this->terminManager = $terminManager;
        $this->postManager = $postManager;
    }

    public function startup()
    {
        parent::startup();
        if (!$this->getUser()->getRoles()[0] == 'superadmin') {
            $this->redirect('Prihlaseni:in');
        }
    }

    public function renderPostDetail($id)
    {
        $this->template->post = $this->postManager->getPost($id);
        $this->template->postSchvaleniId = $this->postManager->getPrehledByPost($id);
    }

    public function actionTerminAno($id, $termin_schvaleni_id=null): void
    {
        $this->terminManager->setTerminSchvaleni($id, $this->getUser()->getIdentity()->username,$termin_schvaleni_id);
        $this->flashMessage('Termín byl schválen.');
        if($termin_schvaleni_id){
            $this->redirect('Administrace:prehled');
        }else{
            $this->redirect('Administrace:default');
        }
    }

    public function actionTerminNe($id, $termin_schvaleni_id=null): void
    {
        $this->terminManager->setTerminZamitnuti($id, $this->getUser()->getIdentity()->username,$termin_schvaleni_id);
        $this->flashMessage('Termín byl zamítnut.');
        if($termin_schvaleni_id){
            $this->redirect('Administrace:prehled');
        }else{
            $this->redirect('Administrace:default');
        }
    }

    public function actionPostAno($id, $post_schvaleni_id=null): void
    {
        $this->postManager->setPostSchvaleni($id, $this->getUser()->getIdentity()->username, $post_schvaleni_id);
        $this->flashMessage('Příspěvek byl schválen.');
        if($post_schvaleni_id){
            $this->redirect('Administrace:prehled');
        }else{
            $this->redirect('Administrace:default');
        }
    }

    public function actionPostNe($id,$post_schvaleni_id=null): void
    {
        $this->postManager->setPostZamitnuti($id, $this->getUser()->getIdentity()->username,$post_schvaleni_id);
        $this->flashMessage('Příspěvek byl zamítnut.');
        if($post_schvaleni_id){
            $this->redirect('Administrace:prehled');
        }else{
            $this->redirect('Administrace:default');
        }
    }

}
