# TYPO3 Extension `form_multiple_uploads`

This extensions makes it possible to have a file upload with multiple files.

## Usage

Install the extension and provide the `multiple` attribute in your `form.yaml` file:

```yaml
  -
    properties:
      saveToFileMount: '1:/user_upload/'
      # important change start
      fluidAdditionalAttributes:
          multiple: multiple
      # important change end
      allowedMimeTypes:
        - video/mp4
```

## Todo

Currently it is not possible to add the multiple files as attachement


## Credits

This extension was created by Georg Ringer for [Studio Mitte, Linz](https://studiomitte.com) with ♥.

[Find more TYPO3 extensions we have developed](https://www.studiomitte.com/loesungen/typo3) that provide additional features for TYPO3 sites. 
