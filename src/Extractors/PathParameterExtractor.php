<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\DataType;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\PathParameter;

class PathParameterExtractor extends AbstractTagExtractor
{
    protected const PATH_PARAMETER_TAG_NAME = 'path';

    public function __invoke(DocBlock $methodDocBlock, array $pathParameters): Parameters
    {
        $methodParameters = $this->getTags($methodDocBlock, self::PATH_PARAMETER_TAG_NAME);

        $parameters = new Parameters();

        if (!$methodParameters) {
            foreach ($pathParameters as $pathParameter) {
                $parameters->append($pathParameter);
            }

            return $parameters;
        }

        foreach ($methodParameters as $methodParameter) {
            $split = explode(' ', $methodParameter, 3);
            $count = count($split);

            if ($count < 2) {
                throw new OpenApiException("Wrong number of path parameters provided");
            }

            $name = $split[0];
            $type = DataType::getMappedClass($split[1]);
            $description = ($count >= 3) ? $split[2] : '';

            $parameter = new PathParameter($name, $type);
            $parameter->addDescription($description);

            $parameters->append($parameter);
        }

        return $parameters;
    }
}
