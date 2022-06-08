<?php

namespace Kingsquare\Parser\Banking\Mt940;

use Kingsquare\Banking\Statement;
use Kingsquare\Banking\Transaction;
use Kingsquare\Parser\Banking\Mt940;

/**
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class Engine
{
    private $rawData = '';
    protected $currentStatementData = '';
    protected $currentTransactionData = '';

    public $debug = false;

    protected static $registeredEngines = [
        100 => Engine\Abn::class,
        200 => Engine\Ing::class,
        300 => Engine\Rabo::class,
        400 => Engine\Spk::class,
        500 => Engine\Triodos::class,
        600 => Engine\Knab::class,
        700 => Engine\Hsbc::class,
        800 => Engine\Bunq::class,
        900 => Engine\Penta::class,
        1000 => Engine\Asn::class,
        1100 => Engine\Kbs::class,
        1200 => Engine\Zetb::class,
        1300 => Engine\Kontist::class,
    ];

    /**
     * reads the firstline of the string to guess which engine to use for parsing.
     *
     * @param string $string
     *
     * @return Engine
     */
    public static function __getInstance($string)
    {
        $engine = self::detectBank($string);
        $engine->loadString($string);

        return $engine;
    }

    /**
     * Register a new Engine.
     *
     * @param string $engineClass Class name of Engine to be registered
     * @param int $priority
     */
    public static function registerEngine($engineClass, $priority)
    {
        if (!is_int($priority)) {
            trigger_error('Priority must be integer', E_USER_WARNING);

            return;
        }
        if (array_key_exists($priority, self::$registeredEngines)) {
            trigger_error('Priority already taken', E_USER_WARNING);

            return;
        }
        if (!class_exists($engineClass)) {
            trigger_error('Engine does not exist', E_USER_WARNING);

            return;
        }
        self::$registeredEngines[$priority] = $engineClass;
    }

    /**
     * Unregisters all Engines.
     */
    public static function resetEngines()
    {
        self::$registeredEngines = [];
    }

    /**
     * Checks whether the Engine is applicable for the given string.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isApplicable($string)
    {
        return true;
    }

    /**
     * @param string $string
     *
     * @return Engine
     */
    private static function detectBank($string)
    {
        ksort(self::$registeredEngines, SORT_NUMERIC);

        foreach (self::$registeredEngines as $engineClass) {
            if ($engineClass::isApplicable($string)) {
                return new $engineClass();
            }
        }

        trigger_error('Unknown mt940 parser loaded, thus reverted to default');

        return new Engine\Unknown();
    }

    /**
     * loads the $string into _rawData
     * this could be used to move it into handling of streams in the future.
     *
     * @param string $string
     */
    public function loadString($string)
    {
        $this->rawData = trim($string);
    }

    /**
     * actual parsing of the data.
     *
     * @return Statement[]
     */
    public function parse()
    {
        $results = [];
        foreach ($this->parseStatementData() as $this->currentStatementData) {
            $statement = new Statement();
            if ($this->debug) {
                $statement->rawData = $this->currentStatementData;
            }
            $statement->setBank($this->parseStatementBank());
            $statement->setAccount($this->parseStatementAccount());
            $statement->setStartPrice($this->parseStatementStartPrice());
            $statement->setEndPrice($this->parseStatementEndPrice());
            $statement->setStartTimestamp($this->parseStatementStartTimestamp());
            $statement->setEndTimestamp($this->parseStatementEndTimestamp());
            $statement->setNumber($this->parseStatementNumber());
            $statement->setCurrency($this->parseStatementCurrency());

            foreach ($this->parseTransactionData() as $this->currentTransactionData) {
                $transaction = new Transaction();
                if ($this->debug) {
                    $transaction->rawData = $this->currentTransactionData;
                }
                $transaction->setAccount($this->parseTransactionAccount());
                $transaction->setAccountName($this->parseTransactionAccountName());
                $transaction->setPrice($this->parseTransactionPrice());
                $transaction->setDebitCredit($this->parseTransactionDebitCredit());
                $transaction->setCancellation($this->parseTransactionCancellation());
                $transaction->setDescription($this->parseTransactionDescription());
                $transaction->setValueTimestamp($this->parseTransactionValueTimestamp());
                $transaction->setEntryTimestamp($this->parseTransactionEntryTimestamp($transaction->getValueTimestamp()));
                $transaction->setTransactionCode($this->parseTransactionCode());
                $statement->addTransaction($transaction);
            }
            $results[] = $statement;
        }

        return $results;
    }

    /**
     * split the rawdata up into statementdata chunks.
     *
     * @return array
     */
    protected function parseStatementData()
    {
        $results = preg_split(
            '/(^:20:|^-X{,3}$|\Z)/m',
            $this->getRawData(),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        array_shift($results); // remove the header
        return $results;
    }

    /**
     * split the statement up into transaction chunks.
     *
     * @return array
     */
    protected function parseTransactionData()
    {
        $results = [];
        preg_match_all('/^:61:(.*?)(?=^:61:|^-X{,3}$|\Z)/sm', $this->getCurrentStatementData(), $results);

        return !empty($results[0]) ? $results[0] : [];
    }

    /**
     * return the actual raw data string.
     *
     * @return string _rawData
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * return the actual raw data string.
     *
     * @return string currentStatementData
     */
    public function getCurrentStatementData()
    {
        return $this->currentStatementData;
    }

    /**
     * return the actual raw data string.
     *
     * @return string currentTransactionData
     */
    public function getCurrentTransactionData()
    {
        return $this->currentTransactionData;
    }

    // statement parsers, these work with currentStatementData

    /**
     * return the actual raw data string.
     *
     * @return string bank
     */
    protected function parseStatementBank()
    {
        return '';
    }

    /**
     * uses field 25 to gather accoutnumber.
     *
     * @return string accountnumber
     */
    protected function parseStatementAccount()
    {
        $results = [];
        if (preg_match('/:25:([\d\.]+)*/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        // SEPA / IBAN
        if (preg_match('/:25:([A-Z0-9]{8}[\d\.]+)*/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return '';
    }

    /**
     * uses field 60F to gather starting amount.
     *
     * @return float price
     */
    protected function parseStatementStartPrice()
    {
        return $this->parseStatementPrice('60F');
    }

    /**
     * uses the 62F field to return end price of the statement.
     *
     * @return float price
     */
    protected function parseStatementEndPrice()
    {
        return $this->parseStatementPrice('62F');
    }

    /**
     * The actual pricing parser for statements.
     *
     * @param string $key
     *
     * @return float|string
     */
    protected function parseStatementPrice($key)
    {
        $results = [];
        if (preg_match('/:' . $key . ':([CD])?.*[A-Z]{3}([\d,\.]+)*/', $this->getCurrentStatementData(), $results)
            && !empty($results[2])
        ) {
            $sanitizedPrice = $this->sanitizePrice($results[2]);

            return (!empty($results[1]) && $results[1] === 'D') ? -$sanitizedPrice : $sanitizedPrice;
        }

        return '';
    }

    /**
     * The currency initials parser for statements.
     * @param string $key
     * @return string currency initials
     */
    protected function parseStatementCurrency($key = '60[FM]')
    {
        $results = [];
        if (preg_match('/:' . $key . ':[CD]?.*([A-Z]{3})([\d,\.]+)*/', $this->getCurrentStatementData(), $results)) {
            return $results[1];
        }
        return '';
    }

    /**
     * uses the 60F field to determine the date of the statement.
     *
     * @deprecated will be removed in the next major release and replaced by startTimestamp / endTimestamps
     *
     * @return int timestamp
     */
    protected function parseStatementTimestamp()
    {
        trigger_error('Deprecated in favor of splitting the start and end timestamps for a statement. ' .
            'Please use parseStatementStartTimestamp($format) or parseStatementEndTimestamp($format) instead. ' .
            'parseStatementTimestamp is now parseStatementStartTimestamp', E_USER_DEPRECATED);

        return $this->parseStatementStartTimestamp();
    }

    /**
     * uses the 60F field to determine the date of the statement.
     *
     * @return int timestamp
     */
    protected function parseStatementStartTimestamp()
    {
        return $this->parseTimestampFromStatement('60F');
    }

    /**
     * uses the 62F field to determine the date of the statement.
     *
     * @return int timestamp
     */
    protected function parseStatementEndTimestamp()
    {
        return $this->parseTimestampFromStatement('62F');
    }

    protected function parseTimestampFromStatement($key)
    {
        $results = [];
        if (preg_match('/:' . $key . ':[C|D](\d{6})*/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeTimestamp($results[1]);
        }

        return 0;
    }

    /**
     * uses the 28C field to determine the statement number.
     *
     * @return string
     */
    protected function parseStatementNumber()
    {
        $results = [];
        if (preg_match('/:28C?:(.*)/', $this->getCurrentStatementData(), $results)
            && !empty($results[1])
        ) {
            return trim($results[1]);
        }

        return '';
    }

    // transaction parsers, these work with getCurrentTransactionData

    /**
     * uses the 86 field to determine account number of the transaction.
     *
     * @return string
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        if (preg_match('/^:86: ?([\d\.]+)\s/m', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

        return '';
    }

    /**
     * uses the 86 field to determine accountname of the transaction.
     *
     * @return string
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        if (preg_match('/:86: ?[\d\.]+ (.+)/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeAccountName($results[1]);
        }

        return '';
    }

    /**
     * uses the 61 field to determine amount/value of the transaction.
     *
     * @return float
     */
    protected function parseTransactionPrice()
    {
        $results = [];
        if (preg_match('/^:61:.*?[CD]([\d,\.]+)N/i', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizePrice($results[1]);
        }

        return 0;
    }

    /**
     * uses the 61 field to determine debit or credit of the transaction.
     *
     * @return string
     */
    protected function parseTransactionDebitCredit()
    {
        $results = [];
        if (preg_match('/^:61:\d+([CD])\d+/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeDebitCredit($results[1]);
        }

        return '';
    }

    /**
     * Parses the Cancellation flag of a Transaction
     *
     * @return boolean
     */
    protected function parseTransactionCancellation()
    {
        return false;
    }

    /**
     * uses the 86 field to determine retrieve the full description of the transaction.
     *
     * @return string
     */
    protected function parseTransactionDescription()
    {
        $results = [];
        if (preg_match_all('/[\n]:86:(.*?)(?=\n(:6([12]))|$)/s', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeDescription(implode(PHP_EOL, $results[1]));
        }

        return '';
    }

    /**
     * uses the 61 field to determine the entry timestamp.
     *
     * @return int
     */
    protected function parseTransactionEntryTimestamp()
    {
        $results = [];
        if (preg_match('/^:61:(\d{2})((\d{2})\d{2})((\d{2})\d{2})[C|D]/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {

            list(, $valueDateY, $valueDateMD, $valueDateM, $entryDateMD, $entryDateM) = $results;
            $entryDate = $valueDateY . $entryDateMD;
            if ($valueDateMD !== $entryDateMD && $valueDateM > $entryDateM) {
                $entryDate = ($valueDateY + 1) . $entryDateMD;
            }

            return $this->sanitizeTimestamp($entryDate, 'ymd');
        }
        return $this->parseTransactionValuta('61');
    }

    /**
     * uses the 61 field to determine the value timestamp.
     *
     * @return int
     */
    protected function parseTransactionValueTimestamp()
    {
        return $this->parseTransactionValuta('61');
    }

    /**
     * This does the actual parsing of the transaction timestamp for given $key.
     *
     * @param string $key
     * @return int
     */
    protected function parseTransactionValuta($key)
    {
        $results = [];
        if (preg_match('/^:' . $key . ':(\d{6})/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return $this->sanitizeTimestamp($results[1]);
        }

        return 0;
    }

    /**
     * uses the 61 field to get the bank specific transaction code.
     *
     * @return string
     */
    protected function parseTransactionCode()
    {
        $results = [];
        if (preg_match('/^:61:.*?N(.{3}).*/', $this->getCurrentTransactionData(), $results)
            && !empty($results[1])
        ) {
            return trim($results[1]);
        }
        return '';
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function sanitizeAccount($string)
    {
        static $crudeReplacements = [
            '.' => '',
            ' ' => '',
            'GIRO' => 'P',
        ];

        // crude IBAN to 'old' converter
        if (Mt940::$removeIBAN
            && preg_match('#[A-Z]{2}[\d]{2}[A-Z]{4}(.*)#', $string, $results)
            && !empty($results[1])
        ) {
            $string = $results[1];
        }

        $account = ltrim(
            str_replace(
                array_keys($crudeReplacements),
                $crudeReplacements,
                strip_tags(trim($string))
            ),
            '0'
        );
        if ($account !== '' && strlen($account) < 9 && strpos($account, 'P') === false) {
            $account = 'P' . $account;
        }

        return $account;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function sanitizeAccountName($string)
    {
        return preg_replace('/[\r\n]+/', '', trim($string));
    }

    /**
     * @param string $string
     * @param string $inFormat
     *
     * @return int
     */
    protected function sanitizeTimestamp($string, $inFormat = 'ymd')
    {
        $date = \DateTime::createFromFormat($inFormat, $string);
        $date->setTime(0, 0);
        if ($date !== false) {
            return (int) $date->format('U');
        }

        return 0;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function sanitizeDescription($string)
    {
        return preg_replace('/[\r\n]+/', '', trim($string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function sanitizeDebitCredit($string)
    {
        $debitOrCredit = strtoupper(substr((string) $string, 0, 1));
        if ($debitOrCredit !== Transaction::DEBIT && $debitOrCredit !== Transaction::CREDIT) {
            trigger_error('wrong value for debit/credit (' . $string . ')', E_USER_ERROR);
            $debitOrCredit = '';
        }

        return $debitOrCredit;
    }

    /**
     * @param string $string
     *
     * @return float
     */
    protected function sanitizePrice($string)
    {
        $floatPrice = ltrim(str_replace(',', '.', strip_tags(trim($string))), '0');

        return (float) $floatPrice;
    }
}
