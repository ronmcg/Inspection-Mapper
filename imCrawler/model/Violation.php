<?php


/**
 * Represents a violation found during an inspection
 */
class Violation {
    private $date;
    private $name;
    private $requirement;
    private $comment;
    private $critical;

    /**
     * Create a new Violation object
     * @param string $comment The comment from the inspector
     * @param bool $critical Bool Is the violation critical
     * @param string $name The name of the violation (The question being asked by inspector)
     * @param string $requirement The legal requirements regarding this violation, can be empty
     */
    function __construct($date, $name, $requirement, $comment, $critical)
    {
        $this->date = $date;
        $this->comment = $comment;
        $this->critical = $critical;
        $this->name = $name;
        $this->requirement = $requirement;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Bool
     */
    public function getCritical()
    {
        return $this->critical;
    }

    /**
     * @param Bool $critical
     */
    public function setCritical($critical)
    {
        $this->critical = $critical;
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
    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * @param string $requirement
     */
    public function setRequirement($requirement)
    {
        $this->requirement = $requirement;
    }
}
?>