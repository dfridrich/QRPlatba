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
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class QRPlatbaTest extends TestCase
{
    public function testFakeCurrencyString()
    {
        self::expectException(InvalidArgumentException::class);

        QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('FAKE');
    }

    public function testCzkString()
    {
        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setMessage('Düakrítičs');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('CZK');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testEurString()
    {
        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('EUR');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:EUR*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testIBAN()
    {
        $string = QRPlatba::create('CZ6508000000192000145399', 1234.56, '2016001234');

        $this->assertSame(
            'SPD*1.0*ACC:CZ6508000000192000145399*AM:1234.56*CC:CZK*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRPlatba::create('CZ6508000000192000145399', 1234.56, '2016001234');
        $string->setIBAN('CZ6508000000192000145399');

        $this->assertSame(
            'SPD*1.0*ACC:CZ6508000000192000145399*AM:1234.56*CC:CZK*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testQrCodeInstance()
    {
        $qrPlatba = QRPlatba::create('12-3456789012/0100', 987.60)
            ->setMessage('QR platba je parádní!')
            ->getQRCodeInstance();

        $this->assertInstanceOf(QrCode::class, $qrPlatba);
    }

    public function testRecipientName()
    {
        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setRecipientName('Düakrítičs');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*RN:Duakritics',
            $string->__toString()
        );
    }

    public function testConstantSymbolString()
    {
        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->setConstantSymbol('0008');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*X-KS:0008',
            $string->__toString()
        );
    }

    public function testAlternativeAccount()
    {
        $string = QRPlatba::create('12-3456789012/0100', 1234.56, '2016001234')
            ->addAlternativeAccount('3456789012/0300');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*ALT-ACC:CZ1503000000003456789012*AM:1234.56*CC:CZK*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRPlatba::create('12-3456789012/0100')
            ->addAlternativeAccount('3456789012/0300')
            ->addAlternativeAccount('1234567987/0800');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*ALT-ACC:CZ1503000000003456789012,CZ0708000000001234567987*CC:CZK',
            $string->__toString()
        );
    }


    public function testInvalidAccount()
    {
        $this->expectException(QRPlatbaException::class);

        $qrplatba = new QRPlatba();
        $qrplatba->prepareIban('12345679870800');
    }

}
