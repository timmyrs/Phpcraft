<?php
namespace Phpcraft;
class UserInterface
{
	protected $stdin;

	/**
	 * The constructor.
	 * Note that from this point forward, STDIN is in the hands of the UI until it is destructed.
	 */
	public function __construct()
	{
		$this->stdin = fopen("php://stdin", "r");
		stream_set_blocking($this->stdin, false);
	}

	/**
	 * The destructor.
	 */
	public function __destruct()
	{
		fclose($this->stdin);
	}

	/**
	 * Renders the UI.
	 * @param boolean $accept_input Set to true if you are looking for a return value.
	 * @return string If $accept_input is true and the user has submitted a line, the return will be that line. Otherwise, it will be null.
	 */
	public function render($accept_input = false)
	{
		if($accept_input)
		{
			$read = [$this->stdin];
			$null = [];
			if(stream_select($read, $null, $null, 0))
			{
				return trim(fgets($this->stdin));
			}
		}
		return null;
	}

	/**
	 * Prints a message.
	 * @return $this
	 */
	public function add($message)
	{
		echo "\x1B[m{$message}\n\x1B[m";
		return $this;
	}

	/**
	 * Prints a message.
	 * @return $this
	 */
	public function append($message)
	{
		return $this->add($message);
	}
}
