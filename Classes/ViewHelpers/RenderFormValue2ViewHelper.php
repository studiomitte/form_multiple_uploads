<?php

declare(strict_types=1);

namespace StudioMitte\FormMultipleUploads\ViewHelpers;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;
use TYPO3\CMS\Form\ViewHelpers\TranslateElementPropertyViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class RenderFormValue2ViewHelper extends \TYPO3\CMS\Form\ViewHelpers\RenderFormValueViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Return array element by key
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string the rendered form values
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $element = $arguments['renderable'];

        if (!$element instanceof FormElementInterface || !self::isEnabled($element)) {
            return '';
        }

        $renderingOptions = $element->getRenderingOptions();

        if ($renderingOptions['_isSection'] ?? false) {
            $data = [
                'element' => $element,
                'isSection' => true,
            ];
        } elseif ($renderingOptions['_isCompositeFormElement'] ?? false) {
            return '';
        } else {
            $formRuntime = $renderingContext
                ->getViewHelperVariableContainer()
                ->get(RenderRenderableViewHelper::class, 'formRuntime');
            $value = $formRuntime[$element->getIdentifier()];
            $data = [
                'element' => $element,
                'value' => $value,
                'processedValue' => self::processElementValue($element, $value, $renderChildrenClosure, $renderingContext),
                'isMultiValue' => is_iterable($value),
            ];
        }

        $as = $arguments['as'];
        $renderingContext->getVariableProvider()->add($as, $data);
        $output = $renderChildrenClosure();
        $renderingContext->getVariableProvider()->remove($as);

        return $output;
    }

    /**
     * Converts the given value to a simple type (string or array) considering the underlying FormElement definition
     *
     * @param FormElementInterface $element
     * @param mixed $value
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     * @internal
     */
    public static function processElementValue(
        FormElementInterface $element,
        $value,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $properties = $element->getProperties();
        if (is_array($value) && $value[0] instanceof FileReference) {
            $references = [];
            foreach ($value as $v) {
                $references[] = $v->getOriginalResource()->getName();
            }

            return $references;
        }
        if (isset($properties['options']) && is_array($properties['options'])) {
            $properties['options'] = TranslateElementPropertyViewHelper::renderStatic(
                ['element' => $element, 'property' => 'options'],
                $renderChildrenClosure,
                $renderingContext
            );
            if (is_array($value)) {
                return self::mapValuesToOptions($value, $properties['options']);
            }
            return self::mapValueToOption($value, $properties['options']);
        }
        if (is_object($value)) {
            return self::processObject($element, $value);
        }

        return $value;
    }
}
