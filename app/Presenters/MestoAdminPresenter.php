<?php


namespace App\Presenters;


use App\Model\MestoManager;
use Nette\Application\UI\Form;

class MestoAdminPresenter extends BaseAdminPresenter
{
    private $mestoManager;

    public function __construct(MestoManager $mestoManager)
    {
        $this->mestoManager = $mestoManager;
    }

    public function startup()
    {
        parent::startup();
    }

    protected function createComponentEditorMesto(): Form
    {
        $form = new Form;
        $form->addGroup('Nové město');
        $form->addHidden('id');

        $form->addText('mesto', 'Jméno města: ')
            ->setRequired('Prosím vyplňte jméno města.');

        $form->addText('url', 'URL adresa města: ');

        $form->addText('top_position' , 'Pozice bodu z hora: ');

        $form->addText('left_position' , 'Pozice bodu z leva: ');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'novyMestoSucceeded'];
        return $form;
    }

    public function novyMestoSucceeded(Form $form, array $values): void{

        $this->mestoManager->saveMesto($values);

        $this->flashMessage('Město bylo úspěšně uloženo.');
        $this->redirect('MestoAdmin:editace',$values['id']);
    }



    public function actionEditace($id = null)
    {
        $item = $this->mestoManager->getMesto($id);

        if($item){
            $this['editorMesto']->setDefaults([
                'id' => $item['id'],
                'mesto' => $item['mesto'],
                'url' => $item['url'],
                'top_position' => $item['top_position'],
                'left_position' => $item['left_position'],
            ]);
        }
        $this->template->id = $id;
        $this->template->top_position = $item['top_position'];
        $this->template->left_position = $item['left_position'];
        $this->template->url = $item['url'];
      //  $this->template->linkGenerator = $this->linkGenerator->link('Post:upload',['mesto_id' => $this->userMestoId,'adresar' => 'post' ]);
    }

    public function actionRemove($id)
    {
        $this->mestoManager->removeMesto($id);
        $this->flashMessage('Město bylo úspěšně smazáno.');
        $this->redirect('Administrace:mesto');
    }    
}