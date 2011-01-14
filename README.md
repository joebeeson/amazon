# Amazon Plugin for CakePHP 1.3+

This plugin is a (*very*) thin veil over Amazon's [AWS SDK for PHP](http://aws.amazon.com/sdkforphp/) for use in CakePHP controllers and shells.

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/amazon.git

* Add the component to a controller

		public $components = array(
			'Amazon.Amazon' => array(
				'key' => 'Your Amazon API key',
				'secret' => 'Your Amazon API key secret'
			)
		);

* Add the task to a shell

		public $tasks = array(
			'Amazon.Amazon'
		);

		public function startup() {
			$this->Amazon->setSetting('key', 'Your Amazon API key');
			$this->Amazon->setSetting('secret', 'Your Amazon API key secret');
		}

## Configuration

For the component, configuration is as simple as adding in the necessary information to the array which gets passed to to the file. Unfortunately tasks don't operate in a similar manner and the settings must be passed via the
`setSetting` method that it makes available.

## Usage

Once configured and initialized usage is identical between both the component and task. At this point you have access to all of the methods available from the AWS SDK. The library currently has support for the following services:

* Simple Notification Service
* Auto Scale
* CloudFront
* CloudWatch
* Elastic Compute Cloud
* Elastic Load Balancer
* Relation Database
* Elastic Map Reduce
* SimpleDB
* Simple Queue Service

Not all of the methods for each service has been thoroughly tested. If you run into any issues, feel free to open an issue here, on the repository.

The specific objects for each service can be accessed through the component or task as a member of it. Here is an example for each:

* `$this->Amazon->SNS`
* `$this->Amazon->AutoScale`
* `$this->Amazon->CloudFront`
* `$this->Amazon->CloudWatch`
* `$this->Amazon->EC2`
* `$this->Amazon->ELB`
* `$this->Amazon->RDS`
* `$this->Amazon->EMR`
* `$this->Amazon->SDB`
* `$this->Amazon->SQS`

## Example

To publish to the Simple Notification Service the method to use is called `publish` -- here is an example:

		$this->Amazon->SNS->publish('arn:aws:sns:us-east-1:567053558973:foo', 'This is the message to publish');

To lookup any EC2 instances, we can do the following:

		$response = $this->Amazon->EC2->describe_instances();

Lets say we wanted to upload a file to S3:

		$this->Amazon->S3->create_object(
			'our_bucket',
			'filename.jpg',
			array(
				'fileUpload' => '/tmp/image.jpg',
				'acl' => AmazonS3::ACL_PUBLIC
			)
		);

## Notes

Almost all of the methods that can be performed against a service will return a `CFResponse` object. The plugin makes no attempt to translate this into anything other than an object since the response is directly generated from the API response. For more information on the `CFResponse` object [click here](http://docs.amazonwebservices.com/AWSSDKforPHP/latest/index.html#i=CFResponse)
