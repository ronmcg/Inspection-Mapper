<?php
/**
 * Represents an Inspection
 */
class Inspection{

    private $date;
    private $type;
    private $action;
    private $violations;

    /**
     * Make a new Inspection
     * @param string $date The date the inspection happened
     * @param string $type The type of inspection it was
     * @param string $action The action taken
     */
    function __construct($date, $type, $action){
        $this->setDate($date);
        $this->type = $type;
        $this->action = $action;
        $this->violations = array();
    }

    /**
     * @return array
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param array $violations
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;
    }

    /**
     * @return The
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param The $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAhsID()
    {
        return $this->ahsID;
    }

    /**
     * @param mixed $ahsID
     */
    public function setAhsID($ahsID)
    {
        $this->ahsID = $ahsID;
    }

    /**
     * @return The
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param The $date
     */
    public function setDate($date)
    {
        //clean the date string
        //sometimes it is wrapped in 2 tags instead of 1 so strip it twice and check
        preg_match("~>(.*)<~", $date, $strip_date);
        $a = preg_match("~>(.*)<~", $strip_date[1], $strip_date2);
        if($a == 0){
            $this->date = $strip_date[1];
        }else{
            $this->date = $strip_date2[1];
        }
    }

    /**
     * @return The
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param The $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
?>