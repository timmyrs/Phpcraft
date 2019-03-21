<?php
namespace Phpcraft;
/**
 * A Packet.
 * Look at the source code of this class for a list of packet names.
 */
abstract class Packet
{
	/**
	 * Returns a binary string containing the payload of the packet.
	 * @param integer $protocol_version The protocol version you'd like to get the payload for.
	 * @return string
	 */
	function getPayload($protocol_version = -1)
	{
		$con = new Connection($protocol_version);
		$this->send($con);
		$con->read_buffer = $con->write_buffer;
		$con->readVarInt();
		return $con->read_buffer;
	}

	/**
	 * Initialises the packet class by reading its payload from the given Connection.
	 * @param Connection $con
	 * @return Packet
	 */
	abstract static function read(Connection $con);

	/**
	 * Adds the packet's ID and payload to the Connection's write buffer and, if the connection has a stream, sends it over the wire.
	 * @param Connection $con
	 * @return void
	 */
	abstract function send(Connection $con);

	abstract function toString();

	/**
	 * Returns the id of the packet name for the given protocol version.
	 * @param string $name The name of the packet.
	 * @param integer $protocol_version
	 * @return integer null the packet is not applicable for the protocol version or unknown.
	 * @deprecated Use PacketId::get($name)->getId($protocol_version), instead.
	 */
	static function getId($name, $protocol_version)
	{
		return @PacketId::get($name)->getId($protocol_version);
	}

	/**
	 * Converts a clientbound packet ID to its name as a string or null if unknown.
	 * @param integer $id
	 * @param integer $protocol_version
	 * @return string
	 * @deprecated Use ClientboundPacket::getById($id)->name, instead.
	 */
	static function clientboundPacketIdToName($id, $protocol_version)
	{
		return @ClientboundPacket::getById($id, $protocol_version)->name;
	}

	/**
	 * Converts a serverbound packet ID to its name as a string or null if unknown.
	 * @param integer $id
	 * @param integer $protocol_version
	 * @return string
	 * @deprecated Use ServerboundPacket::getById($id)->name, instead.
	 */
	static function serverboundPacketIdToName($id, $protocol_version)
	{
		return @ServerboundPacket::getById($id, $protocol_version)->name;
	}

	/**
	 * Initialises the packet class with the given name by reading its payload from the given Connection.
	 * Returns null if the packet does not have a class implementation yet.
	 * @return Packet
	 * @deprecated Use PacketId::get($name)->init($con), instead.
	 */
	static function init($name, Connection $con)
	{
		return PacketId::get($name)->init($con);
	}
}
