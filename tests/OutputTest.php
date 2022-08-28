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

class OutputTest extends TestCase
{
    /**
     * @dataProvider provideFormats
     */
    public function testQrPlatbaFormats(string $format, string $fileName, QRPlatba $qrPlatba)
    {
        $path = __DIR__."/artifacts/$fileName";
        @unlink($fileName);
        $qrPlatba->saveQRCodeImage($path, $format);
        self::assertFileExists($path);
    }

    public function provideFormats()
    {
        return [
            'QR Platba (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba.png',
                'qrPlatba' => $this->givenQrPlatbaObject(),
            ],
            'QR Platba v EUR (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba_eur.png',
                'qrPlatba' => $this->givenQrPlatbaObject()->setCurrency('EUR'),
            ],
            'QR Platba (SVG)' => [
                'format' => QRPlatba::FORMAT_SVG,
                'fileName' => 'qr_platba.svg',
                'qrPlatba' => $this->givenQrPlatbaObject(),
            ],
            'QR Platba a popisek (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba_popisek.png',
                'qrPlatba' => $this->givenQrPlatbaObject()->setLabel('QR Platba'),
            ],
            'QR Platba a popisek v EUR (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba_popisek_eur.png',
                'qrPlatba' => $this->givenQrPlatbaObject()->setCurrency('EUR')->setLabel('QR Platba v EUR'),
            ],
            'QR Platba a popisek (SVG)' => [
                'format' => QRPlatba::FORMAT_SVG,
                'fileName' => 'qr_platba_popisek.svg',
                'qrPlatba' => $this->givenQrPlatbaObject()->setLabel('QR Platba'),
            ],
            'QR Platba+F a popisek (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba_a_faktura_popisek.png',
                'qrPlatba' => $this->givenQrPlatbaWithInvoiceObject()->setLabel('QR Platba+F'),
            ],
            'QR Platba+F a popisek v EUR (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_platba_a_faktura_popisek_eur.png',
                'qrPlatba' => $this->givenQrPlatbaWithInvoiceObject()->setLabel('QR Platba+F')->setCurrency('EUR'),
            ],
            'QR Platba+F a popisek (SVG)' => [
                'format' => QRPlatba::FORMAT_SVG,
                'fileName' => 'qr_platba_a_faktura_popisek.svg',
                'qrPlatba' => $this->givenQrPlatbaWithInvoiceObject()->setLabel('QR Platba+F'),
            ],
            'QR Faktura (PNG)' => [
                'format' => QRPlatba::FORMAT_PNG,
                'fileName' => 'qr_faktura.png',
                'qrPlatba' => $this->givenQrPlatbaWithInvoiceObject()->setIsOnlyInvoice(true)->setLabel('QR Faktura (bez platby)'),
            ],
        ];
    }

    private function givenQrPlatbaObject(): QRPlatba
    {
        return QRPlatba::create('1234/0100', 123.45, '1234567890')
            ->setMessage('Předplatné FlixNet');
    }

    private function givenQrPlatbaWithInvoiceObject(): QRPlatba
    {
        return QRPlatba::create('1234/0100', 2410.00, '1234567890')
            ->setInvoiceId('FAKT1234')
            ->setInvoiceDate(new DateTime('yesterday'))
            ->setTaxPerformance(1)
            ->setInvoiceDocumentType(5)
            ->setInvoiceIncludingDeposit(false)
            ->setInvoiceRelatedId('OBJ1234')
            ->setCompanyTaxId('CZ8508095453')
            ->setCompanyRegistrationId('73263753')
            ->setInvoiceSubjectTaxId('CZ04491254')
            ->setInvoiceSubjectTaxId('04491254')
            ->setTaxDate(new DateTime('yesterday'))
            ->setTaxReportDate(new DateTime('yesterday'))
            ->setTaxBase(1000.0, 0)
            ->setTaxAmount(210.0, 0)
            ->setTaxBase(1000.0, 1)
            ->setTaxAmount(100.0, 1)
            ->setNoTaxAmount(100.0)
            ->setTaxSoftware('SuperÚčto')
            ->setConstantSymbol('0308')
            ->setDueDate(new DateTime('tomorrow'))
            ->setMessage('Předplatné FlixNet');
    }

}
