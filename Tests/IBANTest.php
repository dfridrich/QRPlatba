<?php

require __DIR__ .  DIRECTORY_SEPARATOR . 'bootstrap.php';

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
class IBANTest extends \PHPUnit\Framework\TestCase
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
		$qrplatba = new Defr\QRPlatba\QRPlatba();

		$this->assertSame(
			'CZ3620100000002501301193',
			$qrplatba->prepareIban('CZ3620100000002501301193')
		);

		$this->assertSame(
			'CZ0708000000001234567987',
			$qrplatba->prepareIban('1234567987/0800')
		);


		$string = QRPlatba::create('CZ0301000000123456789012');
		$this->assertSame(
			'SPD*1.0*ACC:CZ0301000000123456789012*CC:CZK',
			$string->__toString()
		);

	}


	public function testInvalidIban()
	{
		$this->expectException(\Defr\QRPlatba\QRPlatbaException::class);

		$qrplatba = new Defr\QRPlatba\QRPlatba();
		$qrplatba->prepareIban('CZ36201000000025013011935555');
	}


}
