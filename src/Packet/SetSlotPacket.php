<?php
namespace Phpcraft\Packet;
use Phpcraft\
{Connection, Exception\IOException, Exception\MissingMetadataException, Slot};
class SetSlotPacket extends Packet
{
	/**
	 * The ID of the window being updated. 0 for inventory.
	 *
	 * @var int $window
	 */
	public $window;
	/**
	 * The ID of the slot being updated.
	 *
	 * @var int $slotId
	 * @see Slot
	 * @see https://wiki.vg/Inventory
	 */
	public $slotId;
	/**
	 * The new value of the slot.
	 *
	 * @var Slot|null $slot
	 */
	public $slot;

	/**
	 * @param int $window The ID of the window being updated. 0 for inventory.
	 * @param int $slotId The ID of the slot being updated. See https://wiki.vg/Inventory and {@link Slot} constants.
	 * @param Slot|null $slot The new value of the slot.
	 */
	function __construct(int $window = 0, int $slotId = 0, ?Slot $slot = null)
	{
		$this->window = $window;
		$this->slotId = $slotId;
		$this->slot = $slot;
	}

	/**
	 * Initialises the packet class by reading its payload from the given Connection.
	 *
	 * @param Connection $con
	 * @return SetSlotPacket
	 * @throws IOException
	 */
	static function read(Connection $con): SetSlotPacket
	{
		return new SetSlotPacket($con->readByte(), $con->readShort(), $con->readSlot());
	}

	/**
	 * Adds the packet's ID and payload to the Connection's write buffer and sends it over the wire if the connection has a stream.
	 * Note that in some cases this will produce multiple Minecraft packets, therefore you should only use this on connections without a stream if you know what you're doing.
	 *
	 * @param Connection $con
	 * @return void
	 * @throws IOException
	 * @throws MissingMetadataException
	 */
	function send(Connection $con): void
	{
		$con->startPacket("set_slot");
		$con->writeByte($this->window);
		$con->writeShort($this->slotId);
		$con->writeSlot($this->slot);
		$con->send();
	}

	function __toString()
	{
		return "{Set Slot: Window ID {$this->window}, Slot ID {$this->slotId}, ".Slot::toString($this->slot)."}";
	}
}
