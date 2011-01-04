<?php

	class TestShell extends Shell {

		public $tasks = array(
			'Amazon'
		);

		public function main() {
			$this->Amazon->setSetting('key', 'AKIAIRU6UIRBH7MP3K4A');
			$this->Amazon->setSetting('secret', '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');
			$this->Amazon->Sns->publish('arn:aws:sns:us-east-1:567053558973:test', 'This is a test from CakePHP');
		}

	}
