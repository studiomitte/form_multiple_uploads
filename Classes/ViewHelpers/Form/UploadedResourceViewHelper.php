<?php

declare(strict_types=1);

namespace StudioMitte\FormMultipleUploads\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\UploadViewHelper;

class UploadedResourceViewHelper extends UploadViewHelper
{

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param HashService $hashService
     * @internal
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper
     * @internal
     */
    public function injectPropertyMapper(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * Initialize the arguments.
     *
     * @internal
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', '');
        $this->registerArgument('accept', 'array', 'Values for the accept attribute', false, []);
    }

    /**
     * @return string
     */
    public function render()
    {
        $output = '';

        $as = $this->arguments['as'];
        $accept = $this->arguments['accept'];
        $resource = $this->getUploadedResource();

        if (!empty($accept)) {
            $this->tag->addAttribute('accept', implode(',', $accept));
        }

        if ($resource !== null) {
            // @phpstan-ignore-next-line
            if (is_array($resource) && !empty($resource)) {
                foreach ($resource as $r) {
                    $resourcePointerIdAttribute = '';
                    if ($this->hasArgument('id')) {
                        $resourcePointerIdAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-file-reference"';
                    }
                    $resourcePointerValue = $r->getUid();
                    if ($resourcePointerValue === null) {
                        // Newly created file reference which is not persisted yet.
                        // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                        $resourcePointerValue = 'file:' . $r->getOriginalResource()->getOriginalFile()->getUid();
                    }
                    $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer][' . $r . ']" value="' . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue)) . '"' . $resourcePointerIdAttribute . ' />';
                }
            } else {
                $resourcePointerIdAttribute = '';
                if ($this->hasArgument('id')) {
                    $resourcePointerIdAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-file-reference"';
                }
                $resourcePointerValue = $resource->getUid();
                if ($resourcePointerValue === null) {
                    // Newly created file reference which is not persisted yet.
                    // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                    $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
                }
                $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer]" value="' . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue)) . '"' . $resourcePointerIdAttribute . ' />';
            }

            $this->templateVariableContainer->add($as, $resource);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove($as);
        }

        $output .= parent::render();
        return $output;
    }

    /**
     * Return a previously uploaded resource.
     * Return NULL if errors occurred during property mapping for this property.
     *
     * @return FileReference|null
     */
    protected function getUploadedResource()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $resource = $this->getValueAttribute();
        if ($resource instanceof FileReference) {
            return $resource;
        }
        return $this->propertyMapper->convert($resource, FileReference::class);
    }
}
