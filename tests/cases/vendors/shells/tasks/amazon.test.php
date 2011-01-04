<?php

	// Bring in the object we're testing
	App::import(
		'Core',
		'Shell',
		array(
			'file' => ROOT . '/cake/console/libs/shell.php'
		)
	);
	include_once(App::pluginPath('amazon') . 'vendors/shells/tasks/amazon.php');

	/**
	 * AmazonTaskTest
	 *
	 * Unit test case for the `AmazonTask` object.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class AmazonTaskTest extends CakeTestCase {

		/**
		 * Executed prior to every test.
		 *
		 * @return null
		 * @access public
		 */
		public function startTest() {
			$this->Amazon = new AmazonTaskProxy(new stdClass);
		}

		/**
		 * Executed following every test.
		 *
		 * @return null
		 * @access public
		 */
		public function endTest() {
			unset($this->Amazon);
		}

		/**
		 * Tests the `initialize` method of the object.
		 *
		 * @return null
		 * @access public
		 */
		public function testInitialize() {

			// Make sure our `_settings` member variable is empty
			$this->assertIdentical(
				$this->Amazon->_settings,
				array(
					'key' => null,
					'secret' => null
				)
			);

			// We'll need a `Controller` object ready before initializing...
			App::import('Core', 'Controller');
			$this->Amazon->initialize(
				new Controller,
				array(
					'testing' => true
				)
			);

			// Now we should have this class and our settings should be merged
			$this->assertTrue(class_exists('CFRuntime'));

		}

		/**
		 * Tests the `setSetting` method of the object.
		 *
		 * @return null
		 * @access public
		 */
		public function testsetSetting() {

			// This should be empty
			$this->assertIdentical(
				array_filter($this->Amazon->_settings),
				array()
			);

			// Get some random values
			$key = uniqid();
			$val = uniqid();

			// Now, set it away...
			$this->Amazon->setSetting($key, $val);

			// Now make sure it's in there
			$this->assertIdentical(
				$this->Amazon->_settings[$key],
				$val
			);

		}

		/**
		 * Tests the `__get` method of the object.
		 *
		 * @return null
		 * @access public
		 */
		public function test__get() {

			// Prior to testing, we need to setup a fake key and secret
			if (!defined('AWS_KEY') and !defined('AWS_SECRET_KEY')) {
				define('AWS_KEY', 'test');
				define('AWS_SECRET_KEY', 'test');
			}

			// Now, let's confirm that each `__service` will be returned
			foreach ($this->Amazon->__services as $variable=>$class) {
				$this->assertIsA(
					$this->Amazon->$variable,
					$class
				);
			}

			// Make sure we get a NULL back if we ask for something that's empty
			$this->assertNull($this->Amazon->{uniqid()});
		}

	}

	/**
	 * AmazonTaskProxy
	 *
	 * Extends the `AmazonTask` to provide access to protected and private
	 * methods and member variables.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class AmazonTaskProxy extends AmazonTask {

		/**
		 * Overrides our parent's `__createService` method so that we don't
		 * actually create the object.
		 *
		 * @param string $class
		 * @return object
		 * @access public
		 */
		public function __createService($class) {
			$return = new stdClass;
			$return->class = $class;
			$return->key = $this->_settings['key'];
			$return->secret = $this->_settings['secret'];
			return $return;
		}

		/**
		 * Checks if the current class has the passed method
		 * available to it.
		 *
		 * @param string $search
		 * @return boolean
		 * @access private
		 */
		private function __classHasMethod($search) {
			$class = new ReflectionClass(get_class($this));
			foreach ($class->getMethods() as $method) {
				if ($method->name == $search) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Checks if the current passed method is private.
		 *
		 * @param string $method
		 * @return boolean
		 * @access private
		 */
		private function __isPrivateMethod($method) {
			if (!is_object($method)) {
				$method = new ReflectionMethod(
					$this,
					$method
				);
			}
			return $method->isPrivate();
		}

		/**
		 * Returns a `ReflectionClass` for the class that defines
		 * the requested `$property`
		 *
		 * If we dont find any that do, we will return null.
		 *
		 * @param ReflectionClass $class
		 * @param string $property
		 * @return mixed
		 * @access private
		 */
		private function __getPropertyDefiningClass($class, $property) {
			if (!is_a($class, "ReflectionClass")) {
				$class = new ReflectionClass($this);
			}
			$loops = 0;
			while ($parent = $class->getParentClass()) {
				if ($loops > 10) {
					return null;
				}
				if ($parent->hasProperty($property)) {
					return $parent;
				}
				$loops++;
			}
		}

		/**
		 * Catches any calls for methods that either dont exist
		 * or are not visible. We will attempt to access any
		 * methods that are not currently visible.
		 *
		 * In the event that we fail to locate the correct thing
		 * to do, we will pass the call off to the main object
		 * if it has a `__call` method as well.
		 *
		 * @param string $method
		 * @param array $arguments
		 * @return mixed
		 * @access public
		 */
		public function __call($method, $arguments) {
			if ($this->__classHasMethod($method)) {
				$method = new ReflectionMethod($this, $method);
				$method->setAccessible(true);
				return $method->invokeArgs($this, $arguments);
			}

			// We didnt find it, let our parent have a crack at it
			if (method_exists(get_parent_class($this), "__call")) {
				return parent::__call($method, $arguments);
			}
		}

		/**
		 * Catches any requests for a member variable that is
		 * protected and sets it anyways.
		 *
		 * If the main class has its own `__get` method we will
		 * pass the call off to them.
		 *
		 * @param string $variable
		 * @return mixed
		 * @access public
		 */
		public function __get($variable) {

			$class = $this->__getPropertyDefiningClass(
				$this,
				$variable
			);

			if (is_null($class)) {

				// The variable doesnt exist, try to pass it on
				if (method_exists(get_parent_class($this), "__get")) {
					return parent::__get($variable);
				}

			} else {

				// Awesome, found it! Lets crack it open...
				$property = new ReflectionProperty(
					$class->getName(),
					$variable
				);

				// Make it accessible and send it back
				$property->setAccessible(true);
				return $property->getValue($this);

			}

		}

		/**
		 * Catches any requests for setting a member variable
		 * that is not visible.
		 *
		 * @param string $variable
		 * @param mixed $value
		 * @return null
		 * @access public
		 */
		public function __set($variable, $value) {
			if (isset($this->$variable)) {
				$reflectionProperty = new ReflectionProperty(
					$this,
					$variable
				);
				$reflectionProperty->setAccessible(true);
				$reflectionProperty->setValue(
					$this,
					$value
				);
			} else {
				$this->$variable = $value;
			}
		}

	}
