<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\TerminManager;
use Cassandra\Date;
use http\Exception;
use Nette;
use Nette\ComponentModel\IComponent;
use Tracy\Debugger;
use Nette\Utils\DateTime;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Tracy\Dumper;
use function Sodium\add;

final class TerminPresenter extends BaseAdminPresenter
{
    /** @var Nette\Database\Context */
    private $terminManager;
    private $userMestoId;
    private $linkGenerator;
    const FILE_DIR = 'upload/termin/';

    public function __construct(TerminManager $terminManager,Nette\Application\LinkGenerator $generator)
    {
        parent::__construct();
        $this->terminManager = $terminManager;
        $this->linkGenerator = $generator;
    }

    protected function startup()
    {
        parent::startup();
        $this->setLayout('admin');
        if (!$this->getUser()->loggedIn) {
            $this->redirect('Prihlaseni:in');
        }
        $this->userMestoId = $this->terminManager->getUserMestoId($this->getUser()->getIdentity()->id);
    }

    /* správa termínů */
    protected function createComponentEditorTermin(): Form
    {
        $form = new Form;
        $form->addGroup('Nový Termin');
        $form->addHidden('id');
        $form->addHidden('schvaleni');

        $form->addText('datum', 'Datum a čas:')
            ->setRequired('Prosím vyplňte datum a čas.');

        $form->addTextArea('tema', 'Téma večeru chval:',null,25);

        $form->addText('kde', 'Místo konání:')->setRequired('Místo konání musí být vyplněno!');

        $volba = [
            'nevyžadováno' => 'nevyžadováno',
            'vyžadováno' => 'vyžadováno'
        ];

        $opakovani = [
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
        ];

        $form->addSelect('opakovani','Počet opakování: ')->setItems($opakovani);

        $form->addCheckbox('online', ' Večer chval vysílán živě jako stream?');

        $form->addSelect('homepage', 'Vložit na úvodní stranu webu (poslat ke schválení): ')->setItems($volba);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'novyTerminSucceeded'];
        return $form;
    }

    public function novyTerminSucceeded(Form $form, array $values): void{

        $values['mesto_id'] = $this->userMestoId;

        for ($i=0; $i <= $values['opakovani']; $i++){
            $this->terminManager->saveTermin($values);
        }

        if($values['homepage'] == 'vyžadováno' && ($values['schvaleni'] == 'Nevyžadováno' || $values['schvaleni'] =='')){
            $superadmin = $this->terminManager->getSuperAdmin();

            if($values['schvaleni'] ==''){
                $data = [
                    'akce' => 'nový',
                    'co' => 'termín',
                ];
            }
            if($values['schvaleni'] == 'Nevyžadováno' ){
                $data = [
                    'akce' => 'editovaný',
                    'co' => 'termín',
                ];
            }


            $this->sendMail($superadmin, $data);
        }

        if($values['id']){
            $this->flashMessage("Termín byl úspěšně editován.", 'success');
        }else{
            $this->flashMessage("Termín byl úspěšně přidán.", 'success');
        }


        $this->redirect('Administrace:termin');

    }

    /**
     * Odstraní Termín.
     * @param string|null $id id termínu
     * @throws AbortException
     */
    public function actionRemove($id = null)
    {
        $this->terminManager->removeTermin($id);
        $this->flashMessage('Termín byl úspěšně odstraněn.');
        $this->redirect('Administrace:termin');
    }

    /*e
    public function actionRemovePic($id, $pic){
        $this->terminManager->removePic($id, $pic);
      //  $this->redirect('Termin:editace', $id);

    } */

    public function actionEditace($id = null){

            $termin = $this->terminManager->getTermin($id);

           // Debugger::dump($termin);

            if($termin){
                $this['editorTermin']->setDefaults([
                    'id' => $termin['id'],
                    'datum' => $termin['datum']->format('j. n. Y H:i'),
                    'tema' => $termin['tema'],
                    'kde' => $termin['kde'],
                    'mesto_id' => $termin['mesto_id'],
                    'homepage' => $termin['homepage'],
                    'schvaleni' => $termin['schvaleni'],
                    'online' => $termin['online']
                ]);
            }
            $this->template->schvaleni = $termin['schvaleni'];
            $this->template->id = $id;
            $this->template->linkGenerator = $this->linkGenerator->link('Termin:upload',['mesto_id' => $this->userMestoId,'adresar' => 'termin' ]);
     //   Debugger::dump($this->getHttpRequest()->getUrl()->getBasePath());

    }



    /*
    public function actionUpload($mesto_id = null): void
    {
        $fileName = null;
        $httpRequest = $this->getHttpRequest();
        $basePathServer = $this->getHttpRequest()->getUrl()->getBasePath();

        if (!$httpRequest->getFiles()) {
            $this->sendError(ErrorEnum::BAD_REQUEST);
        }

        $fileUpload = current($httpRequest->getFiles());
        if (!$fileUpload->isImage()) {
            $this->sendError(ErrorEnum::INVALID_REQUEST_DATA);
        }

        $timeStamp = new DateTime();
        $fileName = $mesto_id."_".$timeStamp->getTimestamp()."_".$fileUpload->getSanitizedName();

        $fileUpload->toImage()->save(self::FILE_DIR.$fileName, 80, Nette\Utils\Image::JPEG);

        $this->sendJson([
            'location' => $basePathServer.self::FILE_DIR.$fileName,
        ]);
    } */
}