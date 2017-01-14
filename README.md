# migxFineUploader
MODX Revolution Multifile - Uploader for Newspublisher. Uploads images to a MIGX-TV from frontend, based on FineUploader

Features
--------------------------------------------------------------------------------
migxFineUploader lets you upload one or multiple files from Frontpage with newsPublisher into a MIGX-TV.
You can use drag/drop to change the sortorder of the uploaded items. 

Requirements
--------------------------------------------------------------------------------
MIGX and newsPublisher has to be installed, before you can use migxFineUploader

Installation
--------------------------------------------------------------------------------
Install MIGX and newsPublisher
Install the transport-package with package-manager of MODX Revolution


Get it Working
--------------------------------------------------------------------------------
Create a dynamic resource-specific mediasource with basePath and baseUrl of
```
[[!migxResourceMediaPath? &createFolder=`1` &pathTpl=`assets/resourceimages/{id}/`]]
```
remember the id fo this mediasource

Go to the MIGX - CMP to the tab 'MIGX'
Click the button 'Import from package'
package is 'migxfineuploader'
This imports a new MIGX-config with name 'mfu_images'
right-click that item in the grid and 'edit' this config
Go to the tab formtabs -> Edit the tab 'image' -> Edit the field 'image'
Go to the tab 'Mediasources' and change the 'Source ID' for both contexts (mgr and web) to the id of the mediasource, you have created before
If you have another context, add this context to the list.
Save everything.

Create a new MIGX-TV for your template.
Name it 'images'
Put into the input-options 'configs' the name of your MIGX-config: 'images'


Newspublisher - Example:
```
[[!NewsPublisher?
&show=`pagetitle,longtitle,content,images`
]]
```
