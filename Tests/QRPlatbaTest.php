<?php

use Defr\QRPlatba\QRPlatba;
use Endroid\QrCode\QrCode;

/**
 * Class QRPlatbaTest
 */
class QRPlatbaTest extends PHPUnit_Framework_TestCase
{
    public function testCzkString()
    {
        $string = QRPlatba::create("12-3456789012/0100", "1234.56", "2016001234")
            ->setMessage("Düakrítičs");

        $this->assertEquals(
            "SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234",
            $string
        );

        $string = QRPlatba::create("12-3456789012/0100", "1234.56", "2016001234")
            ->setMessage("Düakrítičs")
            ->setCurrency("CZK");

        $this->assertEquals(
            "SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234",
            $string
        );
    }

    public function testEurString()
    {
        $string = QRPlatba::create("12-3456789012/0100", "1234.56", "2016001234")
            ->setMessage("Düakrítičs")
            ->setCurrency("EUR");

        $this->assertEquals(
            "SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:EUR*MSG:Duakritics*X-VS:2016001234",
            $string
        );
    }

    public function testQrCodeInstante()
    {
        $qrPlatba = QRPlatba::create("12-3456789012/0100", 987.60)
            ->setMessage("QR platba je parádní!")
            ->getQRCodeInstance();

        $this->assertInstanceOf("Endroid\\QrCode\\QrCode", $qrPlatba);
    }
}
