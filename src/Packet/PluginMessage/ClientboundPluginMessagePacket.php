<?php
namespace Phpcraft\Packet\PluginMessage;
use Phpcraft\
{Connection, Exception\IOException, Packet\Packet};
class ClientboundPluginMessagePacket extends PluginMessagePacket
{
	/**
	 * @param string $channel The name of the plugin message's channel.
	 * @param string $data The data of the plugin message.
	 */
	function __construct(string $channel = "", string $data = "")
	{
		parent::__construct("clientbound_plugin_message", $channel, $data);
	}

	/**
	 * Initialises the packet class by reading its payload from the given Connection.
	 *
	 * @param Connection $con
	 * @return Packet
	 * @throws IOException
	 */
	static function read(Connection $con): Packet
	{
		$channel = self::readChannel($con);
		switch($channel)
		{
			case "minecraft:brand":
				return new ClientboundBrandPluginMessagePacket($con->readString());
		}
		$packet = new ClientboundPluginMessagePacket($channel, $con->read_buffer);
		$con->read_buffer = "";
		return $packet;
	}
}
