<?php
/** @noinspection PhpUnhandledExceptionInspection PhpDocMissingThrowsInspection */
namespace Phpcraft;
abstract class Versions
{
	/**
	 * Returns a list of protocol versions; newest first.
	 *
	 * @param boolean $all true = all versions; false = only supported versions.
	 * @return array<int>
	 */
	static function protocol(bool $all = true): array
	{
		return array_values(Versions::list($all));
	}

	/**
	 * Returns an associative array of Minecraft versions with their protocol version as value; newest first.
	 *
	 * @param boolean $all true = all versions; false = only supported versions.
	 * @return array<string,int>
	 * @throws Exception\IOException If $all is true and the version list could not be downloaded and is not cached
	 */
	static function list(bool $all = true): array
	{
		if(!$all)
		{
			return [
				"1.16.1" => 736,
				"1.16" => 735,
				"1.16-rc1" => 734,
				"1.15.2" => 578,
				"1.15.2-pre2" => 577,
				"1.15.2-pre1" => 576,
				"1.15.1" => 575,
				"1.15.1-pre1" => 574,
				"1.15" => 573,
				"1.15-pre7" => 572,
				"1.15-pre6" => 571,
				"1.15-pre5" => 570,
				"1.15-pre4" => 569,
				"1.15-pre3" => 567,
				"1.15-pre2" => 566,
				"1.15-pre1" => 565,
				"1.14.4" => 498,
				"1.14.4-pre7" => 497,
				"1.14.4-pre6" => 496,
				"1.14.4-pre5" => 495,
				"1.14.4-pre4" => 494,
				"1.14.4-pre3" => 493,
				"1.14.4-pre2" => 492,
				"1.14.4-pre1" => 491,
				"1.14.3" => 490,
				"1.14.3-pre4" => 489,
				"1.14.3-pre3" => 488,
				"1.14.3-pre2" => 487,
				"1.14.3-pre1" => 486,
				"1.14.2" => 485,
				"1.14.2-pre4" => 484,
				"1.14.2-pre3" => 483,
				"1.14.2-pre2" => 482,
				"1.14.2-pre1" => 481,
				"1.14.1" => 480,
				"1.14.1-pre2" => 479,
				"1.14.1-pre1" => 478,
				"1.14" => 477,
				"1.14-pre5" => 476,
				"1.14-pre4" => 475,
				"1.14-pre3" => 474,
				"1.14-pre2" => 473,
				"1.14-pre1" => 472,
				"1.13.2" => 404,
				"1.13.2-pre2" => 403,
				"1.13.2-pre1" => 402,
				"1.13.1" => 401,
				"1.13.1-pre2" => 400,
				"1.13.1-pre1" => 399,
				"18w33a" => 398,
				"18w32a" => 397,
				"18w31a" => 395,
				"18w30a" => 394,
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
				"1.11-pre1" => 314,
				"16w44a" => 313,
				"16w43a" => 313,
				"16w42a" => 312,
				"16w41a" => 311,
				"16w40a" => 310,
				"1.10.2" => 210,
				"1.10.1" => 210,
				"1.10" => 210,
				"1.9.4" => 110,
				"1.9.3" => 110,
				"1.9.2" => 109,
				"1.9.1" => 108,
				"1.9" => 107,
				"1.9-pre4" => 106,
				"1.9-pre3" => 105,
				"1.9-pre2" => 104,
				"1.9-pre1" => 103,
				"16w07b" => 102,
				"16w07a" => 101,
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
		}
		$versions = [];
		foreach(Phpcraft::getCachableJson("https://raw.githubusercontent.com/PrismarineJS/minecraft-data/master/data/pc/common/protocolVersions.json") as $version)
		{
			if(!$version["usesNetty"])
			{
				break;
			}
			$versions[$version["minecraftVersion"]] = $version["version"];
		}
		return $versions;
	}

	/**
	 * Returns true if the given protocol version is supported by Phpcraft.
	 *
	 * @param int $protocol_version e.g., 340
	 * @return boolean
	 */
	static function protocolSupported(int $protocol_version): bool
	{
		return in_array($protocol_version, Versions::list(false));
	}

	/**
	 * Returns true if the given Minecraft version is supported by Phpcraft.
	 *
	 * @param string $minecraft_version
	 * @return boolean
	 */
	static function minecraftSupported(string $minecraft_version): bool
	{
		return array_key_exists($minecraft_version, Versions::list(false));
	}

	/**
	 * Returns the protocol version corresponding to the given Minecraft version; newest first.
	 *
	 * @param string $minecraft_version
	 * @return int The protocol version or NULL if the Minecraft version doesn't exist.
	 */
	static function minecraftToProtocol(string $minecraft_version): int
	{
		return @Versions::list(true)[$minecraft_version];
	}

	/**
	 * Returns an array of Minecraft versions; newest first.
	 *
	 * @param boolean $all true = all versions; false = only supported versions.
	 * @return array<string>
	 */
	static function minecraft(bool $all = true): array
	{
		return array_keys(Versions::list($all));
	}

	/**
	 * Returns an array of non-snapshot Minecraft versions; newest first.
	 *
	 * @param boolean $all true = all versions; false = only supported versions.
	 * @return array<string>
	 */
	static function minecraftReleases(bool $all = true): array
	{
		return array_keys(Versions::releases($all));
	}

	/**
	 * Returns an associative array of non-snapshot Minecraft versions with their protocol version as value; newest first.
	 *
	 * @param boolean $all true = all versions; false = only supported versions.
	 * @return array<string,int>
	 * @see Versions::list
	 */
	static function releases(bool $all = true): array
	{
		$releases = [];
		foreach(Versions::list($all) as $id => $pv)
		{
			if(preg_match('/^1\.[0-9]+(\.[0-9])?$/', $id))
			{
				$releases[$id] = $pv;
			}
		}
		return $releases;
	}

	/**
	 * Returns a human-readable range of Minecraft versions corresponding to the given protocol version, e.g. 47 would return "1.8 - 1.8.9"
	 *
	 * @param int $protocol_version
	 * @return string The version range or an empty string if the given protocol version is not supported.
	 */
	static function protocolToRange(int $protocol_version): string
	{
		$minecraft_versions = Versions::protocolToMinecraft($protocol_version);
		$count = count($minecraft_versions);
		if($count > 0)
		{
			if($count == 1)
			{
				return $minecraft_versions[0];
			}
			return $minecraft_versions[$count - 1]." - ".$minecraft_versions[0];
		}
		return "";
	}

	/**
	 * Returns an array of Minecraft versions corresponding to the given protocol version; newest first.
	 *
	 * @param int $protocol_version
	 * @return array<string>
	 */
	static function protocolToMinecraft(int $protocol_version): array
	{
		$minecraft_versions = [];
		foreach(Versions::list(true) as $k => $v)
		{
			if($v == $protocol_version)
			{
				array_push($minecraft_versions, $k);
			}
		}
		return $minecraft_versions;
	}
}
