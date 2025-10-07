<?php
class User {
    private $userId;
    private $name;
    private $phoneNumber;
    
    private $messages;
    
    public function __construct($userId, $name, $phoneNumber)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
        
        $this->messages = array();
    }
    
    public function getUserId() {
        return $this->userId;
    }

    public function getName() {
        return $this->name;
    }

    public function getPhoneNumber() {
        return $this->phoneNumber;
    }
}

