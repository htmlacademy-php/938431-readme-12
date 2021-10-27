<?php
require_once 'vendor/autoload.php';

$transport = new Swift_SmtpTransport('smtp.mailtrap.io', 25);
$transport->setUsername("keks@phpdemo.ru");
$transport->setPassword("htmlacademy");

$mailer = new Swift_Mailer($transport);
