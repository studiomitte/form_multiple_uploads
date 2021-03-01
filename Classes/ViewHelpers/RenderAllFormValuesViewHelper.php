<?php

declare(strict_types=1);

namespace StudioMitte\FormMultipleUploads\ViewHelpers;

use TYPO3\CMS\Form\Domain\Model\Renderable\CompositeRenderableInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class RenderAllFormValuesViewHelper extends \TYPO3\CMS\Form\ViewHelpers\RenderAllFormValuesViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Return array element by key.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string the rendered form values
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $renderable = $arguments['renderable'];

        if ($renderable instanceof CompositeRenderableInterface) {
            $elements = $renderable->getRenderablesRecursively();
        } else {
            $elements = [$renderable];
        }

        $as = $arguments['as'];
        $output = '';

        foreach ($elements as $element) {
            $output .= RenderFormValue2ViewHelper::renderStatic(
                [
                    'renderable' => $element,
                    'as' => $as,
                ],
                $renderChildrenClosure,
                $renderingContext
            );
        }

        return $output;
    }
}
