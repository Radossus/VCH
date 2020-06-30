<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\PostManager;
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

final class PostPresenter extends BaseAdminPresenter
{
    private $postManager;
    private $userMestoId;
    const FILE_DIR = 'upload/post/';
    private $linkGenerator;

    public function __construct(PostManager $postManager,Nette\Application\LinkGenerator $generator)
    {
        parent::__construct();
        $this->postManager = $postManager;
        $this->linkGenerator = $generator;
    }

    public function startup()
    {
        parent::startup();
        $this->userMestoId = $this->postManager->getUserMestoId($this->getUser()->getIdentity()->id);
    }

    protected function createComponentEditorPost(): Form
    {
        $form = new Form;
        $form->addGroup('Nový příspěvek');
        $form->addHidden('id');
        $form->addHidden('schvaleni');
     //   $form->addHidden('datum');

        $form->addText('datum', 'Datum: ')
            ->setRequired('Prosím vyplňte datum.');

        $form->addText('nazev', 'Název příspěvku: ')
            ->setRequired('Prosím vyplňte název vašeho příspěvku.');

        $form->addText('autor', 'Jméno autora: ')
            ->setRequired('Prosím vyplňte vaše jméno.');

        $form->addTextArea('obsah' , 'Text příspěvku: ',null,25);

        $form->addSelect('kategorie','Kategorie příspěvku: ')->setItems($this->getKategoriePole());

        $form->addUpload('pic','Zde můžete vložit tématický obrázek, pokud nevložíte váš vlastní, bude nastavený výchozí obrázek.')
            ->addRule(Form::IMAGE, 'Plakát musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 500 kB. Upravte velikost obrázku a znovu jej nahrejte.', 500 * 1024 /* v bytech */);

      //  $form->addText('schvaleni','Příspěvek je ve stavu: ')->setDisabled();

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'novyPostSucceeded'];
        return $form;
    }

    public function novyPostSucceeded(Form $form, array $values): void{

        $filename = null;
        $values['mesto_id'] = $this->userMestoId;
        $values['user'] = $this->getUser()->getIdentity()->username;

        $filename = $this->uploadIntroPic($values['pic']);

        if($filename == null) {
            $filename = 'default_article.jpg';
        }

        $this->postManager->savePost($values,$filename);


        if($values['schvaleni'] == null){
            $superadmin = $this->postManager->getSuperAdmin();
            $data = [
                'akce' => 'nový',
                'co' => 'příspěvek',
            ];
            $this->sendMail($superadmin, $data);
        }
        if($values['schvaleni'] == 'Schváleno'){
            $superadmin = $this->postManager->getSuperAdmin();
            $data = [
                'akce' => 'editace',
                'co' => 'příspěvku',
                'id' => $values['id']
            ];
            $this->sendMail($superadmin, $data);
        }

        if($values['id']){
            $this->flashMessage("Příspěvek byl úspěšně editován.", 'success');
        }else{
            $this->flashMessage("Příspěvek byl úspěšně přidán.", 'success');
        }

        //$this->redirect('Administrace:post');

    }

    public function actionEditace($id = null)
    {
        $post = $this->postManager->getPostById($id);

       // Debugger::dump($id);

        if($post){
            $this['editorPost']->setDefaults([
                'id' => $post['id'],
                'datum' => $post['datum']->format('j. n. Y H:i'),
                'nazev' => $post['nazev'],
                'obsah' => $post['obsah'],
                'mesto_id' => $post['mesto_id'],
                'autor' => $post['autor'],
                'schvaleni' => $post['schvaleni'],
                'pic' => $post['pic'],
                'kategorie' => $post['kategorie']
            ]);
        }
     //   Dumper::dump($post['pic']);
        $this->template->pic = $post['pic'];
        $this->template->kategorie = $this->getKategoriePole();
        $this->template->schvaleni = $post['schvaleni'];
        $this->template->id = $id;
        $this->template->linkGenerator = $this->linkGenerator->link('Post:upload',['mesto_id' => $this->userMestoId,'adresar' => 'post' ]);
    }

    public function actionRemove($id): void
    {
        $this->postManager->removePost($id);
        $this->flashMessage('Příspěvek byl úspěšně odstraněn.');
        $this->redirect('Administrace:post');
    }

    public function getKategoriePole()
    {
        $kategories = ($this->postManager->getKategorie());

        foreach ($kategories as $kategory)
        {
            $kat[$kategory->id] = $kategory->jmeno;
        }

        return $kat;
    }

    public function uploadIntroPic($file)
    {
        $httpRequest = $this->getHttpRequest();
        $basePathServer = $this->getHttpRequest()->getUrl()->getBasePath();

        if($file->isImage() and $file->isOk())
        {

            $timeStamp = new DateTime();
            $fileName = $this->userMestoId . "_" . $timeStamp->getTimestamp() . "_" . $file->getSanitizedName();

            $file->move('upload/post/' . '/intro_pic/'. $fileName);

            $image = \Nette\Utils\Image::fromFile('upload/post/' . '/intro_pic/'. $fileName);

            if($image->getWidth() > $image->getHeight()) {
                $image->resize(390, 260);
            }
            else {
                $image->resize(390, 260);
            }
            $image->sharpen();
            $image->save('upload/post/' . '/intro_pic/'. $fileName);

            return $fileName;
        }

    }

    public function actionRemoveIntroPic($id, $pic)
    {
        $this->postManager->removeIntroPic($id,$pic);
        $this->redirect('Post:editace', $id);
    }

}