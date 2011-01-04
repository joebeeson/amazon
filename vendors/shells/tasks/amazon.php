<?php

	/**
	 * AmazonTask
	 *
	 * Provides an entry point into the Amazon SDK.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 * @see http://book.cakephp.org/view/112/Tasks
	 */
	class AmazonTask extends Shell {

		/**
		 * Default settings.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_settings = array(
			'key' 		=> null,
			'secret'	=> null
		);

		/**
		 * Holds an array of valid service "names" and the class that corresponds
		 * to each one.
		 *
		 * @var array
		 * @access private
		 */
		private $__services = array(
			'SNS' 			=> 'AmazonSNS',
			'AutoScale' 	=> 'AmazonAS',
			'CloudFront'	=> 'AmazonCloudFront',
			'CloudWatch'	=> 'AmazonCloudWatch',
			'EC2'			=> 'AmazonEC2',
			'ELB'			=> 'AmazonELB',
			'EMR'			=> 'AmazonEMR',
			'RDS'			=> 'AmazonRDS',
			'S3'			=> 'AmazonS3',
			'SDB'			=> 'AmazonSDB',
			'SQS'			=> 'AmazonSQS'
		);

		/**
		 * Initialization method. Triggered after being attached to a shell.
		 *
		 * @return null
		 * @access public
		 */
		public function initialize() {

			// Handle loading our library firstly...
			App::import(
				'Vendor',
				'Amazon/aws-sdk/sdk.class.php',
				array(
					'file' => 'sdk.class.php'
				)
			);
		}

		/**
		 * Allows outside objects to set one of our settings.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @return null
		 * @access public
		 */
		public function setSetting($key, $value = '') {
			$this->_settings[$key] = $value;
		}

		/**
		 * PHP magic method for satisfying requests for undefined variables. We
		 * will attempt to determine the service that the user is requesting and
		 * start it up for them.
		 *
		 * @var string $variable
		 * @return mixed
		 * @access public
		 */
		public function __get($variable) {
			if (in_array($variable, array_keys($this->__services))) {

				// Store away the requested class for future usage.
				$this->$variable = $this->__createService(
					$this->__services[$variable]
				);

				// Return the class back to the caller
				return $this->$variable;
			}
		}

		/**
		 * Instantiates and returns a new instance of the requested `$class`
		 * object.
		 *
		 * @param string $class
		 * @return object
		 * @access private
		 */
		private function __createService($class) {
			return new $class(
				$this->_settings['key'],
				$this->_settings['secret']
			);
		}

	}
