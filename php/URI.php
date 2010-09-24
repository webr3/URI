<?php
/**
 * URI support as per rfc3986 for PHP
 * @seeAlso <http://www.ietf.org/rfc/rfc3986.txt>
 * ===================================================
 * 
 * @author Nathan <http://webr3.org/nathan#me>
 * @version 2010-09-24T20:22:00Z
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 * PHP Version: 5.1.6+
 * 
 * exposes attributes:
 * - scheme
 * - userinfo
 * - host
 * - port
 * - path
 * - query (prefixed with ?)
 * - fragment (prefixed with #)
 * 
 * exposes methods:
 * - defrag(); remove fragment
 * - isAbsolute();
 * - toAbsolute();
 * - equals( $otherURI );
 * - resolveReference( $reference ); 
 * 
 * stringifiable w/ magic __toString() method
 * 
 * source: <http://github.com/webr3/URI>
 * To the extent possible under law, <http://webr3.org/nathan#me>
 * has waived all copyright and related or neighboring rights to
 * this work.
 * 
 */


class URI {
	
	public $scheme;
	public $userinfo;
	public $host;
	public $port;
	public $path;
	public $query;
	public $fragment;

	private $heirpart;
	private $authority;
	
	public function __construct( $value ) {
		$this->initAttributes($value);
	}
	
	private function initAttributes($value) {
		$this->scheme = self::getScheme($value);
		if( ($this->heirpart = self::getHeirPart($value)) !== null) {
			$this->authority = self::getAuthority($this->heirpart);
			if($this->authority != null) {
				$this->userinfo = self::getUserInfo($this->authority);
				$this->host = self::getHost($this->authority);
				$this->port = self::getPort($this->authority);
			}
			$this->path = self::getPath($this->heirpart);
		}
		$this->query = self::getQueryString($value);
		$this->fragment = self::getFragment($value);
	}
	
	public function defrag() {
		return new URI( preg_replace( '/#.*/i' , '' , $this ) ); #dereference
	}
	
	public function isAbsolute() {
		return $this->scheme != null && $this->host != null && $this->path != null && $this->fragment == null;
	}
	
	public function toAbsolute() {
		if( !$this->scheme || !$this->authority ) throw new Exception('URI must have a scheme and a heirpart');
		$out = $this->resolveReference($this);
		return $out->defrag();
	}
	
	public function equals( $uri ) {
		return ( ($uri.'') === ($this.'') );
	}
	
	public function resolveReference( $reference ) {
		if( is_string($reference) ) {
			$reference = new URI($reference);
		}
		if( !($reference instanceof URI) ) {
			throw new Exception('Expected string or URI');
		}
		$t = (object)array(
			'scheme' => '',
			'authority' => '',
			'path' => '',
			'query' => '',
			'fragment' => ''
		);
		$q = null;
		if( $reference->scheme ) {
			$t->scheme = $reference->scheme . ':';
			if($reference->authority) {
				$t->authority = '//' . $reference->authority; 
			}
			$t->path = self::removeDotSegments($reference->path);
			if($reference->query) $t->query = $reference->query;
		} else {
			if($reference->authority) {
				$t->authority = '//' . $reference->authority;
				$t->path = self::removeDotSegments($reference->path);
				if($reference->query) $t->query = $reference->query; 
			} else {
				$q = $reference->path;
				if(!$q) {
					$t->path = $this->path;
					$t->query = ($reference->query) ? $reference->query : $this->query;
				} else {
					if( $q{0} == '/' ) {
						$t->path = self::removeDotSegments($q);
					} else {
						if($this->path) {
							$q = strrpos($this->path, '/');
							if( $q !== false ) {
								$t->path = substr($this->path,0,++$q);
							}
							$t->path .= $reference->path;
						} else {
							$t->path = '/' + $q;
						}
						$t->path = self::removeDotSegments($t->path);
					}
					$t->query = $reference->query;
				}
				$t->authority = ($this->authority) ? '//' . $this->authority : '';
			}
			$t->scheme = $this->scheme . ':';
		}
		$t->fragment = $reference->fragment;
		return new URI( $t->scheme . $t->authority . $t->path . $t->query . $t->fragment );
	}
	
	private static function getScheme( $uri ) {
		if( !preg_match('/^[a-z0-9\-\+\.]+:/i', $uri, $match ) ) return null;
		//return $match[0]; // including ':'
		return substr($match[0], 0, -1); // without trailing ':'
	}
	
	private static function getAuthority( $heirpart ) {
		if( substr($heirpart,0,2) != '//' ) {
			return null;
		}
		$authority = substr($heirpart,2);
		if( ($q = strpos($authority,'/')) !== FALSE ) {
			$authority = substr($authority,0,$q);
		}
		return $authority;
	}
	
	private static function getUserInfo( $authority ) {
		$q = strpos($authority,'@');
		if( $q === false ) return null;
		return substr($authority,0,$q);
	}
	
	private static function getHost( $authority ) {
		$host = $authority;
		if( ($q = strpos($host,'@')) !== false) {
			$host = substr($host,++$q);
		}
		if( strpos($host,'[') !== false ) {
			$q = strpos($host,']');
			if($q !== false) return substr($host,0,++$q);
		}
		$q = strrpos($host,':');
		if($q !== false) return substr($host,0,$q);
		return $host;
	}
	
	private static function getPort( $authority ) { // there be pirates about
		$port = $authority;
		if( ($q = strpos($port,'@')) !== false) {
			$port = substr($port,++$q);
		}
		if( strpos($port,'[') !== false ) {
			$q = strpos($port,']');
			if($q !== false) $port = substr($port,$q);
		}
		$q = strrpos($port,':');
		if($q === false) return null;
		$port = substr($port,++$q);
		return strlen($port) == 0 ? null : $port;
	}
	
	private static function getPath( $heirpart ) {
		$q = self::getAuthority($heirpart);
		if($q == null) {
			return strlen($heirpart) > 0 ? $heirpart : null; 
		}
		return substr( $heirpart, strlen($q)+2 );
	}
	
	private static function getHeirPart( $uri ) {
		$heirpart = "" . $uri;
		$q = strpos($heirpart,'?');
		if($q !== FALSE) {
			$heirpart = substr($heirpart, 0, $q);
		} else {
			$q = strpos($heirpart,'#');
			if($q !== FALSE) {
				$heirpart = substr($heirpart, 0, $q);
			}
		}
		$q = self::getScheme($heirpart);
		if($q) $heirpart = substr($heirpart, strlen($q)+1);
		return strlen($heirpart) > 0 ? $heirpart : null;
	}
	
	private static function getQueryString( $uri ) {
		if( ($q = strpos($uri,'?')) === FALSE ) return null;
		if( ($h = strpos($uri,'#')) === FALSE ) return substr($uri, $q);
		return substr($uri, $q, $h-$q);
	}
	
	private static function getFragment( $uri ) {
		if( ($h = strpos($uri,'#')) === FALSE ) return null;
		return substr($uri, $h);
	}
	
	private static function removeDotSegments( $input ) {
		$output = '';
		$q = null;
		while(strlen($input) > 0) {
			if( substr($input,0,3) == '../' || substr($input,0,2) == './' ) {
				$input = substr($input, strpos($input,'/') );
			} else if($input == '/.') {
				$input = '/';
			} else if( substr($input,0,3) == '/./' ) {
				$input = substr($input,2);
			} else if( substr($input,0,4) == '/../' || $input == '/..' ) {
				if($input == '/..') {
					$input = '/';
				} else {
					$input = substr($input,3);
				}
				$q = strrpos($output,'/');
				if($q !== FALSE) {
					$output = substr($output,0,$q);
				} else {
					$output = '';
				}
			} else if( substr($input,0,2) == '..' || substr($input,0,1) == '.' ) {
				$input = substr($input,0,strpos($input,'.'));
				$q = strpos($input,'.');
				if($q !== false) {
					$input = substr($input,$q);
				}
			} else {
				if( $input{0} == '/' ) {
					$output .= '/';
					$input = substr($input,1);
				}
				$q = strpos($input,'/');
				if($q == false) {
					$output .= $input;
					$input = '';
				} else {
					$output .= substr($input,0,$q);
					$input = substr($input,$q);
				}
			}
		}
		return $output;
	}
	
	public function __toString() {
		$out = '';
		if($this->scheme) $out .= $this->scheme . ':';
		if($this->host) {
			$out .= '//';
			if($this->userinfo) $out .= $this->userinfo . '@';
			$out .= $this->host;
			if($this->port) $out .= ':' . $this->port;
		}
		$out .= $this->path . $this->query . $this->fragment;
		return $out;
	}
}
