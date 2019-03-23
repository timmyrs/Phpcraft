<?php
namespace Phpcraft;
class NbtCompound extends NbtTag
{
	/**
	 * The child tags of the compound.
	 * @var array $children
	 */
	public $children;

	/**
	 * The constructor.
	 * @param string $name The name of this tag.
	 * @param array $children The child tags of the compound.
	 */
	public function __construct($name, $children = [])
	{
		$this->name = $name;
		$this->children = $children;
	}

	/**
	 * Gets a child of the compound by its name or null if not found.
	 * @return NbtTag
	 */
	public function getChild($name)
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
	 * Gets the index of a child of the compound by its name or -1 if not found.
	 * @return integer
	 */
	public function getChildIndex($name)
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
	 * Adds a child to the compound or replaces an existing one by the same name.
	 * @return NbtCompound $this
	 * @throws Exception
	 */
	public function addChild(NbtTag $tag)
	{
		if($tag instanceof NbtEnd)
		{
			throw new Exception("\\NbtEnd is not a valid child");
		}
		$i = $this->getChildIndex($tag->name);
		if($i > -1)
		{
			$this->children[$i] = $tag;
		}
		else
		{
			array_push($this->children, $tag);
		}
		return $this;
	}

	/**
	 * @copydoc NbtTag::write
	 */
	public function write(Connection $con, $inList = false)
	{
		if(!$inList)
		{
			$this->_write($con, 10);
		}
		foreach($this->children as $child)
		{
			$child->write($con);
		}
		$con->writeByte(0);
		return $con;
	}

	public function copy()
	{
		return new NbtCompound($this->name, $this->children);
	}

	public function toString()
	{
		$str = "{Compound \"".$this->name."\":";
		foreach($this->children as $child)
		{
			$str .= " ".$child->toString();
		}
		return $str."}";
	}
}
