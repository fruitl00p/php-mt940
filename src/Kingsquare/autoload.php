<?php

spl_autoload_register(
  function ($class) {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
          $classes = array(
			  'Kingsquare\\Banking\\Statement' => '/banking/statement.php',
			  'Kingsquare\\Banking\\Transaction' => '/banking/transaction.php',
			  'Kingsquare\\Parser\\Banking' => '/parser/banking.php',
			  'Kingsquare\\Parser\\Banking\\Mt940' => '/parser/banking/mt940.php',
			  'Kingsquare\\Parser\\Banking\\Mt940\\Engine' => '/parser/banking/mt940/engine.php',
			  'Kingsquare\\Parser\\Banking\\Mt940\\Engine\\Abn' => '/parser/banking/mt940/engine/abn.php',
			  'Kingsquare\\Parser\\Banking\\Mt940\\Engine\\Ing' => '/parser/banking/mt940/engine/ing.php',
			  'Kingsquare\\Parser\\Banking\\Mt940\\Engine\\Rabo' => '/parser/banking/mt940/engine/rabo.php',
			  'Kingsquare\\Parser\\Banking\\Mt940\\Engine\\Unknown' => '/parser/banking/mt940/engine/unknown.php',
          );
          $path = dirname(__FILE__);
      }

      if (isset($classes[$class])) {
          require $path . strtolower($classes[$class]);
      }
  }
);