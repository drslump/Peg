<?php

include_once 'library/DrSlump/Peg.php';
include_once 'library/DrSlump/Peg/dsl.php';

use DrSlump\Peg;

// Setup autoloader
Peg::autoload();


$grammar = new Peg\Grammar(function(Peg\Grammar $g){

    $g['sexpr']  = alt( '<string>', '<list>', '<comment>' );
    $g['string'] = alt( '<token>', '<base64>', '<hex>', '<quoted>' );
    $g['token']  = alt( '<alpha>', '<digit>', '<punc>' )->once;
    //$g['base64'] = seq( '|', alt( '<alpha>', '<digit>', '+', '/', '=' )->repeat, '|' );
    $g['base64'] = str('|') ->
                   alt('<alpha>', '<digit>', '/[+/=]/')->repeat ->
                   str('|');
    //$g['hex']    = seq( '#', alt( '<digit>', '/[A-Fa-f]/', '<space>' )->repeat, '#' );
    $g['hex']    = str('#')
                   -> alt( '<digit>', '/[A-Fa-f]+/', '<space>' )->repeat
                   -> str('#');

    $g['quoted'] = alt(
                       // Double quotes
                       seq(
                         '"',
                         alt(
                           seq( '\\', any() ),
                           seq( str('"')->absent, any() )
                         )->repeat,
                         '"'
                       ),
                       // Single quotes
                       str("'")
                         ->alt(
                           str('\\') -> any(),
                           str("'")->absent -> any()
                         )->repeat
                       ->str("'")
                   );

    $g['list']   = alt(
        seq( '(', alt( '<sexpr>', '<space>' )->repeat, ')' ),
        seq( '(', alt( '<sexpr>', '<space>' )->repeat )->error('Missing ending brace')
    );

    $g['space']  = alt( " ", "\t", "\r", "\n" )->once;
    $g['space?'] = ref('space')->maybe;
    $g['digit']  = rex('[0-9]+');
    $g['alpha']  = rex('[A-Za-z0-9]+');
    $g['punc']   = alt( '-', '.', '/', '_', ':', '*', '+', '=' );
    $g['comment']= seq( ';', '/.*/' );

    $g['root'] = seq('<space?>', '<sexpr>', '<space?>');
//    $g->root('sexpr');
});


//<sexpr>     :: <string> | <list>
//<string>    :: <token> | <base64> | <hex> | <quoted> ;
//<token>     :: <tokenchar>+ ;
//<base64>    :: "|" ( <alpha> | <digit> | "+" | "/" | "=" )* "|" ;
//<hex>       :: "#" ( <digit> | "A" ... "F" | "a" ... "f" | <space> )* "#" ;
//<quoted>    :: "\"" <bytes> "\"" | "'" <bytes> "'" ;
//<list>      :: "(" ( <sexp> | <space> )* ")" ;
//<tokenchar> :: <alpha> | <digit> | <punc> ;
//<space>     :: ( " " | "\t" | "\r" | "\n" )* ;
//<digit>     :: "0" ... "9" ;
//<alpha>     :: "A" ... "Z" | "a" ... "z" | <digit> ;
//<punc>      :: "-" | "." | "/" | "_" | ":" | "*" | "+" | "=" ;
//<comment>   :: ";" <bytes> -- until the end of line


ini_set('xdebug.max_nesting_level', 512);

echo $grammar . PHP_EOL;

$packrat = new DrSlump\Peg\Packrat\Dummy();
//$packrat = new DrSlump\Peg\Packrat\Standard();
//$packrat = new DrSlump\Peg\Packrat\Fixed(100);


define('PEG_DEBUG', false);


if (function_exists('xhprof_enable')) {
    xhprof_enable();
}

//$n = $grammar->parse("// fooOfoo");//1+2');
$mem = memory_get_usage(true);
$time = microtime(true);
//$n = $grammar->parse("( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( ( foo ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) ) )", $packrat);
$n = $grammar->parse("
(((S) (NP) (VP))
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((NP) Medicenter)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((NP) Medicenter)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((NP) Medicenter)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((VP) (V))
 ((VP) (V) (NP))
 ((V) died)
 ((V) employed)
 ((NP) nurses)
 ((NP) patients)
 ((NP) Medicenter)
 ((NP) Dr Chan))
");
$time = (microtime(true) - $time) . "s";
$mem = ceil(memory_get_usage(true) / 1024) . 'Kb';
echo "M: $mem / " . memory_get_peak_usage(true) . "\n";
echo "T: $time\n";


if (function_exists('xhprof_enable')) {
    $data = xhprof_disable();

    include_once "xhprof_lib/utils/xhprof_lib.php";
    include_once "xhprof_lib/utils/xhprof_runs.php";

    $xhprof = new XHProfRuns_Default();

    // Save the run under a namespace "xhprof".
    $run_id = $xhprof->save_run($data, "xhprof");
    echo "\nXHPROF: $run_id\n";
}




//M: 1792Kb / 1835008
//T: 0.036375045776367s

//M: 2048Kb / 2097152
//T: 0.039207935333252s


var_dump($n);


__halt_compiler();


For Alternation we check every branch atom looking if they start with a
string terminal.

    $allTerminals = true;
    $expected = array();
    foreach ($alt->atoms as $atom) {
        if (!($atom instanceof Atom\String)) {
            $allTerminals = false;
            break;
        }
        // If an expected is repeated disable the optimization
        if (in_array($atom->expected, $expected)) {
            $allTerminals = false;
            break;
        }
    }

    // Inject cut operator after each first terminal
    if ($allTerminals) {
        foreach ($alt->atoms as $atom) {
            $atom->cut();
        }
    }





