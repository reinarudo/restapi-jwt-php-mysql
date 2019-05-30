<?php

class Customer
{
    private $id;
    private $name;
    private $email;
    private $address;
    private $mobile;
    private $updatedBy;
    private $updatedOn;
    private $createdBy;
    private $createdOn;
    private $tableName = 'customers';

    /**
     * DB object
     *
     * @var \PDO
     */
    private $dbConn;
    
    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of address
     */ 
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */ 
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the value of mobile
     */ 
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set the value of mobile
     *
     * @return  self
     */ 
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get the value of updatedBy
     */ 
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set the value of updatedBy
     *
     * @return  self
     */ 
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get the value of updatedOn
     */ 
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * Set the value of updatedOn
     *
     * @return  self
     */ 
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * Get the value of createdBy
     */ 
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     *
     * @return  self
     */ 
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the value of createdOn
     */ 
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set the value of createdOn
     *
     * @return  self
     */ 
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get the value of tableName
     */ 
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the value of tableName
     *
     * @return  self
     */ 
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function __construct(){
        $db = new DbConnect();
        $this->dbConn = $db->connect();
    }

    public function getAllCustomers()
    {
        $stmt = $this->dbConn->prepare("SELECT * FROM $this->tableName");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $customers;
    }

    public function getCustomerDetailsById()
    {
        $sql = "SELECT
                    c.*,
                    u.name as created_user,
                    u1.name as updated_user
                FROM customers c
                    JOIN users u ON (c.created_by = u.id)
                    LEFT JOIN users u1 ON (c.updated_by = u1.id)
                WHERE
                    c.id = :customerId";
        
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':customerId', $this->id);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        return $customer;
    }

    public function insert()
    {
        $sql = "INSERT INTO $this->tableName (`id`, `name`, `email`, `address`, 
        `mobile`, `created_by`, `created_on`) VALUES(NULL, :name, :email, 
        :address, :mobile, :createdBy, :createdOn)";

        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':mobile', $this->mobile);
        $stmt->bindParam(':createdBy', $this->createdBy);
        $stmt->bindParam(':createdOn', $this->createdOn);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update()
    {
        $sql = "UPDATE $this->tableName SET";
        if ( null != $this->getName()) {
            $sql .= " name ='{$this->getName()}',";
        }

        if ( null != $this->getAddress()) {
            $sql .= " address ='{$this->getAddress()}',";
        }

        if ( null != $this->getMobile()) {
            $sql .= " mobile ='{$this->getMobile()}',";
        }

        $sql .= " updated_by = :updatedBy,
                  updated_on = :updatedOn
                WHERE
                  id = :userId";

        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(':userId', $this->id);
        $stmt->bindParam(':updatedBy', $this->updatedBy);
        $stmt->bindParam(':updatedOn', $this->updatedOn);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete() {
        $stmt = $this->dbConn->prepare("DELETE FROM $this->tableName 
            WHERE id = :userId");
        $stmt->bindParam(':userId', $this->id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}