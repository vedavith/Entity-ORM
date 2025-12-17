<?php

// This File is Generated with Entity ORM. 

namespace EntityForge\EntityModels;

class UserData
{

	/** @var int */
	 public int $id;

	/** @var string */
	 public string $email;

	/** @var string */
	 public string $first_name;

	/** @var string */
	 public string $last_name;

	/** @var \DateTime */
	 public \DateTime $created_on;

	/** @var \DateTime */
	 public \DateTime $updated_on;

	/** __get **/
	public function __get($property) {
	 if (property_exists($this,$property)) {
		 return $this->$property;
	 }
	}

	/** __set **/
	public function __set($property,$value) {
	 if (property_exists($this, $property)) {
		 $this->$property = $value;
	  }
		 return $this;
	 }

}
