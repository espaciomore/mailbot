<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// start this application
$app = new Silex\Application();

// templating system
$loader = new Twig_Loader_Filesystem(__DIR__.'/../private/templates');
$twig = new Twig_Environment($loader);

// user functions
$send_mail = function($to,$sub,$msg){
  // To send HTML mail, the Content-type header must be set
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
  $headers .= 'From: cs@mailbot.com (MailBot)' . "\r\n";

  return mail( $to, $sub, $msg, $headers );
};

$app->get('/',function() use($app,$twig){
  $css = file_get_contents('style.css');
  return $twig->render('index.html',array('style'=>$css));
});

$app->get('/signup',function(Request $req) use($app,$twig){
  $rName = $req->get('rName');
  $rEmail = $req->get('rEmail');
  $email = $req->get('email');
  $css = file_get_contents('style.css');
  return $twig->render('signup.html',array('rName'=>$rName,'rEmail'=>$rEmail,'email'=>$email,'style'=>$css));
});

$app->post('/invite',function(Request $req) use($app,$twig,$send_mail){
  $rName = $req->get('rName');
  $rEmail = $req->get('rEmail');
  $email = $req->get('email');
  $css = file_get_contents('style.css');
  $message = $twig->render('invitation.html',array('rName'=>$rName,'rEmail'=>$rEmail,'email'=>$email,'style'=>$css));
  $sent = $send_mail($email, $rName.' invited you to MailBot', $message,'Content-type: text/html; charset=iso-8859-1' . "\r\n");
  return new Response($sent ? 'OK':'Mailing Problem', $sent ? 201:500);
});

$app->post('/signup',function(Request $req) use($app,$twig,$send_mail){
  $email = $req->get('email');
  $name = $req->get('name');
  $css = file_get_contents('style.css');
  $message = $twig->render('welcome.html',array('name'=>$name,'email'=>$email,'style'=>$css));
  $sent = $send_mail($email,'Welcome to MailBot', $message);
  return new Response($sent ? 'OK':'Mailing Problem', $sent ? 201:500);
});

$app->run();