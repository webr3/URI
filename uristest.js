/**
 * @fileOverview
 * 
 * URI Tests
 * - Test suite can be launched by running URI.Test();
 * 
 * @author Nathan <http://webr3.org/nathan#me>
 * @version 2010-06-22T16:55:00Z 
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 * 
 * source: <http://github.com/webr3/URI>
 * To the extent possible under law, <http://webr3.org/nathan#me>
 * has waived all copyright and related or neighboring rights to
 * this work.
 */

// node require URI if avail
if( typeof require != 'undefined' ) {
	try { var URI = require('./uris'); } catch( e ) {}
}

/**
 * URI.Test
 * Test suite for URI functionality
 * 
 * @return void
 */
(function() {
	// try to use node.js internal assert lib
	if( typeof require != 'undefined' ) {
		try {
			var assert = require('assert');
		} catch( e ) {
			
		}
	}
	// load custom assert lib
	if( typeof assert == 'undefined' ) {
		var assert = {};
		assert.AssertionError = function(options) {
			this.name = "AssertionError";
			this.message = options.message;
			this.actual = options.actual;
			this.expected = options.expected;
			this.operator = options.operator;
			this.value = this.name + (', ' + this.message + ': ' || ': ') +
				this.actual + ' ' + this.operator + ' ' + this.expected;
		};
		assert.AssertionError.prototype = new String;
		assert.AssertionError.prototype.toString = function() { return this.value.toString(); };
		assert.AssertionError.prototype.valueOf = function() { return this.value; };
		assert.fail = function(actual, expected, message, operator) {
			throw new assert.AssertionError({
				message: message,
				actual: actual,
				expected: expected,
				operator: operator
			});
		};
		assert.equal = function(actual, expected, message) {
			if (actual != expected) assert.fail(actual, expected, message, "==");
		};
	}
	// Test Suite
	var test = new URI('http://frankety:234asf@webr3.org/a/b/cc/d/../.././nathan?egg=shell&norm=bob#s/../x');
	var message = 'URI parsing and components';
	assert.equal( test.scheme() , 'http:' , message );
	assert.equal( test.heirpart() , '//frankety:234asf@webr3.org/a/b/cc/d/../.././nathan' , message );
	assert.equal( test.heirpart().authority() , 'frankety:234asf@webr3.org' , message );
	assert.equal( test.heirpart().authority().userinfo() , 'frankety:234asf' , message );
	assert.equal( test.heirpart().authority().host() , 'webr3.org' , message );
	assert.equal( test.heirpart().path() , '/a/b/cc/d/../.././nathan' , message );
	assert.equal( test.querystring() , '?egg=shell&norm=bob' , message );
	assert.equal( test.fragment() , '#s/../x' , message );
	message = 'URI Defrag';
	assert.equal( test.defrag() , 'http://frankety:234asf@webr3.org/a/b/cc/d/../.././nathan?egg=shell&norm=bob' , message );
	message = 'Absolute URI functions';
	assert.equal( test.isAbsolute() , false , message );
	test = test.toAbsolute();
	assert.equal( test.isAbsolute() , true , message );
	assert.equal( test , 'http://frankety:234asf@webr3.org/a/b/nathan?egg=shell&norm=bob' , message );
	assert.equal( test.heirpart().path() , '/a/b/nathan' , message );
	message = 'Relative URI Reference Resolution';
	test = new URI('http://a/b/c/d;p?q');
	assert.equal( test.resolveReference('g:h') , 'g:h', message );
	assert.equal( test.resolveReference('g') , 'http://a/b/c/g', message );
	assert.equal( test.resolveReference('./g') , 'http://a/b/c/g', message );
	assert.equal( test.resolveReference('g/') , 'http://a/b/c/g/', message );
	assert.equal( test.resolveReference('/g') , 'http://a/g', message );
	assert.equal( test.resolveReference('//g') , 'http://g', message );
	assert.equal( test.resolveReference('?y') , 'http://a/b/c/d;p?y', message );
	assert.equal( test.resolveReference('g?y') , 'http://a/b/c/g?y', message );
	assert.equal( test.resolveReference('#s') , 'http://a/b/c/d;p?q#s', message );
	assert.equal( test.resolveReference('g#s') , 'http://a/b/c/g#s', message );
	assert.equal( test.resolveReference('g?y#s') , 'http://a/b/c/g?y#s', message );
	assert.equal( test.resolveReference(';x') , 'http://a/b/c/;x', message );
	assert.equal( test.resolveReference('g;x') , 'http://a/b/c/g;x', message );
	assert.equal( test.resolveReference('g;x?y#s') , 'http://a/b/c/g;x?y#s', message );
	assert.equal( test.resolveReference('') , 'http://a/b/c/d;p?q', message );
	assert.equal( test.resolveReference('.') , 'http://a/b/c/', message );
	assert.equal( test.resolveReference('./') , 'http://a/b/c/', message );
	assert.equal( test.resolveReference('..') , 'http://a/b/', message );
	assert.equal( test.resolveReference('../') , 'http://a/b/', message );
	assert.equal( test.resolveReference('../g') , 'http://a/b/g', message );
	assert.equal( test.resolveReference('../..') , 'http://a/', message );
	assert.equal( test.resolveReference('../../') , 'http://a/', message );
	assert.equal( test.resolveReference('../../g') , 'http://a/g', message );
	assert.equal( test.resolveReference('../../../g') , 'http://a/g', message );
	assert.equal( test.resolveReference('../../../../g') , 'http://a/g', message );
	assert.equal( test.resolveReference('/./g') , 'http://a/g', message );
	assert.equal( test.resolveReference('/../g') , 'http://a/g', message );
	assert.equal( test.resolveReference('g.') , 'http://a/b/c/g.', message );
	assert.equal( test.resolveReference('.g') , 'http://a/b/c/.g', message );
	assert.equal( test.resolveReference('g..') , 'http://a/b/c/g..', message );
	assert.equal( test.resolveReference('..g') , 'http://a/b/c/..g', message );
	assert.equal( test.resolveReference('./../g') , 'http://a/b/g', message );
	assert.equal( test.resolveReference('./g/.') , 'http://a/b/c/g/', message );
	assert.equal( test.resolveReference('g/./h') , 'http://a/b/c/g/h', message );
	assert.equal( test.resolveReference('g/../h') , 'http://a/b/c/h', message );
	assert.equal( test.resolveReference('g;x=1/./y') , 'http://a/b/c/g;x=1/y', message );
	assert.equal( test.resolveReference('g;x=1/../y') , 'http://a/b/c/y', message );
	assert.equal( test.resolveReference('g?y/./x') , 'http://a/b/c/g?y/./x', message );
	assert.equal( test.resolveReference('g?y/../x') , 'http://a/b/c/g?y/../x', message );
	assert.equal( test.resolveReference('g#s/./x') , 'http://a/b/c/g#s/./x', message );
	assert.equal( test.resolveReference('g#s/../x') , 'http://a/b/c/g#s/../x', message );
})();