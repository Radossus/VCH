<?php


namespace App\Model;


use Nette;
use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use App\Exceptions;

/**
 * Model pro správu uživatelů
 * @package App\Model
 */
class UserManager extends DatabaseManager
{
    /** Konstanty pro práci s databází. */
    const
        TABLE_NAME = 'user',
        COLUMN_ID = 'id',
        COLUMN_MESTO_ID = 'mesto_id';

    public function __construct(Context $database)
    {
        parent::__construct($database);
    }


    public function getSuperAdmin()
    {
      //  Debugger::dump($this->database->table(self::TABLE_NAME)->fetchAll());
        return $this->database->table(self::TABLE_NAME)->fetchAll();
    }

    public function save($user)
    {
        $insert = null;

        if (empty($user[self::COLUMN_ID])) {
            unset($user[self::COLUMN_ID]);

            $password = self::hashPassword($user['password']);

            try{
                $insert =  $this->database->table(self::TABLE_NAME)->insert([
                    'name' => $user['name'],
                    'password' => $password,
                    'mesto_id' => $user['mesto_id'],
                    'role' => $user['role'],
                    'email' => $user['email'],
                ]);
            }catch (Nette\Database\UniqueConstraintViolationException $e){
                throw new Nette\Database\UniqueConstraintViolationException();
            }

        }
        else{
            try{
                $update =  $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $user[self::COLUMN_ID])->update([
                    'name' => $user['name'],
                    'mesto_id' => $user['mesto_id'],
                    'role' => $user['role'],
                    'email' => $user['email'],
                ]);

            }catch (Nette\Database\UniqueConstraintViolationException $e){
                throw new Nette\Database\UniqueConstraintViolationException();
            }

        }
        return $insert;
    }

    public function getUser($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    public function getUsers()
    {
        return $this->database->table('user')->order('name')->fetchAll();
    }

    public function getMesta()
    {
        return $this->database->table('mesto')->order('mesto')->fetchAll();
    }
    public function getRoles()
    {
        $role =  [
            'spravce' => 'spravce',
            'superadmin' => 'superadmin'
        ];

        return $role;
    }

    public function removeUser($id)
    {
        $this->database->table(self::TABLE_NAME)->where('id',$id)->delete();
    }

    public function changePassword($data)
    {
        $update =  $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $data[self::COLUMN_ID])->update([
            'password' => self::hashPassword($data['password']),
        ]);
    }

    public static function hashPassword($password)
    {
        return md5($password);
    }

}