<?php
namespace nucleus; 

require "valenceRequest.class.php";

class Nucleus {

	private $valenceRequest, $db; 
	
	const API_VERSION = "1.4";
	
	const STUDENT = 1471;
	const ENROLLED_STUDENT = 1478; 

	public function __construct($lms, $appKey, $appId, $userKey, $userId, $db) {
	
		$this->valenceRequest = new ValenceRequest($lms, $appKey, $appId, $userKey, $userId);
		$this->db = $db;
	}
	
	public function getVersion() {
		return "0.1";
	}
	
	function whoam() {
	
		$data = $this->valenceRequest->sendRequest("/d2l/api/lp/1.0/users/whoami", "GET");
		return $data;
	}
	
	public function createUser($userName, $firstName, $lastName, $password, $email = NULL) {
	
		$newUserData = array(
			"OrgDefinedId"  	=> $userName,
			"FirstName"     	=> $firstName,
			"MiddleName"    	=> "",
			"LastName" 	    	=> $lastName,
			"ExternalEmail" 	=> $email,
			"UserName" 			=> $userName,
			"RoleId" 			=> self::STUDENT,
			"IsActive" 			=> true,
			"SendCreationEmail" => false
		);

		$createUser = $this->valenceRequest->sendRequest("/d2l/api/lp/" . self::API_VERSION . "/users/", "POST", json_encode($newUserData));
		
		return array("message" => "User successfully created"); 
	}
	
	public function getUserID($userName) {
	
		$userData = $this->valenceRequest->sendRequest("/d2l/api/lp/" . self::API_VERSION . "/users/?userName=" . $userName, "GET");
		$userData = json_decode($userData);
		return $userData->UserId;

	}
	
	public function getUserEnrollments($userName) {
	
		$userID = $this->getUserID($userName);
		
		$userEnrollmentData = $this->valenceRequest->sendRequest("/d2l/api/lp/" . self::API_VERSION . "/enrollments/users/" . $userID . "/orgUnits/", "GET");
		
		$userEnrollments = json_decode($userEnrollmentData);
		
		$enrollments = array(); 
		
		foreach($userEnrollments->Items AS $enrollment) {
		
			$orgUnitInfo = $enrollment->OrgUnit;
			$enrollments[] = $orgUnitInfo->Id;
		
		}
	
		
		return $enrollments;
	}

	public function enrollUser($userName, $orgID) {
		
		$userID = $this->getUserID($userName);
	
		$newEnrollmentData = array(
			"OrgUnitId" => $orgID,
			"UserId" => $userID,
			"RoleId" => self::ENROLLED_STUDENT
		);
		
		$createEnrollment = $this->valenceRequest->sendRequest("/d2l/api/lp/" . self::API_VERSION . "/enrollments/", "POST", json_encode($newEnrollmentData)); 
		
		return array("message" => "User successfully enrolled");
	}
	
	function unenrollUser($userName, $orgID) {
	
		$userID = $this->getUserID($userName);
	
		$deleteEnrollment = $this->valenceRequest->sendRequest("/d2l/api/lp/" . self::API_VERSION . "/enrollments/users/" . $userID . "/orgUnits/" . $orgID, "DELETE");
		
		return array("message" => "Enrolment deleted successfully");
	}

}
