<?php

include_once 'library/DrSlump/SimpleParser.php';

include_once 'library/DrSlump/SimpleParser/Node.php';
include_once 'library/DrSlump/SimpleParser/Node/Child.php';
include_once 'library/DrSlump/SimpleParser/Node/NonTerminal.php';
include_once 'library/DrSlump/SimpleParser/Node/Terminal.php';
include_once 'library/DrSlump/SimpleParser/Node/Root.php';

include_once 'library/DrSlump/SimpleParser/Atom.php';
include_once 'library/DrSlump/SimpleParser/Atom/Ahead.php';
include_once 'library/DrSlump/SimpleParser/Atom/Alternates.php';
include_once 'library/DrSlump/SimpleParser/Atom/RegExp.php';
include_once 'library/DrSlump/SimpleParser/Atom/Repeat.php';
include_once 'library/DrSlump/SimpleParser/Atom/Sequence.php';
include_once 'library/DrSlump/SimpleParser/Atom/Chained.php';
include_once 'library/DrSlump/SimpleParser/Atom/String.php';
include_once 'library/DrSlump/SimpleParser/Atom/Reference.php';

include_once 'library/DrSlump/SimpleParser/Grammar.php';
include_once 'library/DrSlump/SimpleParser/Source.php';

include_once 'library/DrSlump/SimpleParser/dsl.php';

use DrSlump\SimpleParser\Grammar;


class MyGrammar extends Grammar
{
    protected function rules()
    {
        //$this['term'] = alt(
        //    ref('factor')->str('+')->ref('term'),
        //    ref('factor')->str('-')->ref('term'),
        //    ref('term')
        //);
        //
        //$this['term'] = alt(
        //    seq( ref('factor'), '+', ref('term') ),
        //    seq( ref('factor'), '-', ref('term') ),
        //    seq( ref('factor') )
        //);

        $this['factor'] = rex('[0-9]');

        $this['term'] = seq(
            str('//'),
            seq( rex('\n')->absent, any() )->repeat
        );

        $this['term'] = str('//') -> rex('[^\n]')->repeat;

        $this['term'] = seq( '//', rex('[^\n]')->repeat ->as('comment') );


        $this['root'] = $this['term'];
    }
}

$atom = new \DrSlump\SimpleParser\Atom\RegExp('foo');

$grammar = new MyGrammar();

var_dump($grammar);

$grammar->parse("// fooOfoo");//1+2');

Root:
  - comment (fooOfoo)

1 + 2 + 3

Root:
  - left:
    - int (1)
  - op (+)
  - right:
    - left:
      - int (2)
    - op:
      - op (+)
    - right:
      - int (3)

foo(1, 2)

Root:
  - func (foo)
  - args
    - int(1)
    - int(2)

exit;




class Grammar extends DrSlump\TreeTop\Grammar
{
    // @rule multitive ( '+' multitive )*
    function additive() {
        
    }
    
    // @rule primary ( '*' primary )*
    function multitive() {}
    
    // @rule '(' additive ')' / number
    function primary() {}
    
    // @rule '-'? [1-9] [0-9]*
    function number() {}    
    
}

$grammar = new Grammar();
$grammar->alternate('expr', array(
    array('<term>', '*', '<expr>'),
    array('<term>', '/', '<expr>'),
    array('<term>')
));

$rule = $grammar->rule('expr');
$rule->seq('<term>', '*', '<expr>');
$rule->seq('<term>', '/', '<expr>');
$rule->seq('<term>');

$rule->term('term')->text('*')->rule('expr');
$rule->term('term')->text_ahead('*');
$rule->term_repeat('term')->text_not_ahead('/');
$rule->term_maybe('space')->regex('[0-9]')->rule_maybe('space');
$rule->term('space')->repeat
     ->regex('[0-9]')->maybe

$rule = $grammar->rule('term');
$rule->seq('<factor>', '+', '<term>')
     ->seq('<factor>', '-', '<term>')
     ->seq('<factor>');

$rule = $grammar->rule('factor');
$rule->seq('/[0-9]/')
     ->seq('(', '<expr>', ')');

$rule = $grammar->rule('number');
$rule->str('-')->maybe
     ->alt(
        str('0'),
        match('[1-9]')->rule('digit')->repeat
     )
     ->alt(
        str('.'),
        rule('digit')->repeat(1)
     )->maybe
     ->alt(
        match('[eE]']),
        alt(
          str('+'),
          str('-')
        )->maybe,
        rule('digit')->repeat(1)
     )->maybe;
    
rule('number', 
    str('-')->maybe,
    alt(
        '0',
        exp('/[1-9]/')->digit()->repeat     
    ),
    alt(
        '.',
        ref('digit')->repeat(1)
    )->maybe,
    alt(
        match('[eE]')->alt(
            str('+'),
            str('-')
        )->maybe,
        ref('digit')->repeat(1)
    )
)

rule('number', 
    str('-')->maybe,
    alt(
      '0',
      seq( rex('[1-9]'), ref('digit')->repeat )
    )->as('int'),
    alt( '.', atm('digit')->once )->maybe )->as('decimal'),
    alt(
      rex('[eE]'), 
      alt( str('+'), str('-') )->maybe,
      ref('digit')->once      
    )->as('exponent')
);

rule('comment',
    str('//'),
    seq(
        ref('newline')->absent,
        any()
    )->repeat
)->as('Line comment');


$g = new Grammar();

// Single character rules
$g['space'] = rex('\s');
$g['space?'] = ref('space')->maybe;

$g['lparen'] = str('(')->ref('space?');
$g['rparen'] = str(')')->ref('space?');
$g['comma']  = str(',')->ref('space?');

// Things
$g['int:integer'] = seq(
    rex('[0-9]')->once,
    ref('space?')->ignore
);
$g['identifier'] = rex('[a-z]').once;
$g['operator'] = seq('+', '<space?>');

// Grammar parts
$g['sum'] = seq(
    ref('integer')   ->as('left'),
    ref('operator')  ->as('op'),
    ref('expression')->as('right')
);
$g['arglist'] = seq(
    ref('expression'),
    seq('<comma>', '<expression>')->repeat
);
$g['funcall'] = seq(
    '<funcall:identifier>',
    '<lparen>',
    '<:arglist>',
    '<rparen>'
);

$g['expression'] = alt(
    $g['funcall'], 
    $g['sum'], 
    $g['integer']
);

$g['funcall'] -> identifier('func') -> lparen() -> arglist('args') -> rparen()


$g->root('expression'); 
    

// Modify the node (replace it by returning one)
// If FALSE is returned then rollback the match
$g['int'] = rex('[0-9]')->once->apply(function($n){
    $n->setValue( (int)$n->getValue() );
});

// Modify the value
$g['int'] = rex('[0-9]')->once->value(function($v){
    return (int)$v;
});

// Use a custom node class for this atom
$g['int'] = rex('[0-9]')->once->node('MyNode');




use DrSlump\SimpleParser as P;

$g['sum'] = P::seq(
    P::ref('integer')->as('left'),
    P::ref('operator')->as('op'),
    P::ref('expression')->as('right')
);


class MyGrammar extends Grammar
{
    protected function rules()
    {
        $this['spaces'] = rex('\s')->oneOrMore;
        $this['spaces?'] = ref('spaces')->maybe;
        $this['spaces_'] = $this['spaces?'];
        
        $this['comma'] = ref('spaces?') -> str(',') -> ref('spaces?');
        //$this['comma'] -> spaces_() -> str(',') -> spaces_();         
        $this['digit'] = '/[0-9]/';
        
        $this['number'] = seq(
            str('-')->maybe,
            alt(
                str('0'),
                rex('[1-9]') -> digit()->repeat
            ),
            seq( 
                str('.') -> digit()->once 
            )->maybe,
            seq(
                rex('[eE]') -> alt( '+', '-' )->maybe -> digit()->once
            )->maybe
        )->as('number');
        
        $this['string'] = seq(
            '"', 
            alt(
                str('\\') -> any(),
                str('"')->absent -> any()
            )->repeat ->as('string'),
            '"'
        );
        
        $this['array'] = seq(
            '[', '<spaces?>', 
            seq( 
                '<value>', 
                seq( '<comma>', '<value>' )->repeat 
            )->maybe ->as('array'),
            '<spaces?>', ']'
        );
        
        $this['object'] = seq(
            '{', '<spaces?>',
            seq(
                '<entry>', 
                seq( '<comma>', '<entry>' )->repeat 
            )->maybe ->as('object'),
            '<spaces?>', '}'            
        );
        
        $this['value'] = alt(
            '<string>', '<number>', '<object>', '<array>',
            str('true')  ->as('true'),
            str('false') ->as('false'),
            str('null')  ->as('null')
        );
        
        $this['entry'] = seq(
            '<key:string>', 
            '<spaces?>', ':', '<spaces?>',
            '<val:value>'
        )->as('entry');
        
        $this['entry'] = seq(
            ref('string')->as('key'),
            '<spaces?>', ':', '<spaces?>',
            ref('value')
                ->cb(function($s){ 
                    return "<$s>"; 
                })
                ->as('val')
        )->as('entry');
        
        
        
        //$this['entry'] -> string('key') -> spaces_() -> str(':') -> spaces_() -> value('val');
        
        $this['attribute'] = alt(
            '<entry>',
            '<value>'
        )->as('attribute');
        
        $this['root'] = seq( '<spaces?>', '<value>', '<spaces?>' );
        
        // Import auxiliary grammars
        //$rule['string'] = new MyStringGrammar();
    }
}
   

alt -> Alternative
seq -> Sequence
rex -> RegExp
rexi -> RegExp case-insentive
str -> String
stri -> String case-insensitive
ref -> Entity
any -> any character -> /./
not -> atom->absent   (not preficate)
has -> atom->present  (and predicate)

->maybe  -> ?
->optional -> ?
->repeat -> *
->many   -> *
->once   -> +
->many1  -> +

->ignore -> consume matching input but not generate AST

->absent -> negative look ahead
->present -> positive look ahead
->as('foo')  -> Marks an atom as important by giving it a name






$grammar->root('expr');




function(){
    return 
})


regexp expr {
    <term> '*' <expr> | 
    <term> '/' <expr> |
    <term>
}
$g->rule('expr', alt(
  seq( ref('term'), '*', ref('expr') ),
  seq( ref('term'), '/', ref('expr') ),
  ref('term')  
));

regexp term {
    <factor> '+' <term> |
    <factor> '-' <term> |
    <factor>
}
$g->rule('term')->alt(
  ref('factor')->str('*')->ref('expr'),
  seq('<factor>', '/', '<expr>'),
  ref('term')
);

regexp factor {
    [0-9]+ | 
    '(' <expr> ')'
}
$g->rule('factor')->alt(
    '/[0-9]+/',
    str('(')->ref('expr')->str(')')
);




