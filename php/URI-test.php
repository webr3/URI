<?php
/**
 * This file was developed by Nathan <nathan@webr3.org>
 *
 * Created:     nathan - 24 Sep 2010 19:53:20
 * Modified:    SVN: $Id$
 * PHP Version: 5.1.6+
 *
 * @package   @project.name@
 * @author    Nathan <nathan@webr3.org>
 * @version   SVN: $Revision$
 */

require_once 'URI.php';

function assertEqual( $left , $right , $message ) {
	if(is_object($left)) $left = '' . $left;
	if(is_object($right)) $right = '' . $right;
	if( $left !== $right ) {
		throw new Exception($message . ': ' . $left . ' !== ' . $right);
	}
}

function runTests() {
	$test = new URI('http://frankety:234asf@webr3.org/a/b/cc/d/../.././nathan?egg=shell&norm=bob#s/../x');
	$message = 'URI parsing and components';
	assertEqual( $test->scheme , 'http' , $message );
	assertEqual( $test->userinfo , 'frankety:234asf' , $message );
	assertEqual( $test->host , 'webr3.org' , $message );
	assertEqual( $test->path , '/a/b/cc/d/../.././nathan' , $message );
	assertEqual( $test->query , '?egg=shell&norm=bob' , $message );
	assertEqual( $test->fragment , '#s/../x' , $message );
	$message = 'URI Defrag';
	assertEqual( $test->defrag()->equals('http://frankety:234asf@webr3.org/a/b/cc/d/../.././nathan?egg=shell&norm=bob'), true , $message );
	$message = 'Absolute URI functions';
	assertEqual( $test->isAbsolute() , false , $message );
	$test = $test->toAbsolute();
	assertEqual( $test->isAbsolute() , true , $message );
	assertEqual( $test , 'http://frankety:234asf@webr3.org/a/b/nathan?egg=shell&norm=bob' , $message );
	assertEqual( $test->path , '/a/b/nathan' , $message );
	$message = 'Relative URI Reference Resolution';
	$test = new URI('http://a/b/c/d;p?q');
	assertEqual( $test->resolveReference('g:h') , 'g:h', $message );
	assertEqual( $test->resolveReference('g') , 'http://a/b/c/g', $message );
	assertEqual( $test->resolveReference('./g') , 'http://a/b/c/g', $message );
	assertEqual( $test->resolveReference('g/') , 'http://a/b/c/g/', $message );
	assertEqual( $test->resolveReference('/g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('//g') , 'http://g', $message );
	assertEqual( $test->resolveReference('?y') , 'http://a/b/c/d;p?y', $message );
	assertEqual( $test->resolveReference('g?y') , 'http://a/b/c/g?y', $message );
	assertEqual( $test->resolveReference('#s') , 'http://a/b/c/d;p?q#s', $message );
	assertEqual( $test->resolveReference('g#s') , 'http://a/b/c/g#s', $message );
	assertEqual( $test->resolveReference('g?y#s') , 'http://a/b/c/g?y#s', $message );
	assertEqual( $test->resolveReference(';x') , 'http://a/b/c/;x', $message );
	assertEqual( $test->resolveReference('g;x') , 'http://a/b/c/g;x', $message );
	assertEqual( $test->resolveReference('g;x?y#s') , 'http://a/b/c/g;x?y#s', $message );
	assertEqual( $test->resolveReference('') , 'http://a/b/c/d;p?q', $message );
	assertEqual( $test->resolveReference('.') , 'http://a/b/c/', $message );
	assertEqual( $test->resolveReference('./') , 'http://a/b/c/', $message );
	assertEqual( $test->resolveReference('..') , 'http://a/b/', $message );
	assertEqual( $test->resolveReference('../') , 'http://a/b/', $message );
	assertEqual( $test->resolveReference('../g') , 'http://a/b/g', $message );
	assertEqual( $test->resolveReference('../..') , 'http://a/', $message );
	assertEqual( $test->resolveReference('../../') , 'http://a/', $message );
	assertEqual( $test->resolveReference('../../g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('../../../g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('../../../../g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('/./g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('/../g') , 'http://a/g', $message );
	assertEqual( $test->resolveReference('g.') , 'http://a/b/c/g.', $message );
	assertEqual( $test->resolveReference('.g') , 'http://a/b/c/.g', $message );
	assertEqual( $test->resolveReference('g..') , 'http://a/b/c/g..', $message );
	assertEqual( $test->resolveReference('..g') , 'http://a/b/c/..g', $message );
	assertEqual( $test->resolveReference('./../g') , 'http://a/b/g', $message );
	assertEqual( $test->resolveReference('./g/.') , 'http://a/b/c/g/', $message );
	assertEqual( $test->resolveReference('g/./h') , 'http://a/b/c/g/h', $message );
	assertEqual( $test->resolveReference('g/../h') , 'http://a/b/c/h', $message );
	assertEqual( $test->resolveReference('g;x=1/./y') , 'http://a/b/c/g;x=1/y', $message );
	assertEqual( $test->resolveReference('g;x=1/../y') , 'http://a/b/c/y', $message );
	assertEqual( $test->resolveReference('g?y/./x') , 'http://a/b/c/g?y/./x', $message );
	assertEqual( $test->resolveReference('g?y/../x') , 'http://a/b/c/g?y/../x', $message );
	assertEqual( $test->resolveReference('g#s/./x') , 'http://a/b/c/g#s/./x', $message );
	assertEqual( $test->resolveReference('g#s/../x') , 'http://a/b/c/g#s/../x', $message );
	echo 'all tests passed';
}

runTests();