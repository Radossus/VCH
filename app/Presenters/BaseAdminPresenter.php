<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Utils\DateTime;
use Nette;
use Nette\Mail\Message;
use App\Model\UserManager;
use Tracy\Debugger;
use Tracy\Dumper;
use Nette\Application\LinkGenerator;

abstract class BaseAdminPresenter extends Presenter
{

    const
        FILE_DIR = 'upload/';

    /**
     * @var \Nette\Mail\IMailer @inject
     */
    public $mailer;

    public function __construct()
    {
      //  $baba = $this->userManager->getSuperAdmin();
       // Debugger::dump();
    }

    protected function startup()
    {
        parent::startup();
        $this->setLayout('admin');
        if (!$this->getUser()->loggedIn) {
            $this->redirect('Prihlaseni:in');
        }
        //  $this->userIdentity = $this->getUser()->getIdentity();
        $this->template->userIdentity = $this->getUser()->getIdentity();
    }

    public function actionUpload($mesto_id = null, $adresar): void
    {
        $fileName = null;
        $httpRequest = $this->getHttpRequest();
        $basePathServer = $this->getHttpRequest()->getUrl()->getBasePath();

        if (!$httpRequest->getFiles()) {
            $this->sendError(ErrorEnum::BAD_REQUEST);
        }

        $fileUpload = current($httpRequest->getFiles());
        if (!$fileUpload->isImage()) {
            $this->sendError(Error::INVALID_REQUEST_DATA);
        }

        $timeStamp = new DateTime();
        $fileName = $mesto_id . "_" . $timeStamp->getTimestamp() . "_" . $fileUpload->getSanitizedName();

        $fileUpload->toImage()->save(self::FILE_DIR . $adresar . "/" . $fileName, 80, Nette\Utils\Image::JPEG);

        $this->sendJson([
            'location' => $basePathServer . self::FILE_DIR . $adresar . "/" . $fileName,
        ]);
    }

    public function sendMail($recepients, $data)
    {
        $mail = new Message;

        foreach ($recepients as $recepient){

            if(!isset($data['id']))
            {
                $mail->setFrom('Web večery chval <info@vecerychval.cz>')
                    ->addTo($recepient->email)
                    ->setSubject($data['akce'].' '.$data['co']. ' ke schválení na webu večery chval')
                    ->setHtmlBody('
                        <p>Dobrý den,</p>
                        <p>na webu večery chval čeká na schválení '.$data["akce"].' '.$data["co"].'</p>
                        <p>Přihlašte se do administrace http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$this->getHttpRequest()->getUrl()->getBasePath().'administrace </p>
                        <p>Odeslal web vecerychval.cz</p>');
            }
            if(isset($data['id'])){
                $mail->setFrom('Web večery chval <info@vecerychval.cz>')
                    ->addTo($recepient->email)
                    ->setSubject($data['akce'].' u již schváleného příspěvku.')
                    ->setHtmlBody('
                        <p>Dobrý den,</p>
                        <p>na webu večery chval uživatel editoval již schválěný příspěvek</p>
                        <p>Přihlašte se do administrace a prohlédněte si tento příspěvek http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$this->getHttpRequest()->getUrl()->getBasePath().'prehled/post-detail/'.$data['id'].' </p>
                        <p>Odeslal web vecerychval.cz</p>');
            }
        }
       // Debugger::dump($this->getHttpRequest()->getUrl()->getBasePath());
        $this->mailer->send($mail);
    }
}