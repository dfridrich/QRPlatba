# QR Platba

[![Build Status](https://travis-ci.org/dfridrich/QRPlatba.svg)](https://travis-ci.org/dfridrich/QRPlatba)

Knihovna pro generování QR plateb v PHP.

## Instalace pomocí Composeru

`composer require dfridrich/qrplatba:~1.0`

## Příklad

```php
<?php

require "vendor/autoload.php";

use Defr\QRPlatba\QRPlatba;

$qrPlatba = new QRPlatba();

$qrPlatba->setAccount("12-3456789012/0100")
    ->setVariableSymbol("2016001234")
    ->setMessage("Toto je první QR platba.")
    ->setSpecificSymbol("0308")
    ->setSpecificSymbol("1234")
    ->setDueDate(new \DateTime());

echo $qrPlatba->getQRCodeImage();

// nebo...

echo QRPlatba::create("12-3456789012/0100", 987.60)->setMessage("QR platba je parádní!")->getQRCodeImage();
```

## Odkazy

- Oficiálí web QR Platby - http://qr-platba.cz/
- Repozitář, který mě inspiroval - https://github.com/snoblucha/QRPlatba

## Contributing

Budu rád za každý návrh na vylepšení :-)