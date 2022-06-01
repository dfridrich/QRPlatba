<?php

/*
 * This file is part of the library "QRPlatba".
 *
 * (c) Swejzi
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

use PHPUnit\Framework\TestCase;
use Swejzi\QRPlatba\QRPlatba;

/**
 * Class QRPlatbaTest.
 */
class QRPlatbaTest extends TestCase
{
    public function testFakeCurrencyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('FAKE');
    }

    public function testCzkString(): void
    {
        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs');

        $this->assertSame(
            'SPD*1.1*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('CZK');

        $this->assertSame(
            'SPD*1.1*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testEurString(): void
    {
        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('EUR');

        $this->assertSame(
            'SPD*1.1*ACC:CZ0301000000123456789012*AM:1234.56*CC:EUR*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testRecipientName(): void
    {
        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setRecipientName('Düakrítičs');

        $this->assertSame(
            'SPD*1.1*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*RN:Duakritics',
            $string->__toString()
        );
    }
}
