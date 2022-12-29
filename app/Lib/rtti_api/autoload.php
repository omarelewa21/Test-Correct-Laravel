<?php


 function autoload_5ac671c2ad9e1eb5dde7e750691d4dc2($class)
{
    $classes = array(
        'RTTIToetsService' => __DIR__ .'/RTTIToetsService.php',
        'toetsscoreType' => __DIR__ .'/toetsscoreType.php',
        'betrouwbaarheidsinterval' => __DIR__ .'/betrouwbaarheidsinterval.php',
        'referentiescoreType' => __DIR__ .'/referentiescoreType.php',
        'leerresultaten_verzoek' => __DIR__ .'/leerresultaten_verzoek.php',
        'ctpSchool' => __DIR__ .'/ctpSchool.php',
        'ArrayOfCtpToetsafname' => __DIR__ .'/ArrayOfCtpToetsafname.php',
        'ctpToetsafname' => __DIR__ .'/ctpToetsafname.php',
        'ArrayOfCtpResultaat' => __DIR__ .'/ArrayOfCtpResultaat.php',
        'ctpResultaat' => __DIR__ .'/ctpResultaat.php',
        'ctpVocabulaireGebondenVeld' => __DIR__ .'/ctpVocabulaireGebondenVeld.php',
        'ctpAnderResultaatType' => __DIR__ .'/ctpAnderResultaatType.php',
        'ctpOsoResultaat' => __DIR__ .'/ctpOsoResultaat.php',
        'ArrayOfCtpToets' => __DIR__ .'/ArrayOfCtpToets.php',
        'ctpToets' => __DIR__ .'/ctpToets.php',
        'ctpNormering' => __DIR__ .'/ctpNormering.php',
        'norm' => __DIR__ .'/norm.php',
        'normkleur' => __DIR__ .'/normkleur.php',
        'ArrayOfCtpToetsIngang' => __DIR__ .'/ArrayOfCtpToetsIngang.php',
        'ingang' => __DIR__ .'/ingang.php',
        'ArrayOfCtpToetsOnderdeel' => __DIR__ .'/ArrayOfCtpToetsOnderdeel.php',
        'ctpToetsOnderdeel' => __DIR__ .'/ctpToetsOnderdeel.php',
        'ctpLeeg' => __DIR__ .'/ctpLeeg.php',
        'autorisatie' => __DIR__ .'/autorisatie.php'
    );
    if (!empty($classes[$class])) {
        include $classes[$class];
    };
}

spl_autoload_register('autoload_5ac671c2ad9e1eb5dde7e750691d4dc2');

// Do nothing. The rest is just leftovers from the code generation.
{
}
