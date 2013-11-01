<?php

spl_autoload_register(
  function ($class) {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
          $classes = array(
            'Statement_banking' => '/banking/statement.php',
            'Transaction_banking' => '/banking/transaction.php',
            'Banking_parser' => '/parser/banking.php',
            'Mt940_banking_parser' => '/parser/banking/mt940.php',
            'Engine_mt940_banking_parser' => '/parser/banking/mt940/engine.php',
            'Abn_engine_mt940_banking_parser' => '/parser/banking/mt940/engine/abn.php',
            'Ing_engine_mt940_banking_parser' => '/parser/banking/mt940/engine/ing.php',
            'Rabo_engine_mt940_banking_parser' => '/parser/banking/mt940/engine/rabo.php',
            'Unknown_engine_mt940_banking_parser' => '/parser/banking/mt940/engine/unknown.php',
          );
          $path = dirname(__FILE__);
      }

      if (isset($classes[$class])) {
          require $path . strtolower($classes[$class]);
      }
  }
);