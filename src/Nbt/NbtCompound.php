<?php
namespace Phpcraft\Nbt;
use Phpcraft\Connection;
class NbtCompound extends NbtTag
{
	const ORD = 10;
	/**
	 * The child tags of the compound.
	 *
	 * @var array $children
	 */
	public $children;

	/**
	 * @param string $name The name of this tag.
	 * @param $children NbtTag[] The child tags of the compound.
	 */
	function __construct(string $name, array $children = [])
	{
		$this->name = $name;
		$this->children = $children;
	}

	/**
	 * Gets a child of the compound by its name or null if not found.
	 *
	 * @param string $name
	 * @return NbtTag
	 */
	function getChild(string $name)
	{
		foreach($this->children as $child)
		{
			if($child->name == $name)
			{
				return $child;
			}
		}
		return null;
	}

	/**
	 * Returns true if the compound has a child with the given name.
	 *
	 * @param string $name
	 * @return boolean
	 */
	function hasChild(string $name)
	{
		foreach($this->children as $child)
		{
			if($child->name == $name)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds a child to the compound or replaces an existing one by the same name.
	 *
	 * @param NbtTag $tag
	 * @return NbtCompound $this
	 */
	function addChild(NbtTag $tag)
	{
		if($tag instanceof NbtEnd)
		{
			trigger_error("Ignoring NbtEnd, as it is not a valid child");
		}
		else
		{
			$i = $this->getChildIndex($tag->name);
			if($i > -1)
			{
				$this->children[$i] = $tag;
			}
			else
			{
				array_push($this->children, $tag);
			}
		}
		return $this;
	}

	/**
	 * Gets the index of a child of the compound by its name or -1 if not found.
	 *
	 * @param string $name
	 * @return integer
	 */
	function getChildIndex(string $name)
	{
		foreach($this->children as $i => $child)
		{
			if($child->name == $name)
			{
				return $i;
			}
		}
		return -1;
	}

	/**
	 * Adds the NBT tag to the write buffer of the connection.
	 *
	 * @param Connection $con
	 * @param boolean $inList Ignore this parameter.
	 * @return Connection $con
	 */
	function write(Connection $con, bool $inList = false): Connection
	{
		if(!$inList)
		{
			$this->_write($con);
		}
		foreach($this->children as $child)
		{
			$child->write($con);
		}
		$con->writeByte(0);
		return $con;
	}

	function copy(): NbtTag
	{
		return new NbtCompound($this->name, $this->children);
	}

	function __toString(): string
	{
		$str = "{Compound \"".$this->name."\":";
		foreach($this->children as $child)
		{
			$str .= " ".$child->__toString();
		}
		return $str."}";
	}

	/**
	 * Returns the NBT tag in SNBT (stringified NBT) format, as used in commands.
	 *
	 * @param bool $fancy
	 * @param boolean $inList Ignore this parameter.
	 * @return string
	 */
	function toSNBT(bool $fancy = false, bool $inList = false): string
	{
		$snbt = ($inList || !$this->name ? "" : self::stringToSNBT($this->name).($fancy ? ": " : ":"))."{".($fancy ? "\n" : "");
		$c = count($this->children) - 1;
		if($fancy)
		{
			for($i = 0; $i <= $c; $i++)
			{
				$snbt .= self::indentString($this->children[$i]->toSNBT(true)).($i == $c ? "" : ",")."\n";
			}
		}
		else
		{
			for($i = 0; $i <= $c; $i++)
			{
				$snbt .= $this->children[$i]->toSNBT().($i == $c ? "" : ",");
			}
		}
		return $snbt."}";
	}
}
