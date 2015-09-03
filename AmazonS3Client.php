<?php
/**
* Provides a Amazon S3 Client Class
* 
* @module Amazon S3 Client
*/

/**
* Can't trust the header, or the file extension. This library is used
* to search a file for byte patterns in order to resolve it's mime type.
*
* @required mime_type_lib.php
*/
require VENDORS.'mime_type_lib.php';

/**
* Path to Amazon SDK loader
*
* @required aws-autoloader.php
*/
require VENDORS.'aws'.DS.'aws-autoloader.php';

use Aws\S3\S3Client;

/**
* Amazon S3 Client Wrapper with mime type detection.
*
* This class is a thin wrapper around the S3 Client, covers:
* 
* File Upload/Removal
* ACL Manipulation
* File Sharing 
*
* @class AmazonS3Client
*/
class AmazonS3Client
{
	/**
	* Amazon SDK Access Key
	*
	* @property ACCESS_KEY
	* @type String
	* @final
	*/
	const ACCESS_KEY = 'RI2GAJAKIBXKGZGC7UPA';

	/**
	* Amazon SDK Secret Key
	*
	* @property SECRET_KEY
	* @type String
	* @final
	*/
	const SECRET_KEY = 'RVxf9GNi+8SiGF/63+Wmu9G/lErEaF9FeDz0fzBw';

	/**
	* Default Amazon Bucket
	*
	* @property [DEFAULT_BUCKET_NAME]
	* @type String
	* @final
	*/
	const DEFAULT_BUCKET_NAME = 'example.com.s3-website-us-east-1.amazonaws.com';

	/**
	* Current Bucket being worked on
	*
	* @property bucketName
	* @type String
	* @private
	*/
	private $bucketName;

	/**
	* Amazon SDK S3 Client
	*
	* @property s3Client
	* @type Object
	*/
	public $s3Client;

	/**
	* @constructor
	* @private
	*/
	function __construct()
	{
		$this->s3Client = S3Client::factory(array(
			'credentials' => array(
				'key'    => self::ACCESS_KEY,
				'secret' => self::SECRET_KEY,
			)
		));
		$this->bucketName = $self::DEFAULT_BUCKET_NAME
	}

	/**
	* Remove a file using its id
	*
	* @method deleteFile
	* @param $object_id {String}
	*/
	public function deleteFile($object_id)
	{
		return $this->s3Client->deleteObject(array('Bucket' => $this->getBucketName(), 'Key' => $object_id));
	}

	/**
	* Check if a file exists using its id
	*
	* @method fileExists
	* @param $object_id {String}
	* @return Boolean
	*/
	public function fileExists($object_id)
	{
		return $this->s3Client->doesObjectExist($this->getBucketName(), $object_id);
	}

	/**
	* Returns the current Amazon Bucket being targeted
	*
	* @method getBucketName
	* @return String
	*/
	public function getBucketName()
	{
		return $this->bucketName;
	}

	/**
	* Retrieve an existing file using its id
	*
	* @method getFile
	* @param $object_id {String}
	*/
	public function getFile($object_id)
	{
		return $this->s3Client->getObject(array('Bucket' => $this->getBucketName(), 'Key' => $object_id));
	}

	/**
	* Retrieve a file's ACL using its id
	*
	* @method getFileAcl
	* @param $object_id {String}
	*/
	public function getFileAcl($object_id)
	{
		return $this->s3Client->getObjectAcl(array('Bucket' => $this->getBucketName(), 'Key' => $object_id));
	}

	/**
	* Retrieve a file's Meta data using its id
	*
	* @method getFileMetadata
	* @param $object_id {String}
	*/
	public function getFileMetadata($object_id)
	{
		return $this->s3Client->headObject(array('Bucket' => $this->getBucketName(), 'Key' => $object_id));
	}

	/**
	* Gets a file's URL, with its headers set to force a download of the file
	*
	* @param $object_id {String} id of file stored in Amazon S3
	* @param $filename {String} The name to set when downloading the file
	* @param [$expiration] {String} If set, url will be presigned for viewing up to specified time: '+10 minutes'
	* @return Object s3ResponseModel
	*/
	public function getDownloadUrl($object_id, $filename, $expiration = '')
	{
		return $this->s3Client->getObjectUrl($this->getBucketName(), $object_id, $expiration, array(
			'ResponseContentType' => 'application/octet-stream',
			'ResponseContentDisposition' => 'attachment; filename="'.$filename.'"',
		));
	}

	/**
	 * Gets a file's URL. If an expiration is set, a presinged url will be returned
	 *
	 * **Note** Depending on the file type, and the browser used, the file may either be displayed,
	 * or a download will begin. 
	 *
	 * @param $object_id {String} File id
	 * @param $expiration {String} if set url will be presigned for viewing up to specified time: '+10 minutes'
	 * @return: Object s3ResponseModel
	 */
	public function getFileUrl($object_id, $expiration = '')
	{
		return $this->s3Client->getObjectUrl($this->getBucketName(), $object_id, $expiration);
	}

	/**
	 * Lists files in a bucket.
	 *
	 * **Note** `$this->s3Client` has an Iterator that can be used, this is 
	 * recommended if a large search/process is being done, as it traverses one
	 * file at a time.
	 *
	 * You should rarely have to traverse a bucket.
	 *
	 * @param $prefix {String} Only files with their key/name that starts with **$prefix** are selected
	 * @param $max {Integer} Maximum number of results to retrieve, Amazon's max is 1000 
	 * @return: Object s3ResponseModel `Contents` key holds an array of files matched.
	 */
	public function listFiles($prefix = '', $max = 1000)
	{
		return $client->waitUntil('ObjectExists', array(
			'Bucket' => $this->getBucketName(),
			'Delimeter' => '_',
			'Prefix' => $prefix,
			'MaxKeys' => $max,
		));
	}

	/**
	 * Reads a local file, looks up it's `magic header` pattern to determine it's mime type.
	 *
	 * @param filename {String}
	 * @return String mimetype
	 */
	public function readFileMimeType($filename)
	{
		return get_file_mime_type($filename);
	}

	/**
	 * Uploads a local file
	 *
	 * @param $object_id {String} File Id
	 * @param $filename {String} Full path to file that will be uploaded
	 * @param $metadata {Array} Additional metadata to bind with the file being uploaded. They are Key value pairs of strings only.
	 * @param $acl {String} File ACL. Defaults to `bucket-owner-read`
	 * @return: Object s3ResponseModel
	 */
	public function putFile($object_id, $filename, $metadata = array(), $acl = 'bucket-owner-read')
	{
		$mime = $this->readFileMimeType($filename);
		return $this->s3Client->putObject(array(
			'Bucket' 		=> $this->getBucketName(), 
			'Key' 			=> $object_id,
			'SourceFile' 	=> $filename, 
			'Metadata' 		=> $metadata,
			'ACL' 			=> $acl,
			'ContentType'	=> $mime
		));
	}

	/**
	 * Sets ACL info on a file. Access Control
	 *
	 * @param $object_id {String} File id
	 * @param [$acl] {String} Access control. Defaults to `private`
	 * @return: {s3ResponseModel}
	 */
	public function putFileAcl($object_id, $acl = 'private')
	{
		return $this->s3Client->putObjectAcl(array(
			'Bucket' 		=> $this->getBucketName(), 
			'Key' 			=> $object_id,
			'ACL' 			=> $acl 
		));
	}

	/**
	* Set the Amazon Bucket being targeted
	*
	* @method setBucketName
	* @param name {String}
	* @return String
	*/
	public function setBucketName($name)
	{
		$this->bucketName = $name;
	}

	/**
	 * Sets the ACL of a File so it is viewoble by anyone; makes a file public
	 *
	 * @param $object_id {String} File id
	 * @return: Object s3ResponseModel
	 */
	public function shareFile($object_id)
	{
		return $this->putFileAcl($object_id, 'public-read');
	}

	/**
	 * Unlike putFile where you select a filename, uploadFile allows you to select a stream, a raw file or a string to upload.
	 *
	 * @param $object_id {string} File id
	 * @param $body {Mixed} a file, php stream (fopen), or a string to upload
	 * @param [$acl] {string} Access control. Defaults to `private`
	 * @return: Object s3ResponseModel
	 */
	public function uploadFile($object_id, $body, $acl = 'private')
	{
		return $this->s3Client->upload($this->getBucketName(), $object_id, $body, $acl);
	}

	/**
	 * Blocks until a file has finished uploading to the bucket.
	 *
	 * Sends out HEAD requests to check a file exists, so bandwith is negligible
	 *
	 * @param $object_id {String} File id
	 */
	public function waitUntilFileExists($object_id)
	{
		$client->waitUntil('ObjectExists', array(
			'Bucket' => $this->getBucketName(),
			'Key'    => $object_id
		));
	}
}
?>