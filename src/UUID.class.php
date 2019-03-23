<?php
namespace Phpcraft;
/** A UUID helper class. */
class UUID
{
	/**
	 * The binary string containing the UUID.
	 * @var string $binary
	 */
	public $binary;

	/**
	 * The constructor.
	 * @param string $binary The binary string containing the UUID.
	 * @throws Exception When the given string is not a valid UUID binary string.
	 * @see UUID::fromString
	 */
	public function __construct($binary)
	{
		if(strlen($binary) != 16)
		{
			throw new Exception("Invalid UUID binary string: {$binary}");
		}
		$this->binary = $binary;
	}

	/**
	 * Returns a UUID for the given string.
	 * @throws Exception When the given string is not a valid UUID.
	 * @return UUID
	 */
	public static function fromString($str)
	{
		$str = str_replace(["-", "{", "}"], "", $str);
		if(strlen($str) != 32)
		{
			throw new Exception("Invalid UUID: $str");
		}
		return UUID::fromString_($str);
	}

	private static function fromString_($str)
	{
		$binary = "";
		for($i = 0; $i < 32; $i += 2)
		{
			$binary .= chr(intval(hexdec(substr($str, $i, 2))));
		}
		return new UUID($binary);
	}

	/**
	 * Generates a UUIDv4.
	 * @return UUID
	 */
	public static function v4()
	{
		return UUID::fromString_(sprintf("%04x%04x%04x%04x%04x%04x%04x%04x", mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), (mt_rand(0, 0x0fff) | 0x4000), (mt_rand(0, 0x3fff) | 0x8000), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)));
	}

	/**
	 * Generates a UUIDv5.
	 * @return UUID
	 * @throws Exception
	 */
	public static function v5($str, UUID $namespace = null)
	{
		if(!$namespace)
		{
			$namespace = new UUID(str_repeat(chr(0), 16));
		}
		$hash = sha1($str.$namespace->binary);
		return UUID::fromString_(sprintf("%08s%04s%04x%04x%12s", substr($hash, 0, 8), substr($hash, 8, 4), (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000, (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, substr($hash, 20, 12)));
	}

	/**
	 * Returns true if the skin of a player with this UUID would be slim ("Alex" style).
	 * @return boolean
	 */
	public function isSlim()
	{
		return ((ord(substr($this->binary, 3, 1)) & 0xF) ^ (ord(substr($this->binary, 7, 1)) & 0xF) ^ (ord(substr($this->binary, 11, 1)) & 0xF) ^ (ord(substr($this->binary, 15, 1)) & 0xF)) == 1;
	}

	/**
	 * Returns the string representation of the UUID.
	 * @param boolean $withHypens
	 * @return string
	 */
	public function toString($withHypens = false)
	{
		$str = "";
		for($i = 0; $i < 16; $i++)
		{
			if($withHypens && in_array($i, [4, 6, 8, 10]))
			{
				$str .= "-";
			}
			$sec = dechex(ord(substr($this->binary, $i, 1)));
			if(strlen($sec) != 2)
			{
				$sec = "0".$sec;
			}
			$str .= $sec;
		}
		return $str;
	}

	/**
	 * Returns an integer which will always be the same given the same UUID, but collisions are far more likely.
	 * @return integer
	 */
	public function toInt()
	{
		return gmp_intval(gmp_import(substr($this->binary, 0, 2).substr($this->binary, -2)));
	}
}
