<?php

/**
 * Format by WC Currency
 * @param float $price
 */

function format_price($price)
{
  return wc_price($price);
}

