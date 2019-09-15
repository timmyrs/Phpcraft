<?php
namespace Phpcraft\Command;
use InvalidArgumentException;
class FloatProvider extends ArgumentProvider
{
	public function __construct(CommandSender &$sender, string $arg)
	{
		if(!is_numeric($arg))
		{
			throw new InvalidArgumentException("{$arg} is not a valid float");
		}
		$this->value = floatval($arg);
	}

	function getValue(): float
	{
		return $this->value;
	}
}