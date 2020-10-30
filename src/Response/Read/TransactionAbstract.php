<?php declare(strict_types=1);

namespace h4kuna\Fio\Response\Read;

use h4kuna\Fio\Exceptions;
use h4kuna\Fio\Utils;

/**
 * @implements \Iterator<string, mixed>
 */
abstract class TransactionAbstract implements \Iterator
{
	/** @var array<string, mixed> */
	private $properties = [];

	/** @var string */
	protected $dateFormat;


	public function __construct(string $dateFormat)
	{
		$this->dateFormat = $dateFormat;
	}


	/**
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		}
		throw new Exceptions\Runtime('Property does not exists. ' . $name);
	}


	public function clearTemporaryValues(): void
	{
		$this->dateFormat = '';
	}


	/**
	 * @param mixed $value
	 */
	public function bindProperty(string $name, string $type, $value): void
	{
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$value = $this->{$method}($value);
		} elseif ($value !== null) {
			$value = $this->checkValue($value, $type);
		}
		$this->properties[$name] = $value;
	}


	/**
	 * @return mixed
	 */
	public function current()
	{
		return current($this->properties);
	}


	/**
	 * @return string
	 */
	public function key()
	{
		$key = key($this->properties);
		if ($key === NULL) {
			throw new Exceptions\InvalidState('Key cold\'nt be null.');
		}
		return $key;
	}


	public function next()
	{
		next($this->properties);
	}


	public function rewind()
	{
		reset($this->properties);
	}


	public function valid()
	{
		return array_key_exists($this->key(), $this->properties);
	}


	/** @return array<string, mixed> */
	public function getProperties(): array
	{
		return $this->properties;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function checkValue($value, string $type)
	{
		switch ($type) {
			case 'datetime':
				return Utils\Strings::createFromFormat($value, $this->dateFormat);
			case 'float':
				return floatval($value);
			case 'string':
				return trim($value);
			case 'int':
				return intval($value);
			case 'string|null':
				return trim($value) ?: null;
		}
		return $value;
	}

}
