<?php
/** @noinspection PhpUnhandledExceptionInspection */
echo "Phpcraft PHP Minecraft Server List Pinger\n\n";
if(empty($argv))
{
	die("This is for PHP-CLI. Connect to your server via SSH and use `php listping.php`.\n");
}
if(empty($argv[1]))
{
	die("Syntax: listping.php <ip[:port]> [method]\n");
}
require "vendor/autoload.php";
use Phpcraft\{Phpcraft, Versions};
echo "Resolving...";
$server = Phpcraft::resolve($argv[1]);
$serverarr = explode(":", $server);
if(count($serverarr) != 2)
{
	die(" Failed to resolve name. Got {$server}\n");
}
echo " Requesting status from {$server}...";
$info = Phpcraft::getServerStatus($serverarr[0], intval($serverarr[1]), 3, empty($argv[2]) ? 0 : $argv[0]);
echo "\n\n";
if(empty($info))
{
	die("Failed to get status.\n");
}
if(isset($info["description"]))
{
	echo Phpcraft::chatToText($info["description"], 1)."\x1B[0m\n\n";
}
else
{
	echo "This server has no description/MOTD.\n";
}
if(isset($info["version"]))
{
	if(isset($info["version"]["protocol"]))
	{
		if($minecraft_versions = Versions::protocolToMinecraft($info["version"]["protocol"]))
		{
			if(isset($info["version"]["name"]))
			{
				echo "This server is running a Phpcraft-compatible ".$info["version"]["name"]." (".$minecraft_versions[0].") server.\n";
			}
			else
			{
				echo "This server is running a Phpcraft-compatible ".$minecraft_versions[0]." server.\n";
			}
		}
		else
		{
			if(isset($info["version"]["name"]))
			{
				echo "This server is running a Phpcraft-incompatible ".$info["version"]["name"]." server.\n";
			}
			else
			{
				echo "This server is running a Phpcraft-incompatible version.\n";
			}
		}
	}
	else if(isset($info["version"]["name"]))
	{
		echo "This server is running a ".$info["version"]["name"]." server.\n";
	}
}
if(isset($info["players"]))
{
	$sample = "";
	if(isset($info["players"]["sample"]))
	{
		foreach($info["players"]["sample"] as $player)
		{
			if(isset($player["name"]))
			{
				$sample .= "- ".$player["name"]."\n";
			}
		}
	}
	echo "There are ".(isset($info["players"]["online"])?$info["players"]["online"]:"???")."/".(isset($info["players"]["max"])?$info["players"]["max"]:"???")." players online".(($sample=="")?".\n":":\n".$sample);
}
echo "The server answered the status request within ".round($info["ping"] * 1000)." ms.\n";
