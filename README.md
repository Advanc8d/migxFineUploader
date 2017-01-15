# migxFineUploader
MODX Revolution Multifile - Uploader for NewsPublisher. Uploads images to a MIGX-TV from frontend, based on FineUploader

Features
--------------------------------------------------------------------------------
migxFineUploader lets you upload one or multiple files from Frontpage with NewsPublisher into a MIGX-TV.
You can use drag/drop to change the sortorder of the uploaded items. 

Requirements
--------------------------------------------------------------------------------
MIGX and NewsPublisher has to be installed, before you can use migxFineUploader

Installation
--------------------------------------------------------------------------------
Install MIGX and newsPublisher

Install the transport-package with package-manager of MODX Revolution


Get it Working
--------------------------------------------------------------------------------
- Create a dynamic resource-specific mediasource with basePath and baseUrl of
```
[[!migxResourceMediaPath? &createFolder=`1` &pathTpl=`assets/resourceimages/{id}/`]]
```
- remember the id fo this mediasource

- Go to the MIGX - CMP to the tab 'MIGX'
- Click the button 'Import from package'
- package is 'migxfineuploader'
- This imports a new MIGX-config with name 'mfu_images'
- right-click that item in the grid and 'edit' this config
- Go to the tab formtabs -> Edit the tab 'image' -> Edit the field 'image'
- Go to the tab 'Mediasources' and change the 'Source ID' for both contexts (mgr and web) to the id of the mediasource, you have created before
- If you have another context, add this context to the list.
- Save everything.

- Create a new MIGX-TV for your template.
- Name it 'images'
- Put into the input-options 'configs' the name of your MIGX-config: 'images'

- Add the images - TV to your NewsPublisher - snippet-call

Newspublisher - Example:
```
[[!NewsPublisher?
&show=`pagetitle,longtitle,content,images`
]]
```

Now. you should be able to upload images with NewsPublisher to your MIGX-TV, reorder them with drag/drop and delete them.

Customization
--------------------------------------------------------------------------------
If you need your own set of fileUploader - options, for example changing the maxFiles or allowedExtenstions,
Edit the MIGX - config and change the snippet-name under 'Hook - Snippets' under the tab MIGXdb - Settings.

Change the value for:
```
{"mfugetproperties":"mfuUploaderGetProperties"}
```
and create a copy of the snippet 'mfuUploaderGetProperties' with your modified options.

If you need more customization, you can create your own UI - template by creating a chunk with the name in this section:
```
$prop['tplUiTemplate'] = 'mfu.migx.template';
```
you can of course also change the name of that chunk within your customized getproperties - snippet

To change the initialization of FineUploader completely to your needs, create a chunk with the name, found in this section:
```
$prop['tplInitUploader'] = 'mfu.inituploader';
```
you can find the default code for both chunks in this files:
https://github.com/Bruno17/migxFineUploader/blob/master/core/components/migxfineuploader/elements/chunks/mfu.migx.template.chunk.html
https://github.com/Bruno17/migxFineUploader/blob/master/core/components/migxfineuploader/elements/chunks/mfu.inituploader.chunk.html

Read more about FineUploader here:
http://fineuploader.com/

and about uikit, which is used for drag/drop - sorting of items and maybe more in the future:
https://getuikit.com/v2/docs/documentation_get-started.html












