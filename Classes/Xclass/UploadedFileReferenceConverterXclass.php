<?php

namespace StudioMitte\FormMultipleUploads\Xclass;

use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Form\Mvc\Property\Exception\TypeConverterException;
use TYPO3\CMS\Form\Mvc\Property\TypeConverter\UploadedFileReferenceConverter;

class UploadedFileReferenceConverterXclass extends UploadedFileReferenceConverter
{

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param array $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return AbstractFileFolder|Error|array|null
     * @internal
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        if (isset($source[0])) {
            $result = [];
            foreach ($source as $singleSource) {
                $converted = $this->convertSingleFile($singleSource, $configuration);
                $result[] = $converted;
            }
            return $result;
        }

        return $this->convertSingleFile($source, $configuration);
    }

    /**
     * @param array|FileReference $source
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return object|\TYPO3\CMS\Core\Resource\FileInterface|ExtbaseFileReference|Error|null
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
     * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidHashException
     */
    protected function convertSingleFile($source, ?PropertyMappingConfigurationInterface $configuration)
    {
        if ($source instanceof FileReference) {
            return $source;
        }
        if (!isset($source['error']) || $source['error'] === \UPLOAD_ERR_NO_FILE) {
            if (isset($source['submittedFile']['resourcePointer'])) {
                try {
                    // File references use numeric resource pointers, direct
                    // file relations are using "file:" prefix (e.g. "file:5")
                    $resourcePointer = $this->hashService->validateAndStripHmac($source['submittedFile']['resourcePointer']);
                    if (strpos($resourcePointer, 'file:') === 0) {
                        $fileUid = (int)substr($resourcePointer, 5);
                        return $this->createFileReferenceFromFalFileObject($this->resourceFactory->getFileObject($fileUid));
                    }
                    return $this->createFileReferenceFromFalFileReferenceObject(
                        $this->resourceFactory->getFileReferenceObject($resourcePointer),
                        (int)$resourcePointer
                    );
                } catch (\InvalidArgumentException $e) {
                    // Nothing to do. No file is uploaded and resource pointer is invalid. Discard!
                }
            }
            return null;
        }

        if ($source['error'] !== \UPLOAD_ERR_OK) {
            return $this->objectManager->get(Error::class, $this->getUploadErrorMessage($source['error']), 1471715915);
        }

        if (isset($this->convertedResources[$source['tmp_name']])) {
            return $this->convertedResources[$source['tmp_name']];
        }

        if ($configuration === null) {
            throw new \InvalidArgumentException('Argument $configuration must not be null', 1589183114);
        }

        try {
            $resource = $this->importUploadedResource($source, $configuration);
        } catch (TypeConverterException $e) {
            return $e->getError();
        } catch (\Exception $e) {
            return $this->objectManager->get(Error::class, $e->getMessage(), $e->getCode());
        }

        $this->convertedResources[$source['tmp_name']] = $resource;
        return $resource;
    }
}
