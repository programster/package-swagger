<?php



namespace Programster\Swagger;


final class PathCollection extends \ArrayObject implements \JsonSerializable
{
    private readonly array $arrayForm;


    /**
     * @param Path ...$paths
     * @throws \Exception - if two of the paths provided have the same relative path.
     */
    public function __construct(Path ...$paths)
    {
        parent::__construct($paths);
    }


    /**
     * Append a path to this array object.
     * @param $value
     * @return void
     * @throws \Exception - if the value passed is not an instance of a Path
     */
    public function append($value)
    {
        if ($value instanceof Path)
        {
            parent::append($value);
        }
        else
        {
            throw new \Exception("Cannot append non Path to a " . __CLASS__);
        }
    }


    /**
     * Override a specific path.
     * @param mixed $index
     * @param mixed $newval
     * @return void
     * @throws \Exception - the the new value passed is not of a Path type.
     */
    public function offsetSet(mixed $index, mixed $newval) : void
    {
        if ($newval instanceof Path)
        {
            parent::offsetSet($index, $newval);
        }
        else
        {
            throw new \Exception("Cannot add a non Path value to a " . __CLASS__);
        }
    }


    public function jsonSerialize(): mixed
    {
        return $this->getArrayCopy();
    }
}
