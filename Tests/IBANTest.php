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
class IBANTest extends TestCase
{

    public function testAccountHigherThanMaxInt(): void
    {
        $string = QRPlatba::accountToIban('2501301193/2010');

        $this->assertSame('CZ3620100000002501301193', $string);
    }

}
