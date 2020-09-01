<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\UserManager;
use App\Exceptions;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\DateTime;

final class UzivatelPresenter extends BaseAdminPresenter
{
    /** @var Nette\Database\Context */
    private $userManager;


    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function createComponentInsertUzivatel(): Form
    {
        $form = new Form;
        $form->addGroup('Nový uživatel');
        $form->addHidden('id');

        $form->addText('name', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte jméno uživatele.');

        $form->addPassword('password', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte uživatelské heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků!', 6);

        $form->addPassword('passwordVerify', 'Heslo pro kontrolu:')
            ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSelect('mesto_id', 'Spravované město: ')->setItems($this->getMesta());

        $form->addSelect('role', 'Role uživatele: ')->setItems($this->userManager->getRoles());

        $form->addText('email', 'Email: ');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'editUzivatelSucceeded'];

        return $form;
    }

    public function editUzivatelSucceeded(Form $form, array $values): void
    {
        try{
            $this->userManager->save($values);

            if($values['id']){
                $this->flashMessage("Uživatel byl úspěšně editován.", 'success');
                $this->redirect('Uzivatel:editace', $values['id']);
            }else{
                $this->flashMessage("Uživatel byl úspěšně vložen.", 'success');
                $this->redirect('Administrace:uzivatel');
            }

        }catch (Nette\Database\UniqueConstraintViolationException $e){
            $form->addError("Uživatelské jméno už existuje!");
            return;
        }
    }

    protected function createComponentEditUzivatel(): Form
    {
        $form = new Form;
        $form->addGroup('Editace uživatele');
        $form->addHidden('id');

        $form->addText('name', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte jméno uživatele.');

        $form->addSelect('mesto_id', 'Spravované město: ')->setItems($this->getMesta());

        $form->addSelect('role', 'Role uživatele: ')->setItems($this->userManager->getRoles());

        $form->addText('email', 'Email: ');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'editUzivatelSucceeded'];

        return $form;
    }

    protected function createComponentZmenaHesla(): Form
    {
        $form = new Form;
        $form->addGroup('Editace uživatele');
        $form->addHidden('id');
        $form->addPassword('password', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte uživatelské heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků!', 6);

        $form->addPassword('passwordVerify', 'Heslo pro kontrolu: ')
            ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'zmenaHeslaSucceeded'];

        return $form;

    }

    public function zmenaHeslaSucceeded(Form $form, array $values): void
    {
        $this->userManager->changePassword($values);
        $this->flashMessage('Heslo bylo změněno.');
    }

    public function actionRemove($id)
    {
        $this->userManager->removeUser($id);
        $this->flashMessage('Uživatel byl úspěšně odstraněn.');
        $this->redirect('Administrace:uzivatel');
    }


    public function actionEditace($id = null)
    {
        $user = $this->userManager->getUser($id);

        if($user){
            $this['editUzivatel']->setDefaults([
                'id' => $user['id'],
                'name' => $user['name'],
                'mesto_id' => $user['mesto_id'],
                'role' => $user['role'],
                'mesto_id' => $user['mesto_id'],
                'email' => $user['email']
            ]);
        }
        $this->template->name = $user['name'];
        $this->template->id = $id;
    }


    public function getMesta()
    {
        $mesta = $this->userManager->getMesta();

        foreach ($mesta as $mesto)
        {
            $ms[$mesto->id] = $mesto->mesto;
        }

        return $ms;
    }

}