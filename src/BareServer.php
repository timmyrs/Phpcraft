<?php
namespace Phpcraft;
use Phpcraft\World\World;
/**
 * The bare minimium of a server, featuring a world and entity tracking.
 *
 * @since 0.5.5
 */
class BareServer
{
	/**
	 * The world that clients will be presented with.
	 *
	 * @var World $world
	 */
	public $world;
	/**
	 * The counter used to assign entity IDs.
	 *
	 * @var Counter $eidCounter
	 */
	public $eidCounter;
}
