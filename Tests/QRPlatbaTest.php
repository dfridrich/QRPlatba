<?php

/*
 * This file is part of the library "QRPlatba".
 *
 * (c) Dennis Fridrich <fridrich.dennis@gmail.com>
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

use Defr\QRPlatba\QRPlatba;

/**
 * Class QRPlatbaTest.
 */
class QRPlatbaTest extends \PHPUnit\Framework\TestCase
{

    public function testFakeCurrencyString()
    {
		$this->expectException(\InvalidArgumentException::class);

        QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('FAKE');
    }

    public function testCzkString()
    {
        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('CZK');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testEurString()
    {
        $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('EUR');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:EUR*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testQrCodeInstante()
    {
        $qrPlatba = QRPlatba::create('12-3456789012/0100', 987.60)
            ->setMessage('QR platba je parádní!')
            ->getQRCodeInstance();

        $this->assertInstanceOf('Endroid\\QrCode\\QrCode', $qrPlatba);
    }

    public function testRecipientName()
    {
	    $string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
		    ->setRecipientName('Düakrítičs');

	    $this->assertSame(
		    'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*RN:Duakritics',
		    $string->__toString()
	    );
    }

	public function testConstantSymbolString()
	{
		$string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
			->setConstantSymbol('0008');

		$this->assertSame(
			'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*X-KS:0008',
			$string->__toString()
		);
	}

	public function testAlternativeAccount()
	{
		$string = QRPlatba::create('12-3456789012/0100', '1234.56', '2016001234')
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
}
