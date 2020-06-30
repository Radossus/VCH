<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\StrankaManager;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette;

class StrankaMestaPresenter extends BaseAdminPresenter
{
    private $strankaManager;
    private $userMestoId;
    const FILE_DIR = 'upload/stranky/';
    private $linkGenerator;


    public function __construct(StrankaManager $strankaManager,Nette\Application\LinkGenerator $generator)
    {
        parent::__construct();
        $this->strankaManager = $strankaManager;
        $this->linkGenerator = $generator;
    }

    public function startup()
    {
        parent::startup();
        $this->userMestoId = $this->strankaManager->getUserMestoId($this->getUser()->getIdentity()->id);
    }

    protected function createComponentEditorStranka(): Form
    {
        $form = new Form;
        $form->addGroup('Stránka');
        $form->addHidden('id');

        $form->addText('nazev', 'Titulek stránky:')
            ->setRequired('Prosím vyplňte jméno stránky.');

        $form->addTextArea('obsah', 'Obsah stránky:',null,25);

        $form->addCheckbox('uvodni', 'Zobrazit jako první stránku?');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'novaStrankaSucceeded'];
        return $form;
    }

    public function novaStrankaSucceeded(Form $form, array $values): void
    {
        $values['mesto_id'] = $this->userMestoId;
        $this->strankaManager->saveStranka($values);

        if($values['id']){
            $this->flashMessage("Stránka byla úspěšně editována.", 'success');
        }else{
            $this->flashMessage("Stránka byla úspěšně vložena.", 'success');
        }

     //  $this->redirect('Administrace:stranka');
    }

    public function actionEditace($id = null)
    {
        $this->template->id = $id;

        $stranka = $this->strankaManager->getStranka($id);
        if($stranka)
        {

            $this['editorStranka']->setDefaults([
                'id' => $stranka['id'],
                'nazev' => $stranka['nazev'],
                'obsah' => $stranka['obsah'],
                'mesto_id' => $stranka['mesto_id'],
                'uvodni' => $stranka['uvodni']
            ]);
        }
        $this->template->id = $id;
        $this->template->linkGenerator = $this->linkGenerator->link('StrankaMesta:upload',['mesto_id' => $this->userMestoId,'adresar' => 'stranka' ]);
    }

    public function actionRemove($id = null)
    {
        $this->strankaManager->removeStranka($id);
        $this->flashMessage('Stránka byla úspěšně odstraněna.');
        $this->redirect('Administrace:stranka');
    }










}