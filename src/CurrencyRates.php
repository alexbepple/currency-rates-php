<?php

function currencySymbols() {
    global $cachedCurrencySymbols, $lastUpdated;

    $delta = time() - $lastUpdated;
    if (!empty($cachedCurrencySymbols) && $delta <= 300)
        return $cachedCurrencySymbols;

    $page = file_get_contents('http://www.xe.com/iso4217.php');
    $pattern = '/href="\/currency\/[^>]+>(...)<\/a><\/td><td class="[^"]+">([A-Za-z ]+)/';
    preg_match_all($pattern, $page, $out);

    $cachedCurrencySymbols = $out[1];
    $lastUpdated = time();
    return $cachedCurrencySymbols;
}

function currencyRate($from, $to) {
    $symbols = currencySymbols();
    if (!in_array($from, $symbols))
        throw new Exception(sprintf('Invalid from currency: %s', $from));
    if (!in_array($to, $symbols))
        throw new Exception(sprintf('Invalid to currency: %s', $to));

    $url = sprintf('http://www.gocurrency.com/v2/dorate.php?inV=1&from=%s&'.
            'to=%s&Calculate=Convert', $from, $to);
    $contents = file_get_contents($url);
    $start = strripos($contents, '<div id="converter_results"><ul><li>');
    $substring = substr($contents, $start);

    $startOfInterestingStuff = stripos($substring, '<b>') + 3;
    $endOfInterestingStuff = stripos($substring, '</b>', $startOfInterestingStuff);
    $interestingStuff = substr($substring, $startOfInterestingStuff, $endOfInterestingStuff-$startOfInterestingStuff);

    $parts = explode('=', $interestingStuff);
    $innerParts = explode(' ', trim($parts[1]));
    $rate = $innerParts[0];

    return $rate;
}

