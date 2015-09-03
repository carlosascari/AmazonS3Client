# Amazon S3 Client

A thin wrapper around the Amazon SDK S3 Client.

Covers the following:

* File Upload/Removal (local files and streams)
* File Download URL links, w/expiration
* File Preview URL links, w/expiration
* File Sharing, w/expiration
* File search
* Mimetype detection

**Searching**

Bucket searching is not recommended, however it is possible.

When uploading a file to Amazon S3, you must set its `object id` i.e its `filename` in the cloud. This unique id is used when searching. A prefix is used as the search query, so for example, if you had multiple users with files stored in a single bucket, you can prefix every file they upload with their user id e.g: `3343_RunningKittensPic32.jpg`, and use **3343** as the search query to list all files from this user stored the cloud.

See AmazonS3Client::**listFiles**

## Usage

Open up the **AmazonS3Client.php** file and set your Amazon API Credentials. You can also optionally set the default Bucket to work with.

Make sure you set the path to the Amazon SDK autoloader, by default it attempts to find it in the `vendors` folder set by the CakePHP framework, so you will likely need to edit this.

```php
require VENDORS.'aws'.DS.'aws-autoloader.php';
```

Include the client wherever appropriate

```php
require 'path/to/AmazonS3Client.php';
```

**Note** mime_type_lib.php file is not included, google it, download it and include it for mimetype detection.

## API

function **deleteFile**($object_id)
Remove a file from the cloud using its filename.

function **fileExists**($object_id)
Test whether a file exists in the cloud.

function **getBucketName**()
Return the name of the bucket being worked on.

function **getFile**($object_id)
Returns a file in the cloud using its filename.

function **getFileAcl**($object_id)
Returns a file's ACL using its filename.

function **getFileMetadata**($object_id)
Returns a file's Metdata using its filename.

function **getDownloadUrl**($object_id, $filename, $expiration = '')
Returns a url that will force a download of a specified file in the cloud. 
Optionally, an expiration can be set.

function **getFileUrl**($object_id, $expiration = '')
Returns a url that can be used to embbed a preview of the file stored in the cloud.
Optionally, an expiration can be set.

function **listFiles**($prefix = '', $max = 1000)
Returns a list of files after performing a prefix-based search query. 

function **readFileMimeType**($filename)
Returns the resolved mimetype of a local file.

function **putFile**($object_id, $filename, $metadata = array(), $acl = 'bucket-owner-read')
Uploads a local file to the cloud. The file's mimetype is automatically resolved.
Optionally, metadata can be tied with the file (Array of key,value **String** pairs)
Optionally, the ACL of the file can be set, defaults to `bucket-owner-read`.

function **putFileAcl**($object_id, $acl = 'private')
Modify an existing file's ACL, using its filename.

function **setBucketName**($name)
Change the Amazon Bucket being worked on.

function **shareFile**($object_id)
Set an exisiting file's ACL to public. Everyone will be able to view/download the file.

function **uploadFile**($object_id, $body, $acl = 'private')
Uploads a file from a stream to the cloud.
Optionally, the ACL of the file can be set, defaults to `private`.

function **waitUntilFileExists**($object_id)
Block until a file has finished uploading to the cloud. 

## Documentation

Docs are autogenated using the very awesome `yuidoc`

Make sure you have node.js installed.

Install yuidoc

`npm i yuidoc -g`

And run yuidoc in the root directory

`yuidoc`

A folder named documentation will be created, inside the index.html file will display the complete API documentation.
