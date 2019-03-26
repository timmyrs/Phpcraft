<?php
// This plugin provides clients of the server with /abilities, /gamemode, and /metadata.

use Phpcraft\
{ClientConnection, Event, Plugin, PluginManager};

if(!in_array(PluginManager::$platform, ["phpcraft:server"]))
{
	return;
}
PluginManager::registerPlugin("BasicCommands", function(Plugin $plugin)
{
	$plugin->on("chat_message", function(Event $event)
	{
		if($event->isCancelled() || substr($event->data["message"], 0, 1) != "/")
		{
			return;
		}
		$con = $event->data["client"];
		if(!$con instanceof ClientConnection)
		{
			return;
		}
		if(substr($event->data["message"], 0, 11) == "/abilities ")
		{
			$con->startPacket("clientbound_abilities");
			$con->writeByte(hexdec(substr($event->data["message"], 11, 1)));
			$con->writeFloat(0.4000000059604645);
			$con->writeFloat(0.699999988079071);
			$con->send();
		}
		else if(substr($event->data["message"], 0, 10) == "/gamemode ")
		{
			$gamemode = floatval(substr($event->data["message"], 10));
			$con->startPacket("change_game_state");
			$con->writeByte(3);
			$con->writeFloat($gamemode);
			$con->send();
		}
		else if(substr($event->data["message"], 0, 10) == "/metadata ")
		{
			$con->startPacket("entity_metadata");
			$con->writeVarInt($con->eid);
			$con->writeByte(0);
			$con->writeVarInt(0);
			$con->writeByte(hexdec(substr($event->data["message"], 10, 2)));
			$con->writeByte(0xFF);
			$con->send();
		}
		else
		{
			$con->startPacket("clientbound_chat_message");
			$con->writeString(json_encode(["text" => "That's not any command I know. Try /abilities <0-F>, /gamemode <0-3>, or /metadata <00-FF>."]));
			$con->writeByte(0);
			$con->send();
		}
		$event->cancel();
	});
});
