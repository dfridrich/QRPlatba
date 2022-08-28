<?php

require __DIR__ . '/../vendor/autoload.php';

use Defr\QRPlatba\QRPlatba;

$qrPlatba = new QRPlatba();

$qrPlatba->setAccount('12-3456789012/0100')
    ->setVariableSymbol('2016001234')
    ->setMessage('Toto je první QR platba.')
    ->setConstantSymbol('0308')
    ->setSpecificSymbol('1234')
    ->setAmount(1234.56)
    ->setCurrency('CZK') // Výchozí je CZK, lze zadat jakýkoli ISO kód měny
    ->setDueDate(new \DateTime());

echo $qrPlatba->getQRCodeImage(); // Zobrazí <img> tag s kódem, viz níže
