<?php



namespace Programster\Swagger;


final class PathCollection implements \JsonSerializable
{
    private readonly array $arrayForm;


    /**
     * @param Path ...$paths
     * @throws \Exception - if two of the paths provided have the same relative path.
     */
    public function __construct(Path ...$paths)
    {
        $arrayForm = [];

        foreach ($paths as $path)
        {
            if (isset($arrayForm[$path->getRelativePath()]))
            {
                $msg = "You cannot have two paths with the same relative path: {$path->getRelativePath()}";
                throw new \Exception($msg);
            }

            /* @var $path \Programster\Swagger\Path */
            $arrayForm[$path->getRelativePath()] = $path;
        }

        $this->arrayForm = $arrayForm;
    }


    public function toArray() : array
    {
        return $this->arrayForm;
    }


    public function jsonSerialize(): mixed
    {
        return $this->arrayForm;
    }
}
