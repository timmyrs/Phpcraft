<?php
namespace Phpcraft;
use hellsh\UUID;
use Phpcraft\Exception\IOException;
abstract class Phpcraft
{
	const SRC_DIR = __DIR__;
	const INSTALL_DIR = self::SRC_DIR.'/..';
	const BIN_DIR = self::INSTALL_DIR.'/bin';
	const DATA_DIR = self::INSTALL_DIR.'/data';
	/**
	 * @deprecated Use ChatComponent::FORMAT_NONE, instead.
	 */
	const FORMAT_NONE = 0;
	/**
	 * @deprecated Use ChatComponent::FORMAT_ANSI, instead.
	 */
	const FORMAT_ANSI = 1;
	/**
	 * @deprecated Use ChatComponent::FORMAT_SILCROW, instead.
	 */
	const FORMAT_SILCROW = 2;
	/**
	 * @deprecated Use ChatComponent::FORMAT_AMPERSAND, instead.
	 */
	const FORMAT_AMPERSAND = 3;
	/**
	 * @deprecated Use ChatComponent::FORMAT_HTML, instead.
	 */
	const FORMAT_HTML = 4;
	/**
	 * @deprecated Use ServerConnection::METHOD_ALL, instead.
	 */
	const METHOD_ALL = 0;
	/**
	 * @deprecated Use ServerConnection::METHOD_MODERN, instead.
	 */
	const METHOD_MODERN = 1;
	/**
	 * @deprecated Use ServerConnection::METHOD_LEGACY, instead.
	 */
	const METHOD_LEGACY = 2;
	/**
	 * @var Configuration $json_cache
	 */
	public static $json_cache;
	/**
	 * @var Configuration $user_cache
	 */
	public static $user_cache;
	private static $profiles;

	/**
	 * Returns the contents of Minecraft's launcher_profiles.json with some values being set if they are unset.
	 *
	 * @param bool $bypass_cache Set this to true if you anticipate external changes to the file.
	 * @return array
	 * @see Phpcraft::getProfilesFile()
	 * @see Phpcraft::saveProfiles()
	 */
	static function getProfiles(bool $bypass_cache = false): array
	{
		if($bypass_cache || self::$profiles === null)
		{
			$profiles_file = self::getProfilesFile();
			if(file_exists($profiles_file) && is_file($profiles_file))
			{
				self::$profiles = json_decode(file_get_contents($profiles_file), true);
			}
			else
			{
				self::$profiles = [];
			}
			if(empty(self::$profiles["clientToken"]))
			{
				self::$profiles["clientToken"] = UUID::v4()
													 ->__toString();
			}
			if(!isset(self::$profiles["authenticationDatabase"]))
			{
				self::$profiles["authenticationDatabase"] = [];
			}
		}
		return self::$profiles;
	}

	/**
	 * Returns the path of Minecraft's launcher_profiles.json.
	 *
	 * @return string
	 */
	static function getProfilesFile(): string
	{
		return self::getMinecraftFolder()."/launcher_profiles.json";
	}

	/**
	 * Returns the path of the .minecraft folder without a folder seperator at the end.
	 *
	 * @return string
	 */
	static function getMinecraftFolder(): string
	{
		if(self::isWindows())
		{
			$minecraft_folder = getenv("APPDATA")."\\.minecraft";
		}
		else if(stristr(PHP_OS, "LINUX"))
		{
			$minecraft_folder = getenv("HOME")."/.minecraft";
		}
		else if(stristr(PHP_OS, "DAR"))
		{
			$minecraft_folder = getenv("HOME")."/Library/Application Support/minecraft";
		}
		else
		{
			$minecraft_folder = __DIR__."/.minecraft";
		}
		if(!file_exists($minecraft_folder) || !is_dir($minecraft_folder))
		{
			mkdir($minecraft_folder);
		}
		return $minecraft_folder;
	}

	/**
	 * Returns true if the code is running on a Windows machine.
	 *
	 * @return boolean
	 */
	static function isWindows(): bool
	{
		return defined("PHP_WINDOWS_VERSION_MAJOR");
	}

	/**
	 * Saves the profiles array into Minecraft's launcher_profiles.json.
	 *
	 * @param array $profiles
	 * @return void
	 */
	static function saveProfiles(array $profiles): void
	{
		self::$profiles = $profiles;
		file_put_contents(self::getProfilesFile(), json_encode(self::$profiles, JSON_PRETTY_PRINT));
	}

	/**
	 * Returns the contents of a JSON file as associative array with additional memory and disk caching levels.
	 *
	 * @param string $url The URL of the resource.
	 * @return array
	 * @throws IOException
	 * @see Phpcraft::maintainCache
	 */
	static function getCachableJson(string $url): array
	{
		if(!self::$json_cache->data && is_file(self::$json_cache->file))
		{
			if(filemtime(self::$json_cache->file) < time() - 86400)
			{
				self::maintainCache();
			}
			if(is_file(self::$json_cache->file))
			{
				self::$json_cache->data = json_decode(file_get_contents(self::$json_cache->file), true);
			}
		}
		if(!self::$json_cache->has($url) || self::$json_cache->data[$url]["expiry"] < time())
		{
			$content_json = @json_decode(file_get_contents($url), true);
			if($content_json)
			{
				self::$json_cache->set($url, [
					"contents" => $content_json,
					"expiry" => time() + 86400
				]);
			}
			else if(!self::$json_cache->has($url))
			{
				throw new IOException("Failed to download $url");
			}
		}
		return self::$json_cache->data[$url]["contents"];
	}

	/**
	 * Deletes expired cache entries.
	 *
	 * @return void
	 * @see getCachableJson
	 * @see getCachableResource
	 */
	static function maintainCache(): void
	{
		if(!is_file(self::$json_cache->file))
		{
			return;
		}
		$time = time();
		foreach(self::$json_cache->data as $url => $entry)
		{
			if($entry["expiry"] < $time)
			{
				self::$json_cache->unset($url);
			}
		}
	}

	/**
	 * Sends an HTTP POST request with a JSON payload.
	 * The response will always contain a "status" value which will be the HTTP response code, e.g. 200.
	 *
	 * @param string $url
	 * @param array $data
	 * @return array
	 */
	static function httpPOST(string $url, array $data): array
	{
		$res = @file_get_contents($url, false, stream_context_create([
			"http" => [
				"header" => "Content-type: application/json\r\n",
				"method" => "POST",
				"content" => json_encode($data)
			]
		]));
		if($res == "")
		{
			$res = [];
		}
		else
		{
			$res = json_decode($res, true);
		}
		$res["status"] = explode(" ", $http_response_header[0])[1];
		return $res;
	}

	/**
	 * Resolves the given address.
	 *
	 * @param string $server The server address, e.g. localhost
	 * @return string The resolved address, e.g. localhost:25565
	 * @deprecated Use ServerConnection::resolveAddress or even ServerConnection::toAddress.
	 */
	static function resolve(string $server): string
	{
		if(strpos($server, ":") !== false)
		{
			return $server;
		}
		if(ip2long($server) === false && $res = @dns_get_record("_minecraft._tcp.{$server}", DNS_SRV))
		{
			$i = array_rand($res);
			return $res[$i]["target"].":".$res[$i]["port"];
		}
		return $server.":25565";
	}

	static function binaryStringToBin(string $str): string
	{
		$bin_str = "";
		foreach(str_split($str) as $char)
		{
			$bin_str .= str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT)." ";
		}
		return rtrim($bin_str);
	}

	static function binaryStringToHex(string $str): string
	{
		$hex_str = "";
		foreach(str_split($str) as $char)
		{
			$hex_str .= str_pad(dechex(ord($char)), 2, "0", STR_PAD_LEFT)." ";
		}
		return rtrim($hex_str);
	}

	/**
	 * Generates a Minecraft-style SHA1 hash.
	 *
	 * @param string $str
	 * @return string
	 */
	static function sha1(string $str): string
	{
		$gmp = gmp_import(sha1($str, true));
		if(gmp_cmp($gmp, gmp_init("0x8000000000000000000000000000000000000000")) >= 0)
		{
			$gmp = gmp_mul(gmp_add(gmp_xor($gmp, gmp_init("0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF")), gmp_init(1)), gmp_init(-1));
		}
		return gmp_strval($gmp, 16);
	}

	/**
	 * @param array|string|null|ChatComponent $chat
	 * @param int $format
	 * @param array<string,string>|null $translations
	 * @return string
	 * @deprecated Use ChatComponent::cast($chat)->toString($format), instead.
	 */
	static function chatToText($chat, int $format = ChatComponent::FORMAT_NONE, ?array $translations = null): string
	{
		if($translations !== null && count($translations) > count(ChatComponent::$translations))
		{
			ChatComponent::$translations = $translations;
		}
		return ChatComponent::cast($chat)
							->toString($format);
	}

	/**
	 * @param string $hostname
	 * @param int $port
	 * @param float $timeout
	 * @param int $method
	 * @return array
	 * @throws IOException
	 * @deprecated Use ServerConnection::getStatus, instead.
	 */
	static function getServerStatus(string $hostname, int $port = 25565, float $timeout = 3.000, int $method = ServerConnection::METHOD_ALL): array
	{
		return ServerConnection::getStatus($hostname, $port, $timeout, $method);
	}

	/**
	 * @param string $text
	 * @param boolean $allow_amp
	 * @return array
	 * @deprecated Use ChatComponent::text($text, $allow_amp)->toArray(), instead.
	 */
	static function textToChat(string $text, bool $allow_amp = false): array
	{
		return ChatComponent::text($text, $allow_amp)
							->toArray();
	}

	/**
	 * Calculates the "distance" between two RGB arrays (each 3 integers).
	 *
	 * @param array{int,int,int} $rgb1
	 * @param array{int,int,int} $rgb2
	 * @return int
	 */
	static function colorDiff(array $rgb1, array $rgb2): int
	{
		return abs($rgb1[0] - $rgb2[0]) + abs($rgb1[1] - $rgb2[1]) + abs($rgb1[2] - $rgb2[2]);
	}

	/**
	 * Converts an RGB array (3 integers) into a hexadecimal string, e.g. ff00ff.
	 *
	 * @param array{int,int,int} $rgb
	 * @return string
	 * @since 0.5.18
	 */
	static function rgbToHex(array $rgb): string
	{
		return str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT).str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT).str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
	}

	/**
	 * Converts an RGB array (3 integers) into a decimal number for use in NBT tags.
	 *
	 * @param array{int,int,int} $rgb
	 * @return int
	 * @since 0.5.18
	 */
	static function rgbToInt(array $rgb): int
	{
		return hexdec(self::rgbToHex($rgb));
	}

	/**
	 * Recursively deletes a folder.
	 *
	 * @param string $path
	 * @return void
	 */
	static function recursivelyDelete(string $path): void
	{
		if(substr($path, -1) == "/")
		{
			$path = substr($path, 0, -1);
		}
		if(!file_exists($path))
		{
			return;
		}
		if(is_dir($path))
		{
			foreach(scandir($path) as $file)
			{
				if(!in_array($file, [
					".",
					".."
				]))
				{
					self::recursivelyDelete($path."/".$file);
				}
			}
			rmdir($path);
		}
		else
		{
			unlink($path);
		}
	}
}

Phpcraft::$json_cache = new Configuration(__DIR__."/.json_cache");
Phpcraft::$user_cache = new Configuration(__DIR__."/.user_cache");
