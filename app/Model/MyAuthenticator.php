<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Tracy\Debugger;
use Nette\Application\UI\Form;

final class MyAuthenticator implements Nette\Security\IAuthenticator
{
    private $database;
    private $passwords;

    public function __construct(Nette\Database\Context $database, Nette\Security\Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;

     //   Debugger::dump($username);
     //   Debugger::dump($password);

        $row = $this->database->table('user')
            ->where('name', $username)->fetch();

     //   Debugger::dump($row);

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
          //  Debugger::dump('User not found.');
        }

        //if (!$this->passwords->verify($password, $row->password)) {
        if($this->hashPassword($password ) != $row->password )
        {
            throw new Nette\Security\AuthenticationException('Invalid password.');
           // Debugger::dump('Invalid password');
        }

        return new Nette\Security\Identity($row->id, $row->role, ['username' => $row->name]);
    }

    public static function hashPassword($password)
    {
        return md5($password);
    }
}