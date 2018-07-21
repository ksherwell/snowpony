<?php
namespace Infortis\Base\Plugin\Framework\Locale;

class Format
{
  public function afterGetPriceFormat($subject, $result) {
    $result['precision'] = 0;
    $result['requiredPrecision'] = 0;

    return $result;
  }
}