<?php
namespace Phpcraft\Packet\PluginMessage;
class ClientboundBrandPluginMessagePacket extends ClientboundStringPluginMessagePacket
{
	/**
	 * @param string $data The brand.
	 */
	function __construct(string $data = "")
	{
		parent::__construct(self::CHANNEL_BRAND, $data);
	}
}
