<?php

require __DIR__ . '/vendor/autoload.php';

use Defr\QRPlatba\QRPlatba;

$qrPlatba = new QRPlatba();

$qrPlatba->setIBAN('SK3112000000198742637541') // nastavení č. účtu
    ->setVariableSymbol('2016001234')
    ->setMessage('Toto je první QR platba.')
    ->setConstantSymbol('0308')
    ->setSpecificSymbol('1234')
    ->setAmount('1234.56')
    ->setCurrency('EUR') // Výchozí je CZK, lze zadat jakýkoli ISO kód měny
    ->setDueDate(new \DateTime());

echo $qrPlatba->getQRCodeImage();
