<?php

namespace WebLinks\DAO;

use WebLinks\Domain\Link;

class LinkDAO extends DAO 
{

    /**
     * @var \WebLinks\DAO\UserDAO
     */
    private $userDAO;

    public function setUserDAO(UserDAO $userDAO) {
        $this->userDAO = $userDAO;
    }


    /**
     * Returns a list of all links, sorted by id.
     *
     * @return array A list of all links.
     */
    public function findAll() {
        $sql = "SELECT * FROM t_link ORDER BY link_id DESC";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $entities = array();
        foreach ($result as $row) {
            $id = $row['link_id'];
            $entities[$id] = $this->buildDomainObject($row);
        }
        return $entities;
    }

    /**
     * Creates an Link object based on a DB row.
     *
     * @param array $row The DB row containing Link data.
     * @return \WebLinks\Domain\Link
     */
    protected function buildDomainObject($row) {
        $link = new Link();
        $link->setId($row['link_id']);
        $link->setUrl($row['link_url']);
        $link->setTitle($row['link_title']);

        if (array_key_exists('user_id', $row)) {
            $userId = $row['user_id'];
            $user = $this->userDAO->find($userId);
            $link->setUser($user);
        }
        
        return $link;
    }
}
