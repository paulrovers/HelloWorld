<?php

namespace app\controllers;

use core\Controller;

//use Mailer;
//use SpamCheck;

class contact extends Controller
{
    public $strTitle = 'Contact';
    public $strDescription = 'Description';
    public $strPageurl = '';
    public $h1Title = 'Contact';

    public function indexAction()
    {
        if (isset($this->route_params['name'])) {
            $page = (string)$this->route_params['name'];
        } else {
            $page = '';
        }
        $this->strPageurl = $_ENV['APP_URL'] . "/" . $page . "/";

        //has the form been submitted?
        /*
        if(isset($_POST['submit'])){
            $spamcheck = new SpamCheck();

            if(!isset($_SESSION['messageId'])){$_SESSION['messageId'] = '';}

            $fields = array(
                'naam' => 'name',
                'waarnaartoe' => 'email',
                'telefoon' => 'phone',
                'vraag' => 'message',
                'url' => 'empty',
                'email' => 'empty'
            );

            //check values supplied in form and sent email if this is not the case
            if($spamcheck->CheckFormFields($_POST, $fields) != false && count($spamcheck->errors) == 0 && $_SESSION['messageId'] != md5($_POST['waarnaartoe'].$_POST['vraag'])){
                $form = array(
                    'naam' => $_POST['naam'],
                    'email' => $_POST['waarnaartoe'],
                    'telefoon' => $_POST['telefoon'],
                    'vraag' => $_POST['vraag']
                );
                //sent email
                $this->sendmail($form);
                $this->melding = 'Bericht is verzonden';
                $_SESSION['messageId'] = md5($_POST['waarnaartoe'].$_POST['vraag']);
                $_POST = array();
            }elseif(count($spamcheck->errors) != 0){
                //return errors to template
                $this->errors = $spamcheck->errors;
                $this->errorfields = $spamcheck->errorfields;
            }
        }

        //load default template variables
        $array = [
            'melding' => $this->melding,
            'errors' => $this->errors,
            'form' => $_POST,
            'errorfields' => $this->errorfields
        ];*/


        $array = [];
        //load template based on name used
        $this->loadTemplate('contact.twig', $array);
    }


    public function sendmail($form)
    {
        $array = array(
            'form' => $form
        );
        $mailer = new Mailer;
        $mailer->setaddress($_ENV['MAIL_RECEIVER']);
        $mailer->setname($_ENV['MAIL_RECEIVER_NAME']);
        $mailer->setsubject('Contact formulier van Jenda.nl');
        $mailer->sethtml($this->loadEmailTemplate('email/contact.twig', $array));
        $mailer->send();
    }


}




