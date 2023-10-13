<?php

namespace app\Models;

use core\Model;

class Users extends Model
{

    /**
     * Check if it's a valid user
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function verify_login(string $email, string $password):bool
    {

        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $array = ['email' => $email];
        $result = $this->dbQuery($query,$array);

        if (count($result) == 1){
            echo 'koekoek';
            print_r($result[0]['password']);
            if(password_verify($password,$result[0]['password'])) {
                return true;
            }
        }
        return false;
    }


    /**
     * get record from database by searching for id
     * @param int $id // id
     * @return mixed
     */
    public function get_user(int $id):array
    {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $array = ['id' => $id];
        $result = $this->dbQuery($query,$array);
        return $result[0];
    }

    /**
     * get record from database by searching for email
     * @param string $email // email
     * @return mixed
     */
    public function get_user_by_email(string $email):array
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $array = ['email' => $email];
        $result = $this->dbQuery($query,$array);
        return $result[0];
    }

    /**
     * @param array $form //form with fields: id, name, email
     * @return bool
     */
    public function save(array $form):bool
    {
        $query = "UPDATE users SET `name` = :name, `email` = :email  WHERE id = :id";
        $array = [
            'id' => $form['id'],
            'name' => $form['name'],
            'email' => $form['email']
        ];
        $this->dbQuery($query,$array);
        return true;
    }




}