<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\UserManager;
use App\Exceptions;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\DateTime;

final class UserProfilePresenter extends BaseAdminPresenter
{
    /** @var Nette\Database\Context */
    private $userManager;
    private $userIdentity;
    private $modalPasswordShow;


    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function startup()
    {
        parent::startup();
        $this->userIdentity = $this->getUser()->getIdentity();
        $this->modalPasswordShow = false;
    }

    public function renderDefault()
    {
        $this->template->id = $this->userIdentity->id;
        $this->template->modalStatus = $this->modalPasswordShow;
    }

    protected function createComponentEditUzivatel(): Form
    {
        $form = new Form;
        $form->addGroup('Profil uživatele');
        $form->addHidden('id')->setDefaultValue($this->getUserDetail()->id);

        $form->addText('name', 'Jméno uživatele: ')->setDefaultValue($this->getUserDetail()->name);
            
        $form->addText('mesto', 'Spravované město: ')->setDefaultValue($this->getUserDetail()->mesto->mesto);

        $form->addText('role', 'Role uživatele: ')->setDefaultValue($this->getUserDetail()->role);

        $form->addText('email', 'Kontaktní email: ')->setDefaultValue($this->getUserDetail()->email);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'editUzivatelSucceeded'];

        return $form;
    }

    public function editUzivatelSucceeded(Form $form, array $values): void
    {
        $this->userManager->saveUserProfile($values);
        $this->flashMessage("Změny byly uloženy.", 'success');
        $this->redirect('UserProfile:');
    }

    public function getUserDetail()
    {
        return $this->userManager->getUser($this->userIdentity->id);
    }

    protected function createComponentZmenaHesla(): Form
    {
        $form = new Form;
        $form->addGroup('Editace uživatele');
        $form->addHidden('id');

        $form->addPassword('currentpassword', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte původní uživatelské heslo.');

        $form->addPassword('newpassword', 'Jméno uživatele: ')
            ->setRequired('Prosím vyplňte nové uživatelské heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků!', 6);

        $form->addPassword('newpasswordVerify', 'Heslo pro kontrolu: ')
            ->setRequired('Zadejte prosím nové heslo ještě jednou pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['newpassword']);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'zmenaHeslaSucceeded'];

        return $form;

    }

    public function zmenaHeslaSucceeded(Form $form, array $values): void
    {
        $passwordStatus = true;
        $passwordStatus = $this->userManager->checkPassword($this->userIdentity->id,$values['currentpassword']);

        if(!$passwordStatus){
            $form->addError("Původní heslo neodpovídá!");
            $this->modalPasswordShow = true;
        }else{
            $data['id'] = $this->userIdentity->id;
            $data['password'] = $values['newpassword'];
            $this->userManager->changePassword($data);
            $this->flashMessage('Heslo bylo změněno.');
            $this->redirect('UserProfile:');
        }
    }

}