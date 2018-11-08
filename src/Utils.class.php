<?php
namespace Phpcraft;
require_once __DIR__."/validate.php"; 
/** Utilities. */
class Utils
{
	private static $minecraft_folder = null;
	private static $versions = [
		"1.13.2" => 404,
		"1.13.2-pre2" => 403,
		"1.13.2-pre1" => 402,
		"1.13.1" => 401,
		"1.13" => 393,
		"1.12.2" => 340,
		"1.12.2-pre2" => 339,
		"1.12.1" => 338,
		"1.12.1-pre2" => 337,
		"1.12.1-pre1" => 337,
		"17w31a" => 336,
		"1.12" => 335,
		"1.11.2" => 316,
		"1.11.1" => 316,
		"1.11" => 315,
		"1.10.2" => 210,
		"1.10.1" => 210,
		"1.10" => 210,
		"1.9.4" => 110,
		"1.9.3" => 110,
		"1.9.2" => 109,
		"1.9.1" => 108,
		"1.9" => 107,
		"1.8.9" => 47,
		"1.8.8" => 47,
		"1.8.7" => 47,
		"1.8.6" => 47,
		"1.8.5" => 47,
		"1.8.4" => 47,
		"1.8.3" => 47,
		"1.8.2" => 47,
		"1.8.1" => 47,
		"1.8" => 47
	];

	/**
	 * Returns the path of the .minecraft folder without a slash at the end.
	 * @return string
	 */
	static function getMinecraftFolder()
	{
		if(Utils::$minecraft_folder === null)
		{
			if(stristr(PHP_OS, "LINUX"))
			{
				Utils::$minecraft_folder = getenv("HOME")."/.minecraft";
			}
			else if(stristr(PHP_OS, "DAR"))
			{
				Utils::$minecraft_folder = getenv("HOME")."/Library/Application Support/minecraft";
			}
			else if(stristr(PHP_OS, "WIN"))
			{
				Utils::$minecraft_folder = getenv("APPDATA")."\\.minecraft";
			}
			else
			{
				Utils::$minecraft_folder = __DIR__."/.minecraft";
			}
			if(!file_exists(Utils::$minecraft_folder) || !is_dir(Utils::$minecraft_folder))
			{
				mkdir(Utils::$minecraft_folder);
			}
		}
		return Utils::$minecraft_folder;
	}

	/**
	 * Returns the path of the .minecraft/launcher_profiles.json.
	 * @return string
	 */
	static function getProfilesFile()
	{
		return Utils::getMinecraftFolder()."/launcher_profiles.json";
	}

	/**
	 * Returns the contents of the .minecraft/launcher_profiles.json with some values being set if they are unset.
	 * @return array
	 * @see Utils::getProfilesFile()
	 * @see Utils::saveProfiles()
	 */
	static function getProfiles()
	{
		$profiles_file = Utils::getProfilesFile();
		if(file_exists($profiles_file) && is_file($profiles_file))
		{
			$profiles = json_decode(file_get_contents($profiles_file), true);
		}
		else
		{
			$profiles = [];
		}
		if(empty($profiles["clientToken"]))
		{
			$profiles["clientToken"] = Utils::generateUUIDv4();
		}
		if(!isset($profiles["selectedUser"]))
		{
			$profiles["selectedUser"] = [];
		}
		if(!isset($profiles["authenticationDatabase"]))
		{
			$profiles["authenticationDatabase"] = [];
		}
		return $profiles;
	}

	/**
	 * Saves the profiles array into the .minecraft/launcher_profiles.json.
	 * @param array $profiles
	 * @return void
	 */
	static function saveProfiles($profiles)
	{
		file_put_contents(Utils::getProfilesFile(), json_encode($profiles, JSON_PRETTY_PRINT));
	}

	/**
	 * Returns the JSON-decoded content of the assets index of the latest version.
	 * @return array
	 */
	static function getAssetIndex()
	{
		$assets_dir = Utils::getMinecraftFolder()."/assets";
		if(!file_exists($assets_dir) || !is_dir($assets_dir))
		{
			mkdir($assets_dir);
		}
		$assets_index_dir = $assets_dir."/indexes";
		if(!file_exists($assets_index_dir) || !is_dir($assets_index_dir))
		{
			mkdir($assets_index_dir);
		}
		$index_file = $assets_index_dir."/1.13.1.json";
		if(!file_exists($index_file))
		{
			$index = file_get_contents("https://launchermeta.mojang.com/v1/packages/f776dabd6239938411e2f123837f4005b74e49f8/1.13.1.json");
			file_put_contents($index_file, $index);
		}
		else
		{
			$index = file_get_contents($index_file);
		}
		return json_decode($index, true);
	}

	/**
	 * Checks the asset index for the existence of an asset.
	 * @return boolean
	 */
	static function doesAssetExist($name)
	{
		return isset(Utils::getAssetIndex()["objects"][$name]);
	}

	/**
	 * Downloads an asset by name and returns the path to the downloaded file on success or null on failure.
	 * @param string $name
	 * @return string
	 */
	static function downloadAsset($name)
	{
		$index = Utils::getAssetIndex();
		$objects_dir = Utils::getMinecraftFolder()."/assets/objects";
		if(!file_exists($objects_dir) || !is_dir($objects_dir))
		{
			mkdir($objects_dir);
		}
		if($asset = $index["objects"][$name])
		{
			$hash = $index["objects"][$name]["hash"];
			$dir = $objects_dir."/".substr($hash, 0, 2);
			if(!file_exists($dir) || !is_dir($dir))
			{
				mkdir($dir);
			}
			$file = $dir."/".$hash;
			file_put_contents($file, file_get_contents("http://resources.download.minecraft.net/".substr($hash, 0, 2)."/".$hash));
			return $file;
		}
		return null;
	}

	/**
	 * Validates an in-game name.
	 * @param string $name
	 * @return boolean True if the name is valid.
	 */
	static function validateName($name)
	{
		if(strlen($name) < 3 || strlen($name) > 16)
		{
			return false;
		}
		$allowed_characters = ["_", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
		foreach(range("a", "z") as $char)
		{
			array_push($allowed_characters, $char);
		}
		foreach(range("A", "Z") as $char)
		{
			array_push($allowed_characters, $char);
		}
		foreach(str_split($name) as $char)
		{
			if(!in_array($char, $allowed_characters))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Returns an array of extensions missing to enable online mode.
	 * If you want to enable online mode, GMP, openssl, and mcrypt are required. This function returns a string array of all extensions that are missing. Therefore, an empty array means all required extensions are installed.
	 * @return array
	 */
	static function getExtensionsMissingToGoOnline()
	{
		$extensions_needed = [];
		if(!extension_loaded("gmp"))
		{
			array_push($extensions_needed, "GMP");
		}
		if(!extension_loaded("openssl"))
		{
			array_push($extensions_needed, "openssl");
		}
		$mcrypt = false;
		foreach(stream_get_filters() as $filter)
		{
			if(stristr($filter, "mcrypt"))
			{
				$mcrypt = true;
			}
		}
		if(!$mcrypt)
		{
			array_push($extensions_needed, "mcrypt");
		}
		return $extensions_needed;
	}

	/**
	 * Generates a random UUID (UUIDv4).
	 * @param boolean $withHypens
	 * @return string
	 */
	static function generateUUIDv4($withHypens = false)
	{
		return sprintf($withHypens ? "%04x%04x-%04x-%04x-%04x-%04x%04x%04x" : "%04x%04x%04x%04x%04x%04x%04x%04x", mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), (mt_rand(0, 0x0fff) | 0x4000), (mt_rand(0, 0x3fff) | 0x8000), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
	}

	/**
	 * Adds hypens to a UUID.
	 * @param string $uuid
	 * @return string
	 */
	public function addHypensToUUID($uuid)
	{
		return substr($uuid, 0, 8)."-".substr($uuid, 8, 4)."-".substr($uuid, 12, 4)."-".substr($uuid, 16, 4)."-".substr($uuid, 20);
	}

	/**
	 * Sends an HTTP POST request with a JSON payload.
	 * The response will always contain a "status" value which will be the HTTP response code, e.g. 200.
	 * @param string $url
	 * @param array $data
	 * @return array
	 */
	static function httpPOST($url, $data)
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
	 * @param string $server The server address, e.g. localhost
	 * @return string The resolved address, e.g. localhost:25565
	 */
	static function resolve($server)
	{
		$arr = explode(":", $server);
		if(count($arr) > 1)
		{
			return Utils::resolveName($arr[0], false).":".$arr[1];
		}
		return Utils::resolveName($server, true);
	}

	private static function resolveName($server, $withPort = true)
	{
		if(ip2long($server) === false && $res = @dns_get_record("_minecraft._tcp.{$server}", DNS_SRV))
		{
			$i = array_rand($res);
			return Utils::resolveName($res[$i]["target"], false).($withPort ? ":".$res[$i]["port"] : "");
		}
		return $server.($withPort ? ":25565" : "");
	}

	/**
	 * Converts an integer into a VarInt binary string.
	 * @param integer $value
	 * @return string
	 */
	static function intToVarInt($value)
	{
		$bytes = "";
		global $write_buffer;
		do
		{
			$temp = ($value & 0b01111111);
			$value = (($value >> 7) & 0b01111111);
			if($value != 0)
			{
				$temp |= 0b10000000;
			}
			$bytes .= pack("c", $temp);
		}
		while($value != 0);
		return $bytes;
	}

	/**
	 * Returns whether a given protocol version is supported.
	 * @param integer $protocol_version e.g., 340
	 * @return boolean
	 */
	static function isProtocolVersionSupported($protocol_version)
	{
		return in_array($protocol_version, Utils::$versions);
	}

	/**
	 * Returns an array of Minecraft versions corresponding to the given protocol version, newest first.
	 * @param integer $protocol_version e.g., 340 for ["1.12.2"]
	 * @return array
	 */
	static function getMinecraftVersionsFromProtocolVersion($protocol_version)
	{
		$minecraft_versions = [];
		foreach(Utils::$versions as $k => $v)
		{
			if($v == $protocol_version)
			{
				array_push($minecraft_versions, $k);
			}
		}
		return $minecraft_versions;
	}

	/**
	 * Returns whether a given Minecraft version is supported.
	 * @param string $minecraft_version e.g., 1.12.2
	 * @return boolean
	 */
	static function isMinecraftVersionSupported($minecraft_version)
	{
		return isset(Utils::$versions[$minecraft_version]);
	}

	/**
	 * Returns the Minecraft version corresponding to the given protocol version.
	 * @param string $minecraft_version e.g., 1.12.2 for 340
	 * @return integer The protocol version or null if the Minecraft version is not supported.
	 */
	static function getProtocolVersionFromMinecraftVersion($minecraft_version)
	{
		return @Utils::$versions[$minecraft_version];
	}

	/**
	 * Generates a Minecraft-style SHA1 hash.
	 * @param string $str
	 * @return string
	 */
	static function sha1($str)
	{
		$gmp = gmp_import(sha1($str, true));
		if(gmp_cmp($gmp, gmp_init("0x8000000000000000000000000000000000000000")) >= 0)
		{
			$gmp = gmp_mul(gmp_add(gmp_xor($gmp, gmp_init("0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF")), gmp_init(1)), gmp_init(-1));
		}
		return gmp_strval($gmp, 16);
	}

	/**
	 * Converts a string using § format codes into a chat object.
	 * @param string $str
	 * @param boolean $allowAnd When true, '&' will be handled like '§'.
	 * @param integer $i Ignore this parameter.
	 * @param boolean $child Ignore this parameter.
	 */
	static function textToChat($str, $allowAnd = false, &$i = 0, $child = false)
	{
		if(strpos($str, "§") === false && (!$allowAnd || strpos($str, "&") === false))
		{
			return ["text" => $str];
		}
		if(!$child && $i == 0 && (strpos(mb_substr($str, 2, null, "utf-8"), "§r") !== false || ($allowAnd && strpos(mb_substr($str, 2, null, "utf-8"), "&r") !== false)))
		{
			$extras = [];
			while($i < mb_strlen($str, "utf-8"))
			{
				array_push($extras, Utils::textToChat($str, $allowAnd, $i, true));
				$i++;
			}
			return ["text" => "", "extra" => $extras];
		}
		$colors = [
			"0" => "black",
			"1" => "dark_blue",
			"2" => "dark_green",
			"3" => "dark_aqua",
			"4" => "dark_red",
			"5" => "dark_purple",
			"6" => "gold",
			"7" => "gray",
			"8" => "dark_gray",
			"9" => "blue",
			"a" => "green",
			"b" => "aqua",
			"c" => "red",
			"d" => "light_purple",
			"e" => "yellow",
			"f" => "white"
		];
		$chat = ["text" => ""];
		$lastWasParagraph = false;
		while($i < mb_strlen($str, "utf-8"))
		{
			$c = mb_substr($str, $i, 1, "utf-8");
			if($c == "§" || ($allowAnd && $c == "&"))
			{
				$lastWasParagraph = true;
			}
			else if($lastWasParagraph)
			{
				$lastWasParagraph = false;
				if($child && $c == "r")
				{
					return $chat;
				}
				if($chat["text"] == "")
				{
					if($c == "r")
					{
						unset($chat["obfuscated"]);
						unset($chat["bold"]);
						unset($chat["strikethrough"]);
						unset($chat["underlined"]);
						unset($chat["italic"]);
						unset($chat["color"]);
					}
					else if($c == "k")
					{
						$chat["obfuscated"] = true;
					}
					else if($c == "l")
					{
						$chat["bold"] = true;
					}
					else if($c == "m")
					{
						$chat["strikethrough"] = true;
					}
					else if($c == "n")
					{
						$chat["underlined"] = true;
					}
					else if($c == "o")
					{
						$chat["italic"] = true;
					}
					else if(isset($colors[$c]))
					{
						$chat["color"] = $colors[$c];
					}
				}
				else
				{
					$i--;
					$component = Utils::textToChat($str, $allowAnd, $i, true);
					if(!empty($component["text"]) || count($component) > 1)
					{
						if(empty($chat["extra"]))
						{
							$chat["extra"] = [$component];
						}
						else
						{
							array_push($chat["extra"], $component);
						}
					}
				}
			}
			else
			{
				$chat["text"] .= $c;
			}
			$i++;
		}
		return $chat;
	}

	/**
	 * Converts a chat object into text with ANSI escape codes so it will be colorful in the console, as well.
	 * @param array|string $chat The chat object as an array or a string.
	 * @param array $translations The translations array so translated messages look proper.
	 * @param mixed $parent The parent chat object so styling is properly inherited. You don't need to set this.
	 * @return string
	 */
	static function chatToANSIText($chat, $translations = null, $parent = false)
	{
		if($translations == null)
		{
			$translations = [
				"chat.type.text" => "<%s> %s",
				"chat.type.announcement" => "[%s] %s",
				"multiplayer.player.joined" => "%s joined the game",
				"multiplayer.player.left" => "%s left the game"
			];
		}
		if(gettype($chat) == "string")
		{
			if(strpos($chat, "§") === false)
			{
				return $chat;
			}
			$chat = Utils::textToChat($chat);
		}
		if($parent === false)
		{
			$child = false;
			$parent = [];
		}
		else
		{
			$child = true;
		}
		$attributes = [
			"bold" => "1",
			"italic" => "3",
			"underlined" => "4",
			"obfuscated" => "8",
			"strikethrough" => "9"
		];
		$text = "\x1B[0";
		foreach($attributes as $n => $v)
		{
			if(!isset($chat[$n]))
			{
				if(isset($parent[$n]))
				{
					$chat[$n] = $parent[$n];
				}
			}
			if(isset($chat[$n]) && $chat[$n])
			{
				$text .= ";{$v}";
			}
		}
		if(!isset($chat["color"]))
		{
			if(isset($parent["color"]))
			{
				$chat["color"] = $parent["color"];
			}
		}
		if(isset($chat["color"]))
		{
			$colors = [
				"black" => "30;107", // Using a white background on black text
				"dark_blue" => "34",
				"dark_green" => "32",
				"dark_aqua" => "36",
				"dark_red" => "31",
				"dark_purple" => "35",
				"gold" => "33",
				"gray" => "37",
				"dark_gray" => "90",
				"blue" => "94",
				"green" => "92",
				"aqua" => "96",
				"red" => "91",
				"light_purple" => "95",
				"yellow" => "93",
				"white" => "97"
			];
			if(isset($colors[$chat["color"]]))
			{
				$text .= ";".$colors[$chat["color"]];
			}
		}
		$text .= "m";
		if(isset($chat["translate"]))
		{
			$raw;
			if(isset($translations[$chat["translate"]]))
			{
				$raw = $translations[$chat["translate"]];
			}
			else
			{
				$raw = $chat["translate"];
			}
			if(isset($chat["with"]))
			{
				$with = [];
				foreach($chat["with"] as $extra)
				{
					array_push($with, Utils::chatToANSIText($extra, $translations, $chat));
				}
				if(($formatted = @vsprintf($raw, $with)) !== false)
				{
					$raw = $formatted;
				}
			}
			$text .= $raw;
		}
		else if(isset($chat["text"]))
		{
			if(strpos($chat["text"], "§") !== false)
			{
				$chat = Utils::textToChat($chat["text"]) + $chat;
			}
			$text .= $chat["text"];
		}
		if(!$child)
		{
			$text .= "\x1B[0;97;40m";
		}
		if(isset($chat["extra"]))
		{
			foreach($chat["extra"] as $extra)
			{
				$text .= Utils::chatToANSIText($extra, $translations, $chat);
			}
			if(!$child)
			{
				$text .= "\x1B[0;97;40m";
			}
		}
		return $text;
	}
}