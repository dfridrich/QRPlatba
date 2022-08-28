<?php

declare(strict_types=1);

/*
 * This file is part of the library "QRPlatba".
 *
 * (c) Dennis Fridrich <fridrich.dennis@gmail.com>
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

use Defr\QRPlatba\QRPlatba;
use Defr\QRPlatba\QRPlatbaException;
use PHPUnit\Framework\TestCase;

class IBANTest extends TestCase
{

    public function testAccountHigherThanMaxInt()
    {
        $string = QRPlatba::accountToIban('2501301193/2010');

        $this->assertSame(
            'CZ3620100000002501301193',
            $string
        );
    }

    public function testPrepareIban()
    {
        $qrplatba = new QRPlatba();

        $this->assertSame(
            'CZ3620100000002501301193',
            $qrplatba->prepareIban('CZ3620100000002501301193')
        );

        $this->assertSame(
            'CZ0708000000001234567987',
            $qrplatba->prepareIban('1234567987/0800')
        );

        $this->assertSame(
            'CZ7020100000002600118167',
            $qrplatba->prepareIban('2600118167/2010')
        );

        $string = QRPlatba::create('CZ0301000000123456789012');
        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*CC:CZK',
            $string->__toString()
        );
    }

    public function testInvalidIban()
    {
        $this->expectException(QRPlatbaException::class);

        $qrplatba = new QRPlatba();
        $qrplatba->prepareIban('CZ36201000000025013011935555');
    }

    public function testAccountTOIBAN()
    {
        self::assertSame(
            'CZ7020100000002600118167',
            QRPlatba::accountToIban('2600118167/2010')
        );
    }

}
