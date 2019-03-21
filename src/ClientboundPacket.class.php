<?php
namespace Phpcraft;
/**
 * The class for the IDs of packets sent to the client.
 */
class ClientboundPacket extends PacketId
{
	private static $all_cache;

	private static function nameMap()
	{
		return [
			"spawn_entity" => "spawn_object",
			"spawn_entity_experience_orb" => "sapwn_experience_orb",
			"spawn_entity_weather" => "spawn_global_entity",
			"spawn_entity_living" => "spawn_mob",
			"spawn_entity_painting" => "spawn_painting",
			"named_entity_spawn" => "spawn_player",
			"login" => "join_game",
			"map_chunk" => "chunk_data",
			"position" => "teleport",
			"playerlist_header" => "player_list_header_and_footer",
			"map" => "map_data",
			"game_state_change" => "change_game_state",
			"experience" => "set_experience",

			"keep_alive" => "keep_alive_request",
			"abilities" => "clientbound_abilities",
			"chat" => "clientbound_chat_message",
			"custom_payload" => "clientbound_plugin_message"
		];
	}

	/**
	 * @copydoc Identifier::all
	 */
	static function all()
	{
		if(!self::$all_cache)
		{
			self::$all_cache = self::_all("toClient", self::nameMap(), function($name, $pv)
			{
				return new ClientboundPacket($name, $pv);
			});
		}
		return self::$all_cache;
	}

	/**
	 * @copydoc Identifier::getId
	 */
	function getId($protocol_version)
	{
		if($protocol_version >= $this->since_protocol_version)
		{
			return $this->_getId($protocol_version, "toClient", self::nameMap());
		}
	}
}