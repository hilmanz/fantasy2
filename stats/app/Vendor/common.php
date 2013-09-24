<?php
// Time format is UNIX timestamp or
  // PHP strtotime compatible strings
  function dateDiff($time1, $time2, $precision = 6) {
    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
      $time1 = strtotime($time1);
    }
    if (!is_int($time2)) {
      $time2 = strtotime($time2);
    }
 
    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
      $ttime = $time1;
      $time1 = $time2;
      $time2 = $ttime;
    }
 
    // Set up intervals and diffs arrays
    $intervals = array('year','month','day','hour','minute','second');
    $diffs = array();
 
    // Loop thru all intervals
    foreach ($intervals as $interval) {
      // Set default diff to 0
      $diffs[$interval] = 0;
      // Create temp time from time1 and interval
      $ttime = strtotime("+1 " . $interval, $time1);
      // Loop until temp time is smaller than time2
      while ($time2 >= $ttime) {
  $time1 = $ttime;
  $diffs[$interval]++;
  // Create new temp time from time1 and interval
  $ttime = strtotime("+1 " . $interval, $time1);
      }
    }
 
    $count = 0;
    $times = array();
    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
      // Break if we have needed precission
      if ($count >= $precision) {
  break;
      }
      // Add value and interval 
      // if value is bigger than 0
      if ($value > 0) {
  // Add s if value is not 1
  if ($value != 1) {
    $interval .= "s";
  }
  // Add value and interval to times array
  $times[] = $value . " " . $interval;
  $count++;
      }
    }
 
    // Return string with times
    return implode(", ", $times);
  }
  
  function subval_sort($a,$subkey) {
  foreach($a as $k=>$v) {
    $b[$k] = strtolower($v[$subkey]);
  }
  asort($b);
  foreach($b as $key=>$val) {
    $c[] = $a[$key];
  }
  return $c;
}
/**
 * a helper function to help sorting an array based on its key's value reversely
 * @param $a
 * @param $subkey
 */
function subval_rsort($a,$subkey) {
  foreach($a as $k=>$v) {
    $b[$k] = strtolower($v[$subkey]);
  }
  arsort($b);
  foreach($b as $key=>$val) {
    $c[] = $a[$key];
  }
  return $c;
}

function phpbb_email_hash($email)
{
  return sprintf('%u', crc32(strtolower($email))) . strlen($email);
}

// Parameters:
// $text = The text that you want to encrypt.
// $key = The key you're using to encrypt.
// $alg = The algorithm.
// $crypt = 1 if you want to crypt, or 0 if you want to decrypt.

function cryptare($text, $key, $alg, $crypt)
{
    $encrypted_data="";
    switch($alg)
    {
        case "3des":
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            break;
        case "cast-128":
            $td = mcrypt_module_open('cast-128', '', 'ecb', '');
            break;   
        case "gost":
            $td = mcrypt_module_open('gost', '', 'ecb', '');
            break;   
        case "rijndael-128":
            $td = mcrypt_module_open('rijndael-128', '', 'ecb', '');
            break;       
        case "twofish":
            $td = mcrypt_module_open('twofish', '', 'ecb', '');
            break;   
        case "arcfour":
            $td = mcrypt_module_open('arcfour', '', 'ecb', '');
            break;
        case "cast-256":
            $td = mcrypt_module_open('cast-256', '', 'ecb', '');
            break;   
        case "loki97":
            $td = mcrypt_module_open('loki97', '', 'ecb', '');
            break;       
        case "rijndael-192":
            $td = mcrypt_module_open('rijndael-192', '', 'ecb', '');
            break;
        case "saferplus":
            $td = mcrypt_module_open('saferplus', '', 'ecb', '');
            break;
        case "wake":
            $td = mcrypt_module_open('wake', '', 'ecb', '');
            break;
        case "blowfish-compat":
            $td = mcrypt_module_open('blowfish-compat', '', 'ecb', '');
            break;
        case "des":
            $td = mcrypt_module_open('des', '', 'ecb', '');
            break;
        case "rijndael-256":
            $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
            break;
        case "xtea":
            $td = mcrypt_module_open('xtea', '', 'ecb', '');
            break;
        case "enigma":
            $td = mcrypt_module_open('enigma', '', 'ecb', '');
            break;
        case "rc2":
            $td = mcrypt_module_open('rc2', '', 'ecb', '');
            break;   
        default:
            $td = mcrypt_module_open('blowfish', '', 'ecb', '');
            break;                                           
    }
   
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    mcrypt_generic_init($td, $key, $iv);
   
    if($crypt)
    {
        $encrypted_data = @mcrypt_generic($td, $text);
    }
    else
    {
        $encrypted_data = @mdecrypt_generic($td, $text);
    }
   
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
   
    return $encrypted_data;
} 
function convertBase64($str){
  $str = str_replace("=",".",$str);
  $str = str_replace("+","-",$str);
  $str = str_replace("/","_",$str);
  return $str;
}
function realBase64($str){
  $str = str_replace(".","=",$str);
  $str = str_replace("-","+",$str);
  $str = str_replace("_","/",$str);
  return $str;
}
function urlencode64($str){
  
  $key = md5('th1$ 1s my b0unty, n0t y0urs !');
  $hash = cryptare($str,$key,'des',1);
  $str = convertBase64(base64_encode($hash));
  return $str;
}
function urldecode64($str){
  global $CONFIG;
  $key = md5('th1$ 1s my b0unty, n0t y0urs !');
  $secret = base64_decode(realBase64($str));
  $str = cryptare($secret,$key,'des',0);
  return trim($str);
}
function encrypt_param($str){
  return urlencode64($str);
}
function decrypt_param($str){
  return urldecode64($str);
}
function to_secure_params($str){
  parse_str($str,$p);
  
  $json = json_encode($p);
  $str_hash = urlencode64($json);
  //print $str_hash;
  
  return $str_hash;
}
function reveal($str){
  $str = trim(urldecode64($str));
  return json_decode($str);
} 

/**
* Return unique id
* Borrowed from PHPBB
* @param string $extra additional entropy
*/
function unique_id($id)
{
  return $id;
}
function curlGet($url,$params,$cookie_file='',$timeout=15){
  if(count($params) > 0){
    $url .= "?".http_build_query($params);
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  if($cookie_file!=''){
    curl_setopt($ch,CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file); 
  }
  $response = curl_exec ($ch);
  $info = curl_getinfo($ch);
  if($info['http_code']==0){
    $response = json_encode(array('error'=>'unable to connect to web service !'));
  }
  curl_close ($ch);
  return $response;
}
function curlPost($url,$params,$cookie_file='',$timeout=15){
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  
  if($cookie_file!=''){
    curl_setopt($ch,CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file); 
  }
  $response = curl_exec ($ch);
  $info = curl_getinfo($ch);
  if($info['http_code']==0){
    $response = json_encode(array('error'=>'unable to connect to web service !'));
  }
  curl_close ($ch);
  return $response;
}


/*
================================================================================

EvalMath - PHP Class to safely evaluate math expressions
Copyright (C) 2005 Miles Kaufmann <http://www.twmagic.com/>

================================================================================

NAME
    EvalMath - safely evaluate math expressions

SYNOPSIS
    <?
      include('evalmath.class.php');
      $m = new EvalMath;
      // basic evaluation:
      $result = $m->evaluate('2+2');
      // supports: order of operation; parentheses; negation; built-in functions
      $result = $m->evaluate('-8(5/2)^2*(1-sqrt(4))-8');
      // create your own variables
      $m->evaluate('a = e^(ln(pi))');
      // or functions
      $m->evaluate('f(x,y) = x^2 + y^2 - 2x*y + 1');
      // and then use them
      $result = $m->evaluate('3*f(42,a)');
    ?>

DESCRIPTION
    Use the EvalMath class when you want to evaluate mathematical expressions
    from untrusted sources.  You can define your own variables and functions,
    which are stored in the object.  Try it, it's fun!

METHODS
    $m->evalute($expr)
        Evaluates the expression and returns the result.  If an error occurs,
        prints a warning and returns false.  If $expr is a function assignment,
        returns true on success.

    $m->e($expr)
        A synonym for $m->evaluate().

    $m->vars()
        Returns an associative array of all user-defined variables and values.

    $m->funcs()
        Returns an array of all user-defined functions.

PARAMETERS
    $m->suppress_errors
        Set to true to turn off warnings when evaluating expressions

    $m->last_error
        If the last evaluation failed, contains a string describing the error.
        (Useful when suppress_errors is on).

AUTHOR INFORMATION
    Copyright 2005, Miles Kaufmann.

LICENSE
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are
    met:

    1   Redistributions of source code must retain the above copyright
        notice, this list of conditions and the following disclaimer.
    2.  Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.
    3.  The name of the author may not be used to endorse or promote
        products derived from this software without specific prior written
        permission.

    THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
    IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
    INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
    SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
    HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
    STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
    ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.

*/

/**
 * This class was heavily modified in order to get usefull spreadsheet emulation ;-)
 * skodak
 *
 */

class EvalMath {

    /** @var string Pattern used for a valid function or variable name. Note, var and func names are case insensitive.*/
    private static $namepat = '[a-z][a-z0-9_]*';

    var $suppress_errors = false;
    var $last_error = null;

    var $v = array(); // variables (and constants)
    var $f = array(); // user-defined functions
    var $vb = array(); // constants
    var $fb = array(  // built-in functions
        'sin','sinh','arcsin','asin','arcsinh','asinh',
        'cos','cosh','arccos','acos','arccosh','acosh',
        'tan','tanh','arctan','atan','arctanh','atanh',
        'sqrt','abs','ln','log','exp','floor','ceil');

    var $fc = array( // calc functions emulation
        'average'=>array(-1), 'max'=>array(-1),  'min'=>array(-1),
        'mod'=>array(2),      'pi'=>array(0),    'power'=>array(2),
        'round'=>array(1, 2), 'sum'=>array(-1), 'rand_int'=>array(2),
        'rand_float'=>array(0));

    var $allowimplicitmultiplication;

    function EvalMath($allowconstants = false, $allowimplicitmultiplication = false) {
        if ($allowconstants){
            $this->v['pi'] = pi();
            $this->v['e'] = exp(1);
        }
        $this->allowimplicitmultiplication = $allowimplicitmultiplication;
    }

    function e($expr) {
        return $this->evaluate($expr);
    }

    function evaluate($expr) {
        $this->last_error = null;
        $expr = trim($expr);
        if (substr($expr, -1, 1) == ';') $expr = substr($expr, 0, strlen($expr)-1); // strip semicolons at the end
        //===============
        // is it a variable assignment?
        if (preg_match('/^\s*('.self::$namepat.')\s*=\s*(.+)$/', $expr, $matches)) {
            if (in_array($matches[1], $this->vb)) { // make sure we're not assigning to a constant
                return $this->trigger(get_string('cannotassigntoconstant', 'mathslib', $matches[1]));
            }
            if (($tmp = $this->pfx($this->nfx($matches[2]))) === false) return false; // get the result and make sure it's good
            $this->v[$matches[1]] = $tmp; // if so, stick it in the variable array
            return $this->v[$matches[1]]; // and return the resulting value
        //===============
        // is it a function assignment?
        } elseif (preg_match('/^\s*('.self::$namepat.')\s*\(\s*('.self::$namepat.'(?:\s*,\s*'.self::$namepat.')*)\s*\)\s*=\s*(.+)$/', $expr, $matches)) {
            $fnn = $matches[1]; // get the function name
            if (in_array($matches[1], $this->fb)) { // make sure it isn't built in
                return $this->trigger(get_string('cannotredefinebuiltinfunction', 'mathslib', $matches[1]));
            }
            $args = explode(",", preg_replace("/\s+/", "", $matches[2])); // get the arguments
            if (($stack = $this->nfx($matches[3])) === false) return false; // see if it can be converted to postfix
            for ($i = 0; $i<count($stack); $i++) { // freeze the state of the non-argument variables
                $token = $stack[$i];
                if (preg_match('/^'.self::$namepat.'$/', $token) and !in_array($token, $args)) {
                    if (array_key_exists($token, $this->v)) {
                        $stack[$i] = $this->v[$token];
                    } else {
                        return $this->trigger(get_string('undefinedvariableinfunctiondefinition', 'mathslib', $token));
                    }
                }
            }
            $this->f[$fnn] = array('args'=>$args, 'func'=>$stack);
            return true;
        //===============
        } else {
            return $this->pfx($this->nfx($expr)); // straight up evaluation, woo
        }
    }

    function vars() {
        return $this->v;
    }

    function funcs() {
        $output = array();
        foreach ($this->f as $fnn=>$dat)
            $output[] = $fnn . '(' . implode(',', $dat['args']) . ')';
        return $output;
    }

    /**
     * @param string $name
     * @return boolean Is this a valid var or function name?
     */
    public static function is_valid_var_or_func_name($name){
        return preg_match('/'.self::$namepat.'$/iA', $name);
    }

    //===================== HERE BE INTERNAL METHODS ====================\\

    // Convert infix to postfix notation
    function nfx($expr) {

        $index = 0;
        $stack = new EvalMathStack;
        $output = array(); // postfix form of expression, to be passed to pfx()
        $expr = trim(strtolower($expr));

        $ops   = array('+', '-', '*', '/', '^', '_');
        $ops_r = array('+'=>0,'-'=>0,'*'=>0,'/'=>0,'^'=>1); // right-associative operator?
        $ops_p = array('+'=>0,'-'=>0,'*'=>1,'/'=>1,'_'=>1,'^'=>2); // operator precedence

        $expecting_op = false; // we use this in syntax-checking the expression
                               // and determining when a - is a negation

        if (preg_match("/[^\w\s+*^\/()\.,-]/", $expr, $matches)) { // make sure the characters are all good
            return $this->trigger(get_string('illegalcharactergeneral', 'mathslib', $matches[0]));
        }

        while(1) { // 1 Infinite Loop ;)
            $op = substr($expr, $index, 1); // get the first character at the current index
            // find out if we're currently at the beginning of a number/variable/function/parenthesis/operand
            $ex = preg_match('/^('.self::$namepat.'\(?|\d+(?:\.\d*)?(?:(e[+-]?)\d*)?|\.\d+|\()/', substr($expr, $index), $match);
            //===============
            if ($op == '-' and !$expecting_op) { // is it a negation instead of a minus?
                $stack->push('_'); // put a negation on the stack
                $index++;
            } elseif ($op == '_') { // we have to explicitly deny this, because it's legal on the stack
                return $this->trigger(get_string('illegalcharacterunderscore', 'mathslib')); // but not in the input expression
            //===============
            } elseif ((in_array($op, $ops) or $ex) and $expecting_op) { // are we putting an operator on the stack?
                if ($ex) { // are we expecting an operator but have a number/variable/function/opening parethesis?
                    if (!$this->allowimplicitmultiplication){
                        return $this->trigger(get_string('implicitmultiplicationnotallowed', 'mathslib'));
                    } else {// it's an implicit multiplication
                        $op = '*';
                        $index--;
                    }
                }
                // heart of the algorithm:
                while($stack->count > 0 and ($o2 = $stack->last()) and in_array($o2, $ops) and ($ops_r[$op] ? $ops_p[$op] < $ops_p[$o2] : $ops_p[$op] <= $ops_p[$o2])) {
                    $output[] = $stack->pop(); // pop stuff off the stack into the output
                }
                // many thanks: http://en.wikipedia.org/wiki/Reverse_Polish_notation#The_algorithm_in_detail
                $stack->push($op); // finally put OUR operator onto the stack
                $index++;
                $expecting_op = false;
            //===============
            } elseif ($op == ')' and $expecting_op) { // ready to close a parenthesis?
                while (($o2 = $stack->pop()) != '(') { // pop off the stack back to the last (
                    if (is_null($o2)) return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                    else $output[] = $o2;
                }
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches)) { // did we just close a function?
                    $fnn = $matches[1]; // get the function name
                    $arg_count = $stack->pop(); // see how many arguments there were (cleverly stored on the stack, thank you)
                    $fn = $stack->pop();
                    $output[] = array('fn'=>$fn, 'fnn'=>$fnn, 'argcount'=>$arg_count); // send function to output
                    if (in_array($fnn, $this->fb)) { // check the argument count
                        if($arg_count > 1) {
                            $a= new stdClass();
                            $a->expected = 1;
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } elseif (array_key_exists($fnn, $this->fc)) {
                        $counts = $this->fc[$fnn];
                        if (in_array(-1, $counts) and $arg_count > 0) {}
                        elseif (!in_array($arg_count, $counts)) {
                            $a= new stdClass();
                            $a->expected = implode('/',$this->fc[$fnn]);
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } elseif (array_key_exists($fnn, $this->f)) {
                        if ($arg_count != count($this->f[$fnn]['args'])) {
                            $a= new stdClass();
                            $a->expected = count($this->f[$fnn]['args']);
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } else { // did we somehow push a non-function on the stack? this should never happen
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                }
                $index++;
            //===============
            } elseif ($op == ',' and $expecting_op) { // did we just finish a function argument?
                while (($o2 = $stack->pop()) != '(') {
                    if (is_null($o2)) return $this->trigger(get_string('unexpectedcomma', 'mathslib')); // oops, never had a (
                    else $output[] = $o2; // pop the argument expression stuff and push onto the output
                }
                // make sure there was a function
                if (!preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches))
                    return $this->trigger(get_string('unexpectedcomma', 'mathslib'));
                $stack->push($stack->pop()+1); // increment the argument count
                $stack->push('('); // put the ( back on, we'll need to pop back to it again
                $index++;
                $expecting_op = false;
            //===============
            } elseif ($op == '(' and !$expecting_op) {
                $stack->push('('); // that was easy
                $index++;
                $allow_neg = true;
            //===============
            } elseif ($ex and !$expecting_op) { // do we now have a function/variable/number?
                $expecting_op = true;
                $val = $match[1];
                if (preg_match('/^('.self::$namepat.')\($/', $val, $matches)) { // may be func, or variable w/ implicit multiplication against parentheses...
                    if (in_array($matches[1], $this->fb) or array_key_exists($matches[1], $this->f) or array_key_exists($matches[1], $this->fc)) { // it's a func
                        $stack->push($val);
                        $stack->push(1);
                        $stack->push('(');
                        $expecting_op = false;
                    } else { // it's a var w/ implicit multiplication
                        $val = $matches[1];
                        $output[] = $val;
                    }
                } else { // it's a plain old var or num
                    $output[] = $val;
                }
                $index += strlen($val);
            //===============
            } elseif ($op == ')') {
                //it could be only custom function with no params or general error
                if ($stack->last() != '(' or $stack->last(2) != 1) return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(3), $matches)) { // did we just close a function?
                    $stack->pop();// (
                    $stack->pop();// 1
                    $fn = $stack->pop();
                    $fnn = $matches[1]; // get the function name
                    $counts = $this->fc[$fnn];
                    if (!in_array(0, $counts)){
                        $a= new stdClass();
                        $a->expected = $this->fc[$fnn];
                        $a->given = 0;
                        return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                    }
                    $output[] = array('fn'=>$fn, 'fnn'=>$fnn, 'argcount'=>0); // send function to output
                    $index++;
                    $expecting_op = true;
                } else {
                    return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                }
            //===============
            } elseif (in_array($op, $ops) and !$expecting_op) { // miscellaneous error checking
                return $this->trigger(get_string('unexpectedoperator', 'mathslib', $op));
            } else { // I don't even want to know what you did to get here
                return $this->trigger(get_string('anunexpectederroroccured', 'mathslib'));
            }
            if ($index == strlen($expr)) {
                if (in_array($op, $ops)) { // did we end with an operator? bad.
                    return $this->trigger(get_string('operatorlacksoperand', 'mathslib', $op));
                } else {
                    break;
                }
            }
            while (substr($expr, $index, 1) == ' ') { // step the index past whitespace (pretty much turns whitespace
                $index++;                             // into implicit multiplication if no operator is there)
            }

        }
        while (!is_null($op = $stack->pop())) { // pop everything off the stack and push onto output
            if ($op == '(') return $this->trigger(get_string('expectingaclosingbracket', 'mathslib')); // if there are (s on the stack, ()s were unbalanced
            $output[] = $op;
        }
        return $output;
    }

    // evaluate postfix notation
    function pfx($tokens, $vars = array()) {

        if ($tokens == false) return false;

        $stack = new EvalMathStack;

        foreach ($tokens as $token) { // nice and easy

            // if the token is a function, pop arguments off the stack, hand them to the function, and push the result back on
            if (is_array($token)) { // it's a function!
                $fnn = $token['fnn'];
                $count = $token['argcount'];
                if (in_array($fnn, $this->fb)) { // built-in function:
                    if (is_null($op1 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    $fnn = preg_replace("/^arc/", "a", $fnn); // for the 'arc' trig synonyms
                    if ($fnn == 'ln') $fnn = 'log';
                    eval('$stack->push(' . $fnn . '($op1));'); // perfectly safe eval()
                } elseif (array_key_exists($fnn, $this->fc)) { // calc emulation function
                    // get args
                    $args = array();
                    for ($i = $count-1; $i >= 0; $i--) {
                        if (is_null($args[] = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $res = call_user_func_array(array('EvalMathFuncs', $fnn), array_reverse($args));
                    if ($res === FALSE) {
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $stack->push($res);
                } elseif (array_key_exists($fnn, $this->f)) { // user function
                    // get args
                    $args = array();
                    for ($i = count($this->f[$fnn]['args'])-1; $i >= 0; $i--) {
                        if (is_null($args[$this->f[$fnn]['args'][$i]] = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $stack->push($this->pfx($this->f[$fnn]['func'], $args)); // yay... recursion!!!!
                }
            // if the token is a binary operator, pop two values off the stack, do the operation, and push the result back on
            } elseif (in_array($token, array('+', '-', '*', '/', '^'), true)) {
                if (is_null($op2 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                if (is_null($op1 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                switch ($token) {
                    case '+':
                        $stack->push($op1+$op2); break;
                    case '-':
                        $stack->push($op1-$op2); break;
                    case '*':
                        $stack->push($op1*$op2); break;
                    case '/':

                        if ($op2 == 0) return $this->trigger(get_string('divisionbyzero', 'mathslib'));
                        $stack->push($op1/$op2); break;
                    case '^':
                        $stack->push(pow($op1, $op2)); break;
                }
            // if the token is a unary operator, pop one value off the stack, do the operation, and push it back on
            } elseif ($token == "_") {
                $stack->push(-1*$stack->pop());
            // if the token is a number or variable, push it on the stack
            } else {
                if (is_numeric($token)) {
                    $stack->push($token);
                } elseif (array_key_exists($token, $this->v)) {
                    $stack->push($this->v[$token]);
                } elseif (array_key_exists($token, $vars)) {
                    $stack->push($vars[$token]);
                } else {
                    return $this->trigger(get_string('undefinedvariable', 'mathslib', $token));
                }
            }
        }
        // when we're out of tokens, the stack should have a single element, the final result
        if ($stack->count != 1) return $this->trigger(get_string('internalerror', 'mathslib'));
        return $stack->pop();
    }

    // trigger an error, but nicely, if need be
    function trigger($msg) {

        $this->last_error = $msg;
        if (!$this->suppress_errors) trigger_error($msg, E_USER_WARNING);
        return false;
    }

}

// for internal use
class EvalMathStack {

    var $stack = array();
    var $count = 0;

    function push($val) {
        $this->stack[$this->count] = $val;
        $this->count++;
    }

    function pop() {
        if ($this->count > 0) {
            $this->count--;
            return $this->stack[$this->count];
        }
        return null;
    }

    function last($n=1) {
        if ($this->count - $n >= 0) {
            return $this->stack[$this->count-$n];
        }
        return null;
    }
}


// spreadsheet functions emulation
class EvalMathFuncs {

    static function average() {
        $args = func_get_args();
        return (call_user_func_array(array('self', 'sum'), $args) / count($args));
    }

    static function max() {
        $args = func_get_args();
        $res = array_pop($args);
        foreach($args as $a) {
            if ($res < $a) {
                $res = $a;
            }
        }
        return $res;
    }

    static function min() {
        $args = func_get_args();
        $res = array_pop($args);
        foreach($args as $a) {
            if ($res > $a) {
                $res = $a;
            }
        }
        return $res;
    }

    static function mod($op1, $op2) {
        return $op1 % $op2;
    }

    static function pi() {
        return pi();
    }

    static function power($op1, $op2) {
        return pow($op1, $op2);
    }

    static function round($val, $precision = 0) {
        return round($val, $precision);
    }

    static function sum() {
        $args = func_get_args();
        $res = 0;
        foreach($args as $a) {
           $res += $a;
        }
        return $res;
    }

    protected static $randomseed = null;

    static function set_random_seed($randomseed) {
        self::$randomseed = $randomseed;
    }

    static function get_random_seed() {
        if (is_null(self::$randomseed)){
            return microtime();
        } else {
            return self::$randomseed;
        }
    }

    static function rand_int($min, $max){
        if ($min >= $max) {
            return false; //error
        }
        $noofchars = ceil(log($max + 1 - $min, '16'));
        $md5string = md5(self::get_random_seed());
        $stringoffset = 0;
        do {
            while (($stringoffset + $noofchars) > strlen($md5string)){
                $md5string .= md5($md5string);
            }
            $randomno = hexdec(substr($md5string, $stringoffset, $noofchars));
            $stringoffset += $noofchars;
        } while (($min + $randomno) > $max);
        return $min + $randomno;
    }

    static function rand_float() {
        $randomvalues = unpack('v', md5(self::get_random_seed(), true));
        return array_shift($randomvalues) / 65536;
    }
}
function get_string($name){
  return $name;
}