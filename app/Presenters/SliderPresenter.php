<?php

declare(strict_types=1);

namespace App\Presenters;
use App\Model\PostManager;
use App\Model\SliderManager;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\Utils\Image;
use Tracy\Debugger;

final class SliderPresenter extends BaseAdminPresenter
{
    private $sliderManager;
    const FILE_DIR = 'upload/slider/';

    public function __construct(SliderManager $sliderManager)
    {
        parent::__construct();
        $this->sliderManager = $sliderManager;
    }

    public function startup()
    {
        parent::startup();
       // $this->userMestoId = $this->postManager->getUserMestoId($this->getUser()->getIdentity()->id);
    }

    protected function createComponentEditorSlider(): Form
    {
        $form = new Form;
        $form->addGroup('Nový obrázek');
        $form->addHidden('id');
        $form->addText('popisek', 'Popis obrázku, jen jako poznámka: ');
        $form->addText('url', 'Pořadí obrázků: ');
        $form->addText('poradi', 'Pořadí obrázků: ');
        $form->addCheckbox('zobrazit', ' Zobrazit obrazek ve slideru')->setDefaultValue(true);
        $form->addUpload('pic','Zde můžete vložit tématický obrázek, pokud nevložíte váš vlastní, bude nastavený výchozí obrázek.')
            ->addRule(Form::IMAGE, 'Plakát musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 500 kB. Upravte velikost obrázku a znovu jej nahrejte.', 500 * 1024 /* v bytech */);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'novySliderSucceeded'];
        return $form;
    }

    public function novySliderSucceeded(Form $form, array $values): void
    {
        $filename = null;
        $upload = $values['pic'];

        if($upload->isOk()){
            $filename = $this->uploadPic($values['pic']);
        }else{
            $row = $this->sliderManager->getItem($values['id']);
            $filename = $row['pic'];
        }

        
        $this->sliderManager->savePost($values,$filename);

        if($values['id']){
            $this->flashMessage("Obrázek byl úspěšně editován.", 'success');
            $this->redirect('Slider:editace',$values['id']);
        }else{
            $this->flashMessage("Obrázek byl úspěšně přidán.", 'success');
            $this->redirect('Administrace:slider');
        }
    }

    public function actionEditace($id = null)
    {
        $row = $this->sliderManager->getItem($id);

        if($row)
        {
            $this['editorSlider']->setDefaults([
                'id' => $row['id'],
                'popisek' => $row['popisek'],
                'url' => $row['url'],
                'poradi' => $row['poradi'],
                'zobrazit' => $row['zobrazit'],
                'pic' => $row['pic']
            ]);
        }

        $this->template->id = $id;
        $this->template->pic = $row['pic'];
    }

    public function actionRemovePic($id, $pic)
    {
        $this->sliderManager->removePic($id,$pic);
        $this->redirect('Slider:editace', $id);
    }

    public function getOrderItem()
    {
        $items = $this->sliderManager->getItemOrder();

        foreach ($items as $item)
        {
            $order[$item->id] = $item->poradi;
        }

        return $order;
    }



    public function actionRemove($id = null)
    {
        $this->sliderManager->removeSliderItem($id);
        $this->flashMessage('Stránka byla úspěšně odstraněna.');
        $this->redirect('Administrace:slider');
    }

    public function uploadPic($file)
    {
        $httpRequest = $this->getHttpRequest();
        $basePathServer = $this->getHttpRequest()->getUrl()->getBasePath();

        if ($file->isImage() and $file->isOk()) {

            $timeStamp = new DateTime();
            $fileName = $timeStamp->getTimestamp() . "_" . $file->getSanitizedName();

            $file->move('upload/slider/' . $fileName);

            $image = \Nette\Utils\Image::fromFile('upload/slider/' . $fileName);

            $image->resize(845, 480, Image::STRETCH);

            $image->sharpen();
            $image->save('upload/slider/' . $fileName);

            return $fileName;
        }
    }
}