<?php

/**
 * Represents a Business
 */
class Business{
    private $ahsID;
    private $name;
    private $type;
    private $address;
    private $phone;
    private $permitHolder;

    private $inspectionsArray; //associative array(inspection => violation)

    /**
     * @param string $id The ID from the URL
     * @param string $name The name of the business
     * @param string $address The address of the business
     * @param string $phone The phone number of the business
     * @param string $permitHolder The businesses permit holder
     */
    function __construct($id, $name, $type, $address, $phone, $permitHolder) {
        $this->ahsID = $id;
        $this->name = $name;
        $this->type = $type;
        $this->address = $address;
        $this->phone = $phone;
        $this->permitHolder = $permitHolder;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAHSID()
    {
        return $this->ahsID;
    }

    /**
     * @param string $ahsID
     */
    public function setAhsID($ahsID)
    {
        $this->ahsID = $ahsID;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }



    /**
     * @return string
     */
    public function getInspectionsArray()
    {
        return $this->inspectionsArray;
    }

    /**
     * @param array $inspectionsArray
     */
    public function setInspectionsArray($inspectionsArray)
    {
        $this->inspectionsArray = $inspectionsArray;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPermitHolder()
    {
        return $this->permitHolder;
    }

    /**
     * @param string $permitHolder
     */
    public function setPermitHolder($permitHolder)
    {
        $this->permitHolder = $permitHolder;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
?>