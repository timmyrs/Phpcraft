<?php
namespace Phpcraft\Event;
use hotswapp\CancellableEvent;
use Phpcraft\
{Packet\ClientboundPacketId, ServerConnection};
/**
 * The event emitted by the client when the server has sent a packet. Cancellable.
 * Cancelling the event tells the client to ignore the packet.
 */
class ClientPacketEvent extends ClientEvent
{
	use CancellableEvent;
	/**
	 * The ID of the packet that the server has sent.
	 *
	 * @var ClientboundPacketId $packetId
	 */
	public $packetId;

	function __construct(ServerConnection $server, ClientboundPacketId $packetId)
	{
		parent::__construct($server);
		$this->packetId = $packetId;
	}
}
