<?php

namespace StudioMitte\FormMultipleUploads\Xclass;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\SaveToDatabaseFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

class SaveToDabaseFinisherXclass extends SaveToDatabaseFinisher
{

    /**
     * Prepare data for saving to database
     *
     * @param array $elementsConfiguration
     * @param array $databaseData
     * @return array
     */
    protected function prepareData(array $elementsConfiguration, array $databaseData)
    {
        foreach ($this->getFormValues() as $elementIdentifier => $elementValue) {
            if (
                ($elementValue === null || $elementValue === '')
                && isset($elementsConfiguration[$elementIdentifier])
                && isset($elementsConfiguration[$elementIdentifier]['skipIfValueIsEmpty'])
                && $elementsConfiguration[$elementIdentifier]['skipIfValueIsEmpty'] === true
            ) {
                continue;
            }

            $element = $this->getElementByIdentifier($elementIdentifier);
            if (
                !$element instanceof FormElementInterface
                || !isset($elementsConfiguration[$elementIdentifier])
                || !isset($elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn'])
            ) {
                continue;
            }

            if (is_array($elementValue) && $elementValue[0] instanceof FileReference) {
                $data = [];
                foreach ($elementValue as $v) {
                    $data[] = $v->getOriginalResource()->getCombinedIdentifier();
                }
                $elementValue = implode(',', $data);
            } elseif ($elementValue instanceof FileReference) {
                if (isset($elementsConfiguration[$elementIdentifier]['saveFileIdentifierInsteadOfUid'])) {
                    $saveFileIdentifierInsteadOfUid = (bool)$elementsConfiguration[$elementIdentifier]['saveFileIdentifierInsteadOfUid'];
                } else {
                    $saveFileIdentifierInsteadOfUid = false;
                }

                if ($saveFileIdentifierInsteadOfUid) {
                    $elementValue = $elementValue->getOriginalResource()->getCombinedIdentifier();
                } else {
                    $elementValue = $elementValue->getOriginalResource()->getProperty('uid_local');
                }
            } elseif (is_array($elementValue)) {
                $elementValue = implode(',', $elementValue);
            } elseif ($elementValue instanceof \DateTimeInterface) {
                $format = $elementsConfiguration[$elementIdentifier]['dateFormat'] ?? 'U';
                $elementValue = $elementValue->format($format);
            }

            $databaseData[$elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn']] = $elementValue;
        }
        return $databaseData;
    }
}
