<?php

/*
 * This file is part of the library "QRPlatba".
 *
 * (c) Dennis Fridrich <fridrich.dennis@gmail.com>
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

namespace Swejzi\QRPlatba;

use BaconQrCode\Exception\WriterException;
use DateTime;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\BinaryWriter;
use Endroid\QrCode\Writer\DebugWriter;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use InvalidArgumentException;

/**
 * Knihovna pro generování QR plateb v PHP.
 *
 * @see https://raw.githubusercontent.com/snoblucha/QRPlatba/master/QRPlatba.php
 */
class QRPlatba
{
    /**
     * Verze QR formátu QR Platby.
     */
    public const VERSION = '1.1';

    public const FORMAT_BINARY = 'binary';
    public const FORMAT_DEBUG = 'debug';
    public const FORMAT_ESP = 'esp';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PNG = 'png';
    public const FORMAT_SVG = 'svg';

    /**
     * @var array
     */
    private static $currencies = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL',
        'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY',
        'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD',
        'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS',
        'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF',
        'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD',
        'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT',
        'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD',
        'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN',
        'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK',
        'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR',
        'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SPL', 'SRD',
        'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY',
        'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF',
        'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR',
        'ZMW', 'ZWD',
    ];

    /**
     * @var array
     */
    private $keys = [
        'ACC' => null,
        // Max. 46 - znaků IBAN, BIC Identifikace protistrany !povinny
        'ALT-ACC' => null,
        // Max. 93 - znaků Seznam alternativnich uctu. odddeleny carkou,
        'AM' => null,
        //Max. 10 znaků - Desetinné číslo Výše částky platby.
        'CC' => 'CZK',
        // Právě 3 znaky - Měna platby.
        'DT' => null,
        // Právě 8 znaků - Datum splatnosti YYYYMMDD.
        'MSG' => null,
        // Max. 60 znaků - Zpráva pro příjemce.
        'X-VS' => null,
        // Max. 10 znaků - Celé číslo - Variabilní symbol
        'X-SS' => null,
        // Max. 10 znaků - Celé číslo - Specifický symbol
        'X-KS' => null,
        // Max. 10 znaků - Celé číslo - Konstantní symbol
        'RF' => null,
        // Max. 16 znaků - Identifikátor platby pro příjemce.
        'RN' => null,
        // Max. 35 znaků - Jméno příjemce.
        'PT' => null,
        // Právě 3 znaky - Typ platby.
        'CRC32' => null,
        // Právě 8 znaků - Kontrolní součet - HEX.
        'NT' => null,
        // Právě 1 znak P|E - Identifikace kanálu pro zaslání notifikace výstavci platby.
        'NTA' => null,
        //Max. 320 znaků - Telefonní číslo v mezinárodním nebo lokálním vyjádření nebo E-mailová adresa
        'X-PER' => null,
        // Max. 2 znaky -  Celé číslo - Počet dní, po které se má provádět pokus o opětovné provedení neúspěšné platby
        'X-ID' => null,
        // Max. 20 znaků. -  Identifikátor platby na straně příkazce. Jedná se o interní ID, jehož použití a interpretace závisí na bance příkazce.
        'X-URL' => null,
        // Max. 140 znaků. -  URL, které je možno využít pro vlastní potřebu
    ];

    /**
     * Kontruktor nové platby.
     *
     * @param null|string $account
     * @param null|string $amount
     * @param null|string $variable
     * @param null|string $currency
     * @throws \InvalidArgumentException
     */
    public function __construct(?string $account = null, ?string $amount = null, ?string $variable = null, ?string $currency = null)
    {
        if ($account) {
            $this->setAccount($account);
        }
        if ($amount) {
            $this->setAmount($amount);
        }
        if ($variable) {
            $this->setVariableSymbol($variable);
        }
        if ($currency) {
            $this->setCurrency($currency);
        }
    }

    /**
     * Nastavení čísla účtu ve formátu 12-3456789012/0100.
     *
     * @param string $account
     *
     * @return $this
     */
    public function setAccount(string $account): self
    {
        $this->keys['ACC'] = self::accountToIban($account);

        return $this;
    }

    /**
     * Převedení čísla účtu na formát IBAN.
     *
     * @param string $accountNumber
     *
     * @return string
     */
    public static function accountToIban(string $accountNumber): string
    {
        $accountNumberParts = explode('/', $accountNumber);
        $bank = $accountNumberParts[1];
        $pre = 0;
        if (false === mb_strpos($accountNumberParts[0], '-')) {
            $acc = $accountNumberParts[0];
        } else {
            [$pre, $acc] = explode('-', $accountNumberParts[0]);
        }

        $accountPart = sprintf('%06d%010s', $pre, $acc);
        $iban = 'CZ00' . $bank . $accountPart;

        $alfa = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
        $alfa = explode(' ', $alfa);
        $alfa_replace = [];
        for ($i = 1; $i < 27; ++$i) {
            $alfa_replace[] = $i + 9;
        }
        $controlegetal = str_replace(
            $alfa,
            $alfa_replace,
            mb_substr($iban, 4, mb_strlen($iban) - 4) . mb_substr($iban, 0, 2) . '00'
        );
        $controlegetal = 98 - (int)bcmod($controlegetal, 97);

        return sprintf('CZ%02d%04d%06d%010s', $controlegetal, $bank, $pre, $acc);
    }

    /**
     * Nastavení částky.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount(float $amount): self
    {
        $this->keys['AM'] = sprintf('%.2f', $amount);

        return $this;
    }

    /**
     * Nastavení variabilního symbolu.
     *
     * @param string $vs
     *
     * @return $this
     */
    public function setVariableSymbol(string $vs): self
    {
        $this->keys['X-VS'] = $vs;

        return $this;
    }

    /**
     * @param string $cc
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCurrency(string $cc): self
    {
        if (!in_array($cc, self::$currencies, true)) {
            throw new InvalidArgumentException(sprintf('Currency %s is not supported.', $cc));
        }

        $this->keys['CC'] = $cc;

        return $this;
    }

    /**
     * Statický konstruktor nové platby.
     *
     * @param null|string $account
     * @param null|float $amount
     * @param null|string $variable
     *
     * @return QRPlatba
     * @throws \InvalidArgumentException
     */
    public static function create(?string $account = null, ?float $amount = null, ?string $variable = null): self
    {
        return new self($account, $amount, $variable);
    }

    /**
     * Nastavení konstatního symbolu.
     *
     * @param string $ks
     *
     * @return $this
     */
    public function setConstantSymbol(string $ks): self
    {
        $this->keys['X-KS'] = $ks;

        return $this;
    }

    /**
     * Nastavení specifického symbolu.
     *
     * @param string $ss
     *
     * @return $this
     * @throws QRPlatbaException
     *
     */
    public function setSpecificSymbol(string $ss): self
    {
        if (mb_strlen($ss) > 10) {
            throw new QRPlatbaException('Specific symbol is higher than 10 chars');
        }
        $this->keys['X-SS'] = $ss;

        return $this;
    }

    /**
     * Nastavení zprávy pro příjemce. Z řetězce bude odstraněna diaktirika.
     *
     * @param string $msg
     *
     * @return $this
     */
    public function setMessage(string $msg): self
    {
        $this->keys['MSG'] = mb_substr($this->stripDiacritics($msg), 0, 60);

        return $this;
    }

    /**
     * Odstranění diaktitiky.
     *
     * @param string $string
     *
     * @return string|string[]
     */
    private function stripDiacritics(string $string)
    {
        return str_replace(
            [
                'ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ú', 'ů',
                'ó', 'ť', 'ď', 'ľ', 'ň', 'ŕ', 'â', 'ă', 'ä', 'ĺ', 'ć',
                'ç', 'ę', 'ë', 'î', 'ń', 'ô', 'ő', 'ö', 'ů', 'ű', 'ü',
                'Ě', 'Š', 'Č', 'Ř', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ů',
                'Ó', 'Ť', 'Ď', 'Ľ', 'Ň', 'Ä', 'Ć', 'Ë', 'Ö', 'Ü',
            ],
            [
                'e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'u', 'u',
                'o', 't', 'd', 'l', 'n', 'a', 'a', 'a', 'a', 'a', 'a',
                'c', 'e', 'e', 'i', 'n', 'o', 'o', 'o', 'u', 'u', 'u',
                'E', 'S', 'C', 'R', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U',
                'O', 'T', 'D', 'L', 'N', 'A', 'C', 'E', 'O', 'U',
            ],
            $string
        );
    }

    /**
     * Nastavení jména příjemce. Z řetězce bude odstraněna diaktirika.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setRecipientName(string $name): self
    {
        $this->keys['RN'] = mb_substr($this->stripDiacritics($name), 0, 35);

        return $this;
    }

    /**
     * Nastavení data úhrady.
     *
     * @param DateTime $date
     *
     * @return $this
     */
    public function setDueDate(DateTime $date): self
    {
        $this->keys['DT'] = $date->format('Ymd');

        return $this;
    }

    /**
     * Metoda vrátí QR Platbu jako textový řetězec.
     *
     * @return string
     */
    public function __toString(): string
    {
        $chunks = ['SPD', self::VERSION];
        foreach ($this->keys as $key => $value) {
            if (null === $value) {
                continue;
            }
            $chunks[] = $key . ':' . $value;
        }

        return implode('*', $chunks);
    }

    /**
     * Metoda vrátí QR kód jako HTML tag, případně jako data-uri.
     *
     * @param bool $htmlTag
     * @param int $size
     * @param int $margin
     * @param string $format
     *
     * @return string
     */
    public function getQRCodeImage(bool $htmlTag = true, int $size = 300, int $margin = 0, string $format = self::FORMAT_PNG): string
    {
        $qrCodeResult = $this->getQRCodeResult($size, $margin, $format);
        $dataUri = $qrCodeResult->getDataUri();

        return $htmlTag ? sprintf('<img src="%s" alt="QR Platba" />', $dataUri) : $dataUri;
    }

    /**
     * Instance třídy QrCode pro libovolné úpravy (barevnost, atd.).
     *
     * @param int $size
     * @param int $margin
     * @param string $format
     *
     * @return \Endroid\QrCode\Writer\Result\ResultInterface
     * @throws \BaconQrCode\Exception\WriterException
     */
    public function getQRCodeResult(int $size = 300, int $margin = 0, string $format = self::FORMAT_PNG): ResultInterface
    {
        $qrCodeBuilder = Builder::create()
            ->data((string)$this)
            ->size($size)
            ->margin($margin)
            ->foregroundColor(new Color(0, 0, 0, 0))
            ->backgroundColor(new Color(255, 255, 255, 0))
            ->writer($this->getWriterByFormat($format));

        return $qrCodeBuilder->build();
    }

    /**
     * @param string $format
     *
     * @return \Endroid\QrCode\Writer\WriterInterface
     * @throws \BaconQrCode\Exception\WriterException
     */
    private function getWriterByFormat(string $format): WriterInterface
    {
        switch ($format) {
            case self::FORMAT_BINARY:
                return new BinaryWriter();
            case self::FORMAT_DEBUG:
                return new DebugWriter();
            case self::FORMAT_ESP:
                return new EpsWriter();
            case self::FORMAT_PDF:
                return new PdfWriter();
            case self::FORMAT_PNG:
                return new PngWriter();
            case self::FORMAT_SVG:
                return new SvgWriter();
        }

        throw new WriterException('Writer is not defined.');
    }

    /**
     * Uložení QR kódu do souboru.
     *
     * @param null|string $filename File name of the QR Code
     * @param int $size Format of the file (png, jpeg, jpg, gif, wbmp)
     * @param int $margin
     * @param string $format
     *
     * @return QRPlatba
     * @throws \BaconQrCode\Exception\WriterException
     */
    public function saveQRCodeImage(?string $filename = null, int $size = 300, int $margin = 0, string $format = self::FORMAT_PNG): self
    {
        $qrCodeResult = $this->getQRCodeResult($size, $margin, $format);
        $qrCodeResult->saveToFile($filename);

        return $this;
    }
}
