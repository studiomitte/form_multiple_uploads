<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Form\Mvc\Property\TypeConverter\UploadedFileReferenceConverter::class] = [
    'className' => \StudioMitte\FormMultipleUploads\Xclass\UploadedFileReferenceConverterXclass::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Form\Domain\Finishers\SaveToDatabaseFinisher::class] = [
    'className' => \StudioMitte\FormMultipleUploads\Xclass\SaveToDabaseFinisherXclass::class
];
