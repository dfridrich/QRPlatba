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

namespace Defr\QRPlatba;

use DateTime;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\Label\Alignment\LabelAlignmentLeft;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeEnlarge;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Iban\Validation\Iban;
use Iban\Validation\Validator;

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
    public const SPD_VERSION = '1.0';

    /**
     * Verze QR formátu QR Faktury.
     */
    public const SID_VERSION = '1.0';

    public const FORMAT_PNG = 'png';
    public const FORMAT_SVG = 'svg';

    public const FORMATS = [
        self::FORMAT_PNG,
        self::FORMAT_SVG,
    ];

    private static array $currencies = [
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
     * Klíče QR Platby
     */
    private array $spdKeys = [
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
     * Klíče QR Faktury
     */
    private array $sidKeys = [
        'ID' => null,
        // Max. 40 znaků - Jednoznačné označení dokladu
        'DD' => null,
        // Právě 8 znaků - Datum vystavení dokladu ve formátu YYYYMMDD
        'AM' => null,
        // Max. 18 znaků - Výše celkové částky k úhradě v měně specifikované klíčem CC
        'TP' => null,
        // Právě 1 znak - Identifikace typu daňového plnění
        'TD' => null,
        // Právě 1 znak - Identifikace typu dokladu
        'SA' => null,
        // Právě 1 znak - Příznak, který rozlišuje, zda faktura obsahuje zúčtování záloh
        'MSG' => null,
        // Max. 40 znaků - Textový popis předmětu fakturace
        'ON' => null,
        // Max. 20 znaků - Číslo (označení) objednávky, k níž se vztahuje tento účetní doklad
        'VS' => null,
        // Max. 10 znaků - Variabilní symbol
        'VII' => null,
        // Max. 14 znaků - DIČ výstavce
        'INI' => null,
        // Max. 8 znaků - IČO výstavce
        'VIR' => null,
        // Max. 14 znaků - DIČ příjemce
        'INR' => null,
        // Max. 8 znaků - IČO příjemce
        'DUZP' => null,
        // Právě 8 znaků - Datum uskutečnění zdanitelného plnění ve formátu YYYYMMDD
        'DPPD' => null,
        // Právě 8 znaků - Datum povinnosti přiznat daň ve formátu YYYYMMDD
        'DT' => null,
        // Právě 8 znaků - Datum splatnosti celkové částky ve formátu YYYYMMDD
        'TB0' => null,
        // Max. 18 znaků - Částka základu daně v základní daňové sazbě v CZK včetně haléřového vyrovnání
        'T0' => null,
        // Max. 18 znaků - Částka daně v základní daňové sazbě v CZK včetně haléřového vyrovnání
        'TB1' => null,
        // Max. 18 znaků - Částka základu daně v první snížené daňové sazbě v CZK včetně haléřového vyrovnání
        'T1' => null,
        // Max. 18 znaků - Částka daně v první snížené daňové sazbě v CZK včetně haléřového vyrovnání
        'TB2' => null,
        // Max. 18 znaků - Částka základu daně ve druhé snížené daňové sazbě v CZK včetně haléřového vyrovnání
        'T2' => null,
        // Max. 18 znaků - Částka daně ve druhé snížené daňové sazbě v CZK včetně haléřového vyrovnání
        'NTB' => null,
        // Max. 18 znaků - Částka osvobozených plnění, plnění mimo předmět DPH, plnění neplátců DPH v CZK včetně haléřového vyrovnání. V případě kladné hodnoty bez znaménka, záporná hodnota se znaménkem. Znaménko vždy explicitně určuje směr toku peněz bez ohledu na jiné atributy
        'CC' => 'CZK',
        // Právě 3 znaky - Měna celkové částky. Není-li klíč v řetězci přítomen = měna je CZK
        'FX' => null,
        // Max. 18 znaků - Směnný kurz mezi CZK a měnou celkové částky
        'FXA' => null,
        // Max. 5 znaků - Počet jednotek cizí měny pro přepočet pomocí klíče FX. Není-li v řetězci klíč přítomen = 1
        'ACC' => null,
        // Max. 46 - Identifikace čísla účtu výstavce faktury, která je složena ze dvou komponent oddělených znaménkem + Tyto komponenty jsou: číslo účtu ve formátu IBAN identifikace banky ve formátu SWIFT dle ISO 9362. Druhá komponenta (SWIFT) je přitom volitelná
        'CRC32' => null,
        // Právě 8 znaků - Kontrolní součet. Hodnota vznikne výpočtem CRC32 celého řetězce (bez klíče CRC32) a převedením této číselné hodnoty do hexadecimálního zápisu.
        'X-SW' => null,
        // Max. 30 - Označení účetního software, ve kterém byl řetězec QR Faktury (faktura) vytvořen. Libovolný řetězec dle rozhodnutí výrobce účetního software. Označení by mělo být obecně unikátní a neměnné pro daný software (nebo jeho verzi).
        'X-URL' => null,
        // Max. 70 - Údaje pro získání účetních údajů (případně faktury) ve strukturovaném formátu z on-line uložiště.
    ];

    /**
     * Přepínač, zda se má generovat pouze QR Faktura
     */
    private $isOnlyInvoice = false;

    private ?string $label = null;

    /**
     * Kontruktor nové platby.
     */
    public function __construct(?string $account = null, ?float $amount = null, ?string $variable = null, ?string $currency = null)
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
     * Statický konstruktor nové platby.
     */
    public static function create(?string $account = null, ?float $amount = null, ?string $variable = null)
    {
        return new self($account, $amount, $variable);
    }

    /**
     * Nastavení čísla účtu ve formátu 12-3456789012/0100 nebo IBAN
     */
    public function setAccount(string $account): self
    {
        $this->spdKeys['ACC'] = $this->prepareIban($account);
        $this->sidKeys['ACC'] = $this->prepareIban($account);

        return $this;
    }


    /**
     * Nastavení alternativního čísla účtu ve formátu 12-3456789012/0100 nebo IBAN
     */
    public function addAlternativeAccount(string $account): self
    {
        $this->spdKeys['ALT-ACC'] .= ($this->spdKeys['ALT-ACC'] ? ',' : '').$this->prepareIban($account);

        return $this;
    }

    /**
     * Přímé nastavení účtu v IBAN formátu
     */
    public function setIBAN(string $iban): self
    {
        $iban = new Iban($iban);
        $validator = new Validator();

        if (!$validator->validate($iban)) {
            foreach ($validator->getViolations() as $violation) {
                throw new QRPlatbaException($violation);
            }
        }

        $normalizedIban = $iban->getNormalizedIban();
        $this->spdKeys['ACC'] = $normalizedIban;
        $this->sidKeys['ACC'] = $normalizedIban;

        return $this;
    }


    /**
     * Rozhodne zda-li se jedna o cislo uctu nebo IBAN a vrati vzdy spravne pripraveny IBAN
     */
    public function prepareIban(string $account): string
    {
        $ibanTest = new Iban($account);
        $validator = new Validator();
        if ($validator->validate($ibanTest)) {
            return $ibanTest->getNormalizedIban();
        }

        return self::accountToIban($account);
    }


    /**
     * Nastavení částky.
     */
    public function setAmount(float $amount): self
    {
        $this->spdKeys['AM'] = number_format($amount, 2, '.', '');
        $this->sidKeys['AM'] = number_format($amount, 2, '.', '');

        return $this;
    }

    /**
     * Nastavení variabilního symbolu.
     */
    public function setVariableSymbol(string $vs): self
    {
        $this->spdKeys['X-VS'] = $vs;
        $this->sidKeys['VS'] = $vs;

        return $this;
    }

    /**
     * Nastavení konstatního symbolu.
     */
    public function setConstantSymbol(string $cs): self
    {
        $this->spdKeys['X-KS'] = $cs;

        return $this;
    }

    /**
     * Nastavení specifického symbolu.
     */
    public function setSpecificSymbol(string $ss): self
    {
        if (mb_strlen($ss) > 10) {
            throw new QRPlatbaException('Specific symbol is higher than 10 chars');
        }
        $this->spdKeys['X-SS'] = $ss;

        return $this;
    }

    /**
     * Nastavení zprávy pro příjemce. Z řetězce bude odstraněna diaktirika.
     */
    public function setMessage(string $msg): self
    {
        $this->spdKeys['MSG'] = mb_substr($this->stripDiacritics($msg), 0, 60);
        $this->sidKeys['MSG'] = mb_substr($this->stripDiacritics($msg), 0, 60);

        return $this;
    }

    /**
     * Nastavení jména příjemce. Z řetězce bude odstraněna diaktirika.
     */
    public function setRecipientName($name): self
    {
        $this->spdKeys['RN'] = mb_substr($this->stripDiacritics($name), 0, 35);

        return $this;
    }

    /**
     * Nastavení data úhrady.
     */
    public function setDueDate(DateTime $date): self
    {
        $this->spdKeys['DT'] = $date->format('Ymd');
        $this->sidKeys['DT'] = $date->format('Ymd');

        return $this;
    }

    /**
     * @param $cc
     */
    public function setCurrency($cc): self
    {
        if (!in_array($cc, self::$currencies, true)) {
            throw new \InvalidArgumentException(sprintf('Currency %s is not supported.', $cc));
        }

        $this->spdKeys['CC'] = $cc;
        $this->sidKeys['CC'] = $cc;

        return $this;
    }

    /**
     * Přepínač, zda se má generovat pouze QR Faktura
     */
    public function setIsOnlyInvoice(bool $isOnlyInvoice): self
    {
        $this->isOnlyInvoice = $isOnlyInvoice;

        return $this;
    }

    /**
     * Nastavení ID faktury
     */
    public function setInvoiceId(string $id): self
    {
        if (mb_strlen($id) > 40) {
            throw new QRPlatbaException('Invoice id is longer than 40 characters');
        }

        $this->sidKeys['ID'] = $id;

        return $this;
    }

    /**
     * Nastavení data vydání faktury
     */
    public function setInvoiceDate(DateTime $date): self
    {
        $this->sidKeys['DD'] = $date->format('Ymd');

        return $this;
    }

    /**
     * Nastavení typu daňového plnění
     *
     * 0, nebo není klíč v řetězci přítomen = běžný typ plnění
     * 1 = RPDP
     * 2 = smíšený
     *
     * @TODO: Pridat konstanty
     */
    public function setTaxPerformance(int $tp): self
    {
        if ($tp!==0 && $tp!==1 && $tp!==2) {
            throw new QRPlatbaException('Unknown tax performance ID');
        }

        $this->sidKeys['TP'] = $tp;

        return $this;
    }

    /**
     * Nastavení identifikace typu dokladu
     *
     * 0 – nedaňový doklad (např. zálohová faktura)
     * 1 – opravný daňový doklad
     * 2 – doklad k přijaté platbě
     * 3 – splátkový kalendář
     * 4 – platební kalendář
     * 5 – souhrnný daňový doklad
     * 9 – ostatní daňové doklady
     * Není-li klíč v řetězci přítomen = 9
     *
     * @TODO: Pridat konstanty
     */
    public function setInvoiceDocumentType(int $td): self
    {
        if (($td<0 || $td>5) && $td!==9) {
            throw new QRPlatbaException('Unknown invoice document type ID');
        }

        $this->sidKeys['TD'] = $td;

        return $this;
    }

    /**
     * Nastavení příznaku, který rozlišuje, zda faktura obsahuje zúčtování záloh
     */
    public function setInvoiceIncludingDeposit(bool $sa): self
    {
        $this->sidKeys['SA'] = (int)$sa;

        return $this;
    }

    /**
     * Nastavení čísla (označení) objednávky, k níž se vztahuje tento účetní doklad
     */
    public function setInvoiceRelatedId(string $on): self
    {
        if (mb_strlen($on) > 20) {
            throw new QRPlatbaException('Invoice related id is longer than 20 characters');
        }

        $this->sidKeys['ON'] = $on;

        return $this;
    }

    /**
     * Nastavení DIČ výstavce
     *
     */
    public function setCompanyTaxId(string $vii): self
    {
        if (mb_strlen($vii) > 14) {
            throw new QRPlatbaException('Tax identification number of invoicing subject is longer than 14 characters');
        }

        $this->sidKeys['VII'] = $vii;

        return $this;
    }

    /**
     * Nastavení IČO výstavce
     */
    public function setCompanyRegistrationId(string $ini): self
    {
        if (mb_strlen($ini) > 8) {
            throw new QRPlatbaException('Company registration number of invoicing subject is longer than 8 characters');
        }

        $this->sidKeys['INI'] = $ini;

        return $this;
    }

    /**
     * Nastavení DIČ příjemce
     */
    public function setInvoiceSubjectTaxId(string $vir): self
    {
        if (mb_strlen($vir) > 14) {
            throw new QRPlatbaException('Tax identification number of invoiced subject is longer than 14 characters');
        }

        $this->sidKeys['VIR'] = $vir;

        return $this;
    }

    /**
     * Nastavení IČO příjemce
     */
    public function setInvoiceSubjectRegistrationId(string $inr): self
    {
        if (mb_strlen($inr) > 8) {
            throw new QRPlatbaException('Company registration number of invoiced subject is longer than 8 characters');
        }

        $this->sidKeys['INR'] = $inr;

        return $this;
    }

    /**
     * Nastavení data uskutečnění zdanitelného plnění
     */
    public function setTaxDate(DateTime $date): self
    {
        $this->sidKeys['DUZP'] = $date->format('Ymd');

        return $this;
    }

    /**
     * Nastavení data povinnosti přiznat daň
     */
    public function setTaxReportDate(DateTime $date): self
    {
        $this->sidKeys['DPPD'] = $date->format('Ymd');

        return $this;
    }

    /**
     * Nastavení částky základu daně v CZK včetně haléřového vyrovnání
     *
     * $taxLevelId: 0, základní sazba DPH
     * $taxLevelId: 1, snížená sazba DPH
     * $taxLevelId: 2, druhá snížená sazba DPH
     */
    public function setTaxBase(float $amount, int $taxLevelId): self
    {
        if ($taxLevelId<0 || $taxLevelId>2) {
            throw new QRPlatbaException('Unknown tax level ID');
        }

        $this->sidKeys['TB'.$taxLevelId] = sprintf('%.2f', $amount);

        return $this;
    }

    /**
     * Nastavení částky daně v CZK včetně haléřového vyrovnání
     *
     * $taxLevelId: 0, základní sazba DPH
     * $taxLevelId: 1, snížená sazba DPH
     * $taxLevelId: 2, druhá snížená sazba DPH
     */
    public function setTaxAmount(float $amount, int $taxLevelId): self
    {
        if ($taxLevelId<0 || $taxLevelId>2) {
            throw new QRPlatbaException('Unknown tax level ID');
        }

        $this->sidKeys['T'.$taxLevelId] = sprintf('%.2f', $amount);

        return $this;
    }

    /**
     * Nastavení částky osvobozených plnění, plnění mimo předmět DPH, plnění neplátců DPH v CZK včetně haléřového vyrovnání
     */
    public function setNoTaxAmount(float $amount): self
    {
        $this->sidKeys['NTB'] = sprintf('%.2f', $amount);

        return $this;
    }

    /**
     * Nastavení směnného kurzu mezi CZK a měnou celkové částky
     */
    public function setCurrencyRate(float $currencyRate): self
    {
        $this->sidKeys['FX'] = sprintf('%.3f', $currencyRate);

        return $this;
    }

    /**
     * Nastavení označení účetního software, ve kterém byl řetězec QR Faktury (faktura) vytvořen
     */
    public function setTaxSoftware(string $taxSoftware): self
    {
        if (mb_strlen($taxSoftware) > 30) {
            throw new QRPlatbaException('Tax software name is longer than 30 characters');
        }

        $this->sidKeys['X-SW'] = $taxSoftware;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Metoda vrátí QR Platbu jako textový řetězec.
     *
     * @return string
     */
    public function __toString()
    {
        $encodedString = '';

        // QR Platba
        if ($this->isOnlyInvoice === false) {
            $chunks = ['SPD', self::SPD_VERSION];
            foreach ($this->spdKeys as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $chunks[] = $key.':'.$value;
            }
            $encodedString .= implode('*', $chunks);
        }

        // QR Faktura
        if (!is_null($this->sidKeys['ID']) && !is_null($this->sidKeys['DD'])) {
            $chunks = ['SID', self::SID_VERSION];
            foreach ($this->sidKeys as $key => $value) {
                if (
                    null === $value ||
                    ($this->isOnlyInvoice === false && (
                            (isset($this->spdKeys[$key]) && $this->spdKeys[$key] === $value) ||
                            (isset($this->spdKeys['X-'.$key]) && $this->spdKeys['X-'.$key] === $value)
                        ))
                ) {
                    continue;
                }
                $chunks[] = $key.':'.$value;
            }

            if ($this->isOnlyInvoice === false) {
                $encodedString .= '*X-INV:'.implode('%2A', $chunks).'*';
            } else {
                $encodedString .= implode('*', $chunks).'*';
            }
        }

        return $encodedString;
    }

    /**
     * Metoda vrátí QR kód jako HTML tag, případně jako data-uri.
     */
    public function getQRCodeImage(bool $htmlTag = true, int $size = 300, int $margin = 10): string
    {
        $data = $this->getDataUri($size, $margin);

        return $htmlTag
            ? sprintf('<img src="%s" width="%2$d" height="%2$d" alt="QR Platba" />', $data, $size)
            : $data;
    }

    /**
     * Metoda vrátí QR kód jako HTML tag, případně jako data-uri.
     */
    public function getDataUri(int $size = 300, int $margin = 10): string
    {
        $qrCode = $this->getQRCodeInstance($size, $margin);
        $writer = new PngWriter();

        return $writer->write($qrCode, null, $this->getLabelInstance())->getDataUri();
    }

    /**
     * Uložení QR kódu do souboru.
     * @throws QRPlatbaException
     */
    public function saveQRCodeImage(?string $filename = null, ?string $format = 'png', int $size = 300, int $margin = 10): self
    {
        $qrCode = $this->getQRCodeInstance($size, $margin);

        switch ($format) {
            case self::FORMAT_PNG:
                $writer = new PngWriter();
                break;
            case self::FORMAT_SVG:
                $writer = new SvgWriter();
                break;
            default:
                throw new QRPlatbaException('Unknown file format');
        }

        $writer->write($qrCode, null, $this->getLabelInstance())->saveToFile($filename);

        return $this;
    }

    /**
     * Instance třídy QrCode pro libovolné úpravy (barevnost, atd.).
     */
    public function getQRCodeInstance(int $size = 300, int $margin = 10): QrCode
    {
        return QrCode::create((string) $this)
            ->setSize($size - ($margin * 2))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium())
            ->setMargin($margin)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeEnlarge())
            ->setForegroundColor(new Color(0, 0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255, 0));
    }

    /**
     * Převedení čísla účtu na formát IBAN.
     * @throws QRPlatbaException
     */
    public static function accountToIban(string $accountNumber): string
    {
        $accountNumber = explode('/', $accountNumber);
        if (count($accountNumber) !== 2) {
            throw new QRPlatbaException('Sorry, but this is not a bank account');
        }
        $bank = $accountNumber[1];
        $pre = 0;
        $acc = 0;
        if (false === mb_strpos($accountNumber[0], '-')) {
            $acc = $accountNumber[0];
        } else {
            list($pre, $acc) = explode('-', $accountNumber[0]);
        }

        $accountPart = sprintf('%06d%010s', $pre, $acc);
        $iban = 'CZ00'.$bank.$accountPart;

        $alfa = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
        $alfa = explode(' ', $alfa);
        $alfa_replace = [];
        for ($i = 1; $i < 27; ++$i) {
            $alfa_replace[] = $i + 9;
        }
        $controlegetal = str_replace(
            $alfa,
            $alfa_replace,
            mb_substr($iban, 4, mb_strlen($iban) - 4).mb_substr($iban, 0, 2).'00'
        );
        $controlegetal = 98 - (int)bcmod($controlegetal, '97');
        $iban = sprintf('CZ%02d%04d%06d%010s', $controlegetal, $bank, $pre, $acc);

        return $iban;
    }

    /**
     * Odstranění diaktitiky.
     */
    private function stripDiacritics(string $string): string
    {
        $string = str_replace(
            [
                'ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ú', 'ů',
                'ó', 'ť', 'ď', 'ľ', 'ň', 'ŕ', 'â', 'ă', 'ä', 'ĺ', 'ć',
                'ç', 'ę', 'ë', 'î', 'ń', 'ô', 'ő', 'ö', 'ů', 'ű', 'ü',
                'Ě', 'Š', 'Č', 'Ř', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ů',
                'Ó', 'Ť', 'Ď', 'Ľ', 'Ň', 'Ä', 'Ć', 'Ë', 'Ö', 'Ü'
            ],
            [
                'e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'u', 'u',
                'o', 't', 'd', 'l', 'n', 'a', 'a', 'a', 'a', 'a', 'a',
                'c', 'e', 'e', 'i', 'n', 'o', 'o', 'o', 'u', 'u', 'u',
                'E', 'S', 'C', 'R', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U',
                'O', 'T', 'D', 'L', 'N', 'A', 'C', 'E', 'O', 'U'
            ],
            $string
        );

        return $string;
    }

    private function getLabelInstance(): ?Label
    {
        if ($this->label !== null) {
            return Label::create($this->label)
                ->setAlignment(new LabelAlignmentLeft())
                ->setFont(new OpenSans())
                ->setTextColor(new Color(0, 0, 0, 0));
        }

        return null;
    }
}
