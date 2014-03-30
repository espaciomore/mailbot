<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


abstract class Mailbot 
{
  public static function build( $prototype = 'Silex\Application')
  {
    $app = new $prototype;

    $app['template_engine'] = $app->share(function(){
      $loader = new Twig_Loader_Filesystem(__DIR__.'/../private/templates');
      return new Twig_Environment($loader);
    });

    $app['mail'] = $app->protect(function($to,$sub,$msg){
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
      $headers .= 'From: cs@mailbot.com (MailBot)' . "\r\n";
      return mail( $to, $sub, $msg, $headers );
    });

    $app['css'] = $app->protect(function($filename='style.css'){
      $scripts = array(
          file_get_contents(__DIR__.'/../private/style/bootstrap.min.css'),
          file_get_contents(__DIR__.'/../private/style/'.$filename)
        );
      return implode("\r\n", $scripts);
    });

    $app['js'] = $app->protect(function($filename='client.js'){
      $scripts = array(
          file_get_contents(__DIR__.'/../private/client/jquery-2.0.2-min.js'),
          file_get_contents(__DIR__.'/../private/client/bootstrap.min.js'),
          file_get_contents(__DIR__.'/../private/client/'.$filename)
        );
      return implode("\r\n", $scripts);
    });

    return $app;
  }
}

$app = Mailbot::build();

$app->get('/',function() use($app){
  $css = $app['css'];
  $js = $app['js'];
  $template_engine = $app['template_engine'];
  return $template_engine->render('index.html',array('css'=>$css(),'js'=>$js()));
});

$app->get('/signup',function(Request $req) use($app){
  $rName = $req->get('rName');
  $rEmail = $req->get('rEmail');
  $email = $req->get('email');
  $css = $app['css'];
  $template_engine = $app['template_engine'];
  return $template_engine->render('signup.html',array('rName'=>$rName,'rEmail'=>$rEmail,'email'=>$email,'css'=>$css()));
});

$app->post('/invite',function(Request $req) use($app){
  $rName = $req->get('rName');
  $rEmail = $req->get('rEmail');
  $email = $req->get('email');
  $css = $app['css'];
  $template_engine = $app['template_engine'];
  $mail = $app['mail'];
  $message = $template_engine->render('invitation.html',array('rName'=>$rName,'rEmail'=>$rEmail,'email'=>$email,'css'=>$css()));
  $sent = $mail($email, $rName.' invited you to MailBot', $message,'Content-type: text/html; charset=iso-8859-1' . "\r\n");
  return new Response($sent ? 'OK':'Mailing Problem', $sent ? 201:500);
});

$app->post('/signup',function(Request $req) use($app){
  $email = $req->get('email');
  $name = $req->get('name');
  $css = $app['css'];
  $template_engine = $app['template_engine'];
  $mail = $app['mail'];
  $message = $template_engine->render('welcome.html',array('name'=>$name,'email'=>$email,'css'=>$css()));
  $sent = $mail($email,'Welcome to MailBot', $message);
  return new Response($sent ? 'OK':'Mailing Problem', $sent ? 201:500);
});

$app->run();