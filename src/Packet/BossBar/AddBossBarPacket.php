<?php
namespace Phpcraft\Packet\BossBar;
use Phpcraft\
{ChatComponent, ClientConnection, Connection, Entity\EntityType, Entity\Living, Exception\IOException, Packet\SpawnMobPacket, Point3D};
class AddBossBarPacket extends BossBarPacket
{
	/**
	 * The "title" of the boss bar.
	 *
	 * @var ChatComponent $title
	 */
	public $title;
	/**
	 * The percentage the boss bar is filled, aka. the health of the boss.
	 * This should be between 0 and 1. And whilst values below 0 disconnect the client, values above 1 render additional boss bars to the right.
	 *
	 * @var float $health
	 */
	public $health = 1.0;
	/**
	 * The color of the boss bar.
	 *
	 * @see BossBarPacket
	 * @var int $color
	 */
	public $color = 0;
	/**
	 * The division of the boss bar.
	 *
	 * @see BossBarPacket
	 * @var int $division
	 */
	public $division = 0;
	/**
	 * True if the sky should be darkened.
	 *
	 * @var boolean $darken_sky
	 */
	public $darken_sky = false;
	/**
	 * True if this should play the end music.
	 *
	 * @var boolean $play_end_music
	 */
	public $play_end_music = false;
	/**
	 * True if this should create fog.
	 *
	 * @var boolean $create_fog
	 */
	public $create_fog = false;

	/**
	 * Adds the packet's ID and payload to the Connection's write buffer and sends it over the wire if the connection has a stream.
	 * Note that in some cases this will produce multiple Minecraft packets, therefore you should only use this on connections without a stream if you know what you're doing.
	 *
	 * @param Connection $con
	 * @return void
	 * @throws IOException
	 */
	function send(Connection $con): void
	{
		if($con->protocol_version > 49)
		{
			$con->startPacket("boss_bar");
			$con->writeUUID($this->uuid);
			$con->writeVarInt(0);
			$con->writeChat($this->title);
			$con->writeFloat($this->health);
			$con->writeVarInt($this->color);
			$con->writeVarInt($this->division);
			$flags = 0;
			if($this->darken_sky)
			{
				$flags |= 0x1;
			}
			if($con->protocol_version >= 395)
			{
				if($this->play_end_music)
				{
					$flags |= 0x2;
				}
				if($this->create_fog)
				{
					$flags |= 0x4;
				}
			}
			else
			{
				if($this->play_end_music || $this->create_fog)
				{
					$flags |= 0x2;
				}
			}
			$con->writeUnsignedByte($flags);
			$con->send();
		}
		else
		{
			$packet = new SpawnMobPacket(abs($this->uuid->hashCode()) * -1, EntityType::get("ender_dragon"), $this->uuid);
			assert($packet->metadata instanceof Living);
			if($con instanceof ClientConnection)
			{
				$packet->pos = new Point3D($con->pos->x, -10, $con->pos->z);
			}
			else
			{
				$packet->pos = new Point3D(0, -10, 0);
			}
			$packet->metadata->custom_name = $this->title;
			$packet->metadata->silent = true;
			$packet->metadata->health = ($this->health * 200);
			if($packet->metadata->health < 3)
			{
				$packet->metadata->health = 3;
			}
			$packet->send($con);
		}
	}

	function __toString()
	{
		$str = "{AddBossBarPacket: Boss Bar {$this->uuid}, \"".$this->title->toString()."\", ".($this->health * 100)."% Health, Color ID {$this->color}, Division ID {$this->division}";
		if($this->darken_sky)
		{
			$str .= ", Darkens Sky";
		}
		if($this->play_end_music)
		{
			$str .= ", Plays End Music";
		}
		if($this->create_fog)
		{
			$str .= ", Creates Fog";
		}
		return $str."}";
	}
}
